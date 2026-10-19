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
                    'Title'       => $item->title,
                    'Category'    => $item->category ?? 'Note',
                    'Secret'      => $item->decrypted_secret,
                    'Status'      => $item->status,
                    'Expires At'  => $item->expires_at ? $item->expires_at->format('d-m-Y') : 'Never',
                    'Created At'  => $item->created_at->format('d-m-Y h:i A'),
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
            'Category',
            'Secret',
            'Status',
            'Expires At',
            'Created At',
        ];
    }
}