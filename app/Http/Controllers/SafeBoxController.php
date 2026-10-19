<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SafeBox;
use App\Models\SafeBoxVersion;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Exports\SafeBoxExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class SafeBoxController extends Controller
{
    /**
     * SafeBox Dashboard
     */
    public function index(Request $request)
    {
        $userId = auth()->id();
        $query = SafeBox::where('user_id', $userId);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        // Category Filter
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Health / Expiration Filter
        if ($request->filled('filter')) {
            if ($request->filter === 'expired') {
                $query->whereNotNull('expires_at')->where('expires_at', '<', Carbon::today());
            } elseif ($request->filter === 'expiring_soon') {
                $query->whereNotNull('expires_at')
                    ->where('expires_at', '>=', Carbon::today())
                    ->where('expires_at', '<=', Carbon::today()->addDays(7));
            }
        }

        // Statistics
        $allUserBoxes = SafeBox::where('user_id', $userId)->get();
        $weakCount = 0;
        $expiredCount = 0;
        $expiringSoonCount = 0;

        foreach ($allUserBoxes as $box) {
            $secretText = $box->decrypted_secret;
            // password strength heuristic (< 8 chars or only numbers/letters)
            if (strlen($secretText) < 8 || !preg_match('/[0-9]/', $secretText) || !preg_match('/[^a-zA-Z0-9]/', $secretText)) {
                $weakCount++;
            }

            if ($box->isExpired()) {
                $expiredCount++;
            } elseif ($box->isExpiringSoon()) {
                $expiringSoonCount++;
            }
        }

        $stats = [
            'total'          => $allUserBoxes->count(),
            'active'         => $allUserBoxes->where('status', 'Active')->count(),
            'locked'         => $allUserBoxes->where('status', 'Locked')->count(),
            'archived'       => $allUserBoxes->where('status', 'Archived')->count(),
            'weak'           => $weakCount,
            'expired'        => $expiredCount,
            'expiring_soon'  => $expiringSoonCount,
            'password_count' => $allUserBoxes->where('category', 'Password')->count(),
            'card_count'     => $allUserBoxes->where('category', 'Card')->count(),
            'api_count'      => $allUserBoxes->where('category', 'API_Key')->count(),
            'note_count'     => $allUserBoxes->where('category', 'Note')->count(),
        ];

        // Pagination
        $data = $query
            ->withCount('versions')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Vault Lock State
        $vaultUnlockedUntil = session()->get('vault_unlocked_until');
        $isVaultUnlocked = $vaultUnlockedUntil && Carbon::now()->lt(Carbon::parse($vaultUnlockedUntil));
        $hasVaultPin = !empty(auth()->user()->vault_pin);

        return view('safebox.index', compact(
            'data',
            'stats',
            'isVaultUnlocked',
            'hasVaultPin'
        ));
    }

    /**
     * Verify Master Vault PIN
     */
    public function verifyPin(Request $request)
    {
        $request->validate([
            'pin' => 'required|string',
        ]);

        $user = auth()->user();

        if (empty($user->vault_pin)) {
            return response()->json([
                'success' => false,
                'message' => 'Master PIN is not configured yet. Please set your PIN first.'
            ], 422);
        }

        if (Hash::check($request->pin, $user->vault_pin)) {
            session()->put('vault_unlocked_until', Carbon::now()->addMinutes(10));
            return response()->json([
                'success' => true,
                'message' => 'Vault unlocked successfully.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid Master PIN entered.'
        ], 403);
    }

    /**
     * Set or Update Master Vault PIN
     */
    public function setPin(Request $request)
    {
        $request->validate([
            'pin' => 'required|digits_between:4,6|confirmed',
        ]);

        $user = auth()->user();
        $user->vault_pin = Hash::make($request->pin);
        $user->save();

        session()->put('vault_unlocked_until', Carbon::now()->addMinutes(10));

        return back()->with('success', 'Master Security PIN configured successfully and vault unlocked.');
    }

    /**
     * Lock Vault immediately
     */
    public function lockVault()
    {
        session()->forget('vault_unlocked_until');

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Security vault has been locked.');
    }

    /**
     * Store Secret
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'      => 'required|string|max:255',
            'category'   => 'required|in:Password,Card,API_Key,Note',
            'secret'     => 'required|string',
            'status'     => 'required|in:Active,Locked,Archived',
            'expires_at' => 'nullable|date',
        ]);

        $meta = $this->buildMetadata($request);

        SafeBox::create([
            'user_id'    => auth()->id(),
            'title'      => $request->title,
            'category'   => $request->category,
            'secret'     => Crypt::encryptString($request->secret),
            'meta_data'  => !empty($meta) ? Crypt::encryptString(json_encode($meta)) : null,
            'status'     => $request->status,
            'expires_at' => $request->expires_at,
        ]);

        return redirect()
            ->back()
            ->with('success', 'New secret record saved securely.');
    }

    /**
     * Update Secret and record version history
     */
    public function update(Request $request, $id)
    {
        $box = SafeBox::where('user_id', auth()->id())->findOrFail($id);

        $request->validate([
            'title'         => 'required|string|max:255',
            'category'      => 'required|in:Password,Card,API_Key,Note',
            'secret'        => 'required|string',
            'status'        => 'required|in:Active,Locked,Archived',
            'expires_at'    => 'nullable|date',
            'change_reason' => 'nullable|string|max:255',
        ]);

        $currentDecryptedSecret = $box->decrypted_secret;
        $isSecretChanged = ($currentDecryptedSecret !== $request->secret);

        // Record version snapshot if secret changed
        if ($isSecretChanged) {
            SafeBoxVersion::create([
                'safe_box_id' => $box->id,
                'secret'      => $box->secret,
                'meta_data'   => $box->meta_data,
                'reason'      => $request->change_reason ?: 'Secret/password updated',
            ]);
        }

        $meta = $this->buildMetadata($request);

        $box->update([
            'title'      => $request->title,
            'category'   => $request->category,
            'secret'     => Crypt::encryptString($request->secret),
            'meta_data'  => !empty($meta) ? Crypt::encryptString(json_encode($meta)) : null,
            'status'     => $request->status,
            'expires_at' => $request->expires_at,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Secret updated successfully and previous version saved in history.');
    }

    /**
     * Fetch Version History for a SafeBox item
     */
    public function versions($id)
    {
        $box = SafeBox::where('user_id', auth()->id())->with('versions')->findOrFail($id);

        $versions = $box->versions->map(function ($ver) {
            $meta = [];
            if (!empty($ver->meta_data)) {
                try {
                    $meta = json_decode(Crypt::decryptString($ver->meta_data), true) ?: [];
                } catch (\Exception $e) {
                    $meta = [];
                }
            }

            return [
                'id'         => $ver->id,
                'secret'     => $ver->decrypted_secret,
                'reason'     => $ver->reason,
                'meta'       => $meta,
                'created_at' => $ver->created_at->format('d M, Y h:i A'),
            ];
        });

        return response()->json([
            'success'  => true,
            'title'    => $box->title,
            'category' => $box->category,
            'current'  => $box->decrypted_secret,
            'versions' => $versions,
        ]);
    }

    /**
     * Soft Delete
     */
    public function destroy($id)
    {
        $box = SafeBox::where('user_id', auth()->id())->findOrFail($id);
        $box->delete();

        return back()->with('success', 'Secret moved to Trash.');
    }

    /**
     * Trash Page
     */
    public function trash()
    {
        $data = SafeBox::onlyTrashed()
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('safebox.trash', compact('data'));
    }

    /**
     * Restore Secret
     */
    public function restore($id)
    {
        SafeBox::onlyTrashed()
            ->where('user_id', auth()->id())
            ->findOrFail($id)
            ->restore();

        return redirect()
            ->route('safebox.trash')
            ->with('success', 'Secret restored successfully.');
    }

    /**
     * Permanent Delete
     */
    public function forceDelete($id)
    {
        SafeBox::onlyTrashed()
            ->where('user_id', auth()->id())
            ->findOrFail($id)
            ->forceDelete();

        return redirect()
            ->route('safebox.trash')
            ->with('success', 'Secret permanently deleted.');
    }

    /**
     * Export SafeBox records to CSV
     */
    public function export()
    {
        return Excel::download(
            new SafeBoxExport,
            'safebox-records-' . now()->format('Y-m-d_H-i-s') . '.csv'
        );
    }

    /**
     * Helper to build metadata JSON by Category
     */
    private function buildMetadata(Request $request): array
    {
        $category = $request->category;
        $meta = [];

        if ($category === 'Password') {
            if ($request->filled('meta_username')) $meta['username'] = $request->meta_username;
            if ($request->filled('meta_url')) $meta['url'] = $request->meta_url;
        } elseif ($category === 'Card') {
            if ($request->filled('meta_card_holder')) $meta['card_holder'] = $request->meta_card_holder;
            if ($request->filled('meta_card_number')) $meta['card_number'] = $request->meta_card_number;
            if ($request->filled('meta_card_expiry')) $meta['card_expiry'] = $request->meta_card_expiry;
            if ($request->filled('meta_card_cvv')) $meta['card_cvv'] = $request->meta_card_cvv;
        } elseif ($category === 'API_Key') {
            if ($request->filled('meta_api_key')) $meta['api_key'] = $request->meta_api_key;
            if ($request->filled('meta_environment')) $meta['environment'] = $request->meta_environment;
        }

        return $meta;
    }
}
