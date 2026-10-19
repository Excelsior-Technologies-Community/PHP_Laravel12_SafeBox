<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

class SafeBox extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'category',
        'secret',
        'meta_data',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function versions()
    {
        return $this->hasMany(SafeBoxVersion::class)->latest();
    }

    public function getDecryptedSecretAttribute(): string
    {
        try {
            return Crypt::decryptString($this->secret);
        } catch (\Exception $e) {
            return '*** Encryption Error ***';
        }
    }

    public function getDecryptedMetaAttribute(): array
    {
        if (empty($this->meta_data)) {
            return [];
        }

        try {
            $decrypted = Crypt::decryptString($this->meta_data);
            return json_decode($decrypted, true) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return $this->expires_at->isPast();
    }

    public function isExpiringSoon(): bool
    {
        if (!$this->expires_at || $this->isExpired()) {
            return false;
        }

        return $this->expires_at->diffInDays(Carbon::today()) <= 7;
    }
}
