<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SafeBoxVersion extends Model
{
    protected $fillable = [
        'safe_box_id',
        'secret',
        'meta_data',
        'reason',
    ];

    public function safeBox()
    {
        return $this->belongsTo(SafeBox::class);
    }

    public function getDecryptedSecretAttribute(): string
    {
        try {
            return Crypt::decryptString($this->secret);
        } catch (\Exception $e) {
            return '*** Encryption Error ***';
        }
    }
}
