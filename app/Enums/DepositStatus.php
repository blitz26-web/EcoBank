<?php

declare(strict_types=1);

namespace App\Enums;

enum DepositStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    /**
     * Label yang ramah pengguna untuk ditampilkan di UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Verifikasi',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
        };
    }

    /**
     * Kelas warna badge Bootstrap 5 sesuai status.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Completed => 'success',
            self::Cancelled => 'secondary',
        };
    }

    /**
     * Ikon Bootstrap Icons sesuai status.
     */
    public function icon(): string
    {
        return match ($this) {
            self::Pending => 'bi-hourglass-split',
            self::Completed => 'bi-check-circle-fill',
            self::Cancelled => 'bi-x-circle-fill',
        };
    }
}
