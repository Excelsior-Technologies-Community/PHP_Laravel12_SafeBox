<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SafeBox;
use Illuminate\Support\Facades\Crypt;

class SafeBoxController extends Controller
{
    /**
     * SafeBox Dashboard
     * Features:
     * - Search
     * - Status Filter
     * - Pagination
     * - Statistics
     */
    public function index(Request $request)
    {
        $query = SafeBox::where('user_id', auth()->id());

        // Search
        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        // Status Filter
        if ($request->filled('status')) {

            $query->where('status', $request->status);
        }

        // Statistics
        $stats = [
            'total' => SafeBox::where('user_id', auth()->id())->count(),

            'active' => SafeBox::where('user_id', auth()->id())
                ->where('status', 'Active')
                ->count(),

            'locked' => SafeBox::where('user_id', auth()->id())
                ->where('status', 'Locked')
                ->count(),

            'archived' => SafeBox::where('user_id', auth()->id())
                ->where('status', 'Archived')
                ->count(),
        ];

        // Pagination
        $data = $query
            ->oldest()
            ->paginate(5)
            ->withQueryString();

        return view('safebox.index', compact(
            'data',
            'stats'
        ));
    }

    /**
     * Store Secret
     */
    public function store(Request $request)
    {
        $request->validate([

            'title' => 'required|max:255',

            'secret' => 'required',

            'status' => 'required|in:Active,Locked,Archived'

        ]);

        SafeBox::create([

            'user_id' => auth()->id(),

            'title' => $request->title,

            'secret' => Crypt::encryptString($request->secret),

            'status' => $request->status,

        ]);

        return redirect()
            ->back()
            ->with('success', 'Secret saved successfully.');
    }

    /**
     * Soft Delete
     */
    public function destroy($id)
    {
        $box = SafeBox::where('user_id', auth()->id())
            ->findOrFail($id);

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
            ->paginate(5);

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
}
