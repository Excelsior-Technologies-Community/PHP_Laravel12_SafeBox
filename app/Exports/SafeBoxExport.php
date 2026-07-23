<?php

namespace App\Exports;

use App\Models\SafeBox;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SafeBoxExport implements FromCollection, WithHeadings
{
    /**
     * Export only logged-in user's SafeBox records.
     */
    public function collection()
    {
        return SafeBox::where('user_id', Auth::id())
            ->get()
            ->map(function ($item) {
                return [
                    'Title'      => $item->title,
                    'Secret'     => Crypt::decryptString($item->secret),
                    'Status'     => $item->status,
                    'Created At' => $item->created_at->format('d-m-Y h:i A'),
                ];
            });
    }

    /**
     * CSV Header
     */
    public function headings(): array
    {
        return [
            'Title',
            'Secret',
            'Status',
            'Created At',
        ];
    }
}