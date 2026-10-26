<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WithdrawalStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Withdrawal extends Model
{
    use HasFactory;

    public const PAYMENT_BANK_TRANSFER = 'bank_transfer';
    public const PAYMENT_E_WALLET = 'e_wallet';
    public const PAYMENT_CASH = 'cash';

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'officer_id');
    }

    public static function paymentLabels(): array
    {
        return self::PAYMENT_LABELS;
    }

    public function paymentLabel(): string
    {
        return self::PAYMENT_LABELS[$this->payment_method] ?? $this->payment_method;
    }

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
