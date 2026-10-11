<?php

declare(strict_types=1);

namespace App\Enums;

enum WithdrawalStatus: string
{
    case Requested = 'requested';
    case Processed = 'processed';
    case Rejected = 'rejected';

    /**
     * Label yang ramah pengguna untuk ditampilkan di UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::Requested => 'Diajukan',
            self::Processed => 'Diproses',
            self::Rejected => 'Ditolak',
        };
    }

    /**
     * Kelas warna badge Bootstrap 5 sesuai status.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Requested => 'info',
            self::Processed => 'success',
            self::Rejected => 'danger',
        };
    }

    /**
     * Ikon Bootstrap Icons sesuai status.
     */
    public function icon(): string
    {
        return match ($this) {
            self::Requested => 'bi-hourglass-split',
            self::Processed => 'bi-cash-coin',
            self::Rejected => 'bi-x-circle-fill',
        };
    }
}
