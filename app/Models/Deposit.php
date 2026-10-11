<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DepositStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deposit extends Model
{
    /** @use HasFactory<\Database\Factories\DepositFactory> */
    use HasFactory;

    protected $fillable = [
        'deposit_code',
        'user_id',
        'officer_id',
        'total_points',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'total_points' => 'integer',
            'status' => DepositStatus::class,
        ];
    }

    /**
     * Warga pemilik setoran ini.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Petugas yang memverifikasi / mencatat setoran ini.
     * Bernilai null selama setoran masih menunggu verifikasi.
     */
    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'officer_id');
    }

    /**
     * Rincian jenis sampah yang disetorkan.
     */
    public function items(): HasMany
    {
        return $this->hasMany(DepositItem::class);
    }

    /* ------------------------------------------------------------------ */
    /* Helper status                                                       */
    /* ------------------------------------------------------------------ */

    public function isPending(): bool
    {
        return $this->status === DepositStatus::Pending;
    }

    public function isCompleted(): bool
    {
        return $this->status === DepositStatus::Completed;
    }

    public function isCancelled(): bool
    {
        return $this->status === DepositStatus::Cancelled;
    }
}
