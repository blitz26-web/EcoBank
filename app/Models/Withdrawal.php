<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WithdrawalStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Withdrawal extends Model
{
    /** @use HasFactory<\Database\Factories\WithdrawalFactory> */
    use HasFactory;

    /** Metode pembayaran yang didukung (nilai valid kolom payment_method). */
    public const PAYMENT_BANK_TRANSFER = 'bank_transfer';
    public const PAYMENT_E_WALLET = 'e_wallet';
    public const PAYMENT_CASH = 'cash';

    /**
     * Label ramah pengguna untuk setiap metode pembayaran.
     * Dipakai di form pengajuan, tabel antrian, dan riwayat penarikan.
     */
    public const PAYMENT_LABELS = [
        self::PAYMENT_BANK_TRANSFER => 'Transfer Bank',
        self::PAYMENT_E_WALLET => 'E-Wallet',
        self::PAYMENT_CASH => 'Tunai di Loket',
    ];

    protected $fillable = [
        'withdrawal_code',
        'user_id',
        'officer_id',
        'points_amount',
        'cash_amount',
        'payment_method',
        'account_number',
        'status',
        'processed_at',
        'rejected_reason',
    ];

    protected function casts(): array
    {
        return [
            'points_amount' => 'integer',
            'cash_amount' => 'decimal:2',
            'status' => WithdrawalStatus::class,
            'processed_at' => 'datetime',
        ];
    }

    /**
     * Warga pengaju penarikan ini.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Petugas/admin yang memproses atau menolak penarikan ini.
     * Bernilai null selama pengajuan belum ditindaklanjuti.
     */
    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'officer_id');
    }

    /* ------------------------------------------------------------------ */
    /* Helper metode pembayaran                                            */
    /* ------------------------------------------------------------------ */

    /**
     * Daftar label metode pembayaran untuk dropdown/radio form.
     *
     * @return array<string, string>
     */
    public static function paymentLabels(): array
    {
        return self::PAYMENT_LABELS;
    }

    /**
     * Label metode pembayaran penarikan ini; bila nilai kolom tidak
     * dikenal (mis. data lama), tampilkan nilai mentahnya apa adanya.
     */
    public function paymentLabel(): string
    {
        return self::PAYMENT_LABELS[$this->payment_method] ?? $this->payment_method;
    }

    /* ------------------------------------------------------------------ */
    /* Helper status                                                       */
    /* ------------------------------------------------------------------ */

    public function isRequested(): bool
    {
        return $this->status === WithdrawalStatus::Requested;
    }

    public function isProcessed(): bool
    {
        return $this->status === WithdrawalStatus::Processed;
    }

    public function isRejected(): bool
    {
        return $this->status === WithdrawalStatus::Rejected;
    }
}
