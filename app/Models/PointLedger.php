<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LedgerType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LogicException;

class PointLedger extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'reference_type',
        'reference_id',
        'description',
        'balance_after',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'balance_after' => 'integer',
            'type' => LedgerType::class,
        ];
    }

    /**
     * User pemilik mutasi poin ini.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Model pemicu mutasi ini (Deposit, Withdrawal, dan lain-lain)
     * melalui relasi polimorfik.
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    protected static function booted(): void
    {
        /*
         * Imutabilitas ledger (audit trail):
         * entri ledger TIDAK BOLEH diubah maupun dihapus setelah dibuat.
         * Koreksi saldo selalu dilakukan dengan membuat ENTRI BARU
         * (misalnya entri debit koreksi), sehingga riwayat audit
         * tetap utuh dan dapat direkonsiliasi kapan pun.
         */
        static::updating(function (PointLedger $ledger): void {
            throw new LogicException('Entri point ledger bersifat imutabel dan tidak dapat diubah.');
        });

        static::deleting(function (PointLedger $ledger): void {
            throw new LogicException('Entri point ledger tidak dapat dihapus (audit trail).');
        });
    }
}
