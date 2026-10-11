<?php

declare(strict_types=1);

namespace App\Enums;

enum LedgerType: string
{
    case Credit = 'credit';
    case Debit = 'debit';

    /**
     * Label arah mutasi poin untuk ditampilkan di UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::Credit => 'Poin Masuk',
            self::Debit => 'Poin Keluar',
        };
    }

    /**
     * Tanda arah mutasi: +1 untuk kredit, -1 untuk debit.
     * Berguna saat mengagregasi saldo dari ledger.
     */
    public function sign(): int
    {
        return match ($this) {
            self::Credit => 1,
            self::Debit => -1,
        };
    }

    /**
     * Kelas warna badge Bootstrap 5 sesuai arah mutasi.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Credit => 'success',
            self::Debit => 'danger',
        };
    }
}
