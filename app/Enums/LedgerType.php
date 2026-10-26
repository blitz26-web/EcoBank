<?php

declare(strict_types=1);

namespace App\Enums;

enum LedgerType: string
{
    case Credit = 'credit';
    case Debit = 'debit';

    public function label(): string
    {
        return match ($this) {
            self::Credit => 'Poin Masuk',
            self::Debit => 'Poin Keluar',
        };
    }

    public function sign(): int
    {
        return match ($this) {
            self::Credit => 1,
            self::Debit => -1,
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Credit => 'success',
            self::Debit => 'danger',
        };
    }
}
