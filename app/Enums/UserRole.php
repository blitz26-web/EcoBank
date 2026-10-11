<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case Warga = 'warga';
    case Petugas = 'petugas';
    case Admin = 'admin';

    /**
     * Label yang ramah pengguna untuk ditampilkan di UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::Warga => 'Warga',
            self::Petugas => 'Petugas',
            self::Admin => 'Administrator',
        };
    }

    /**
     * Kelas warna badge Bootstrap 5 sesuai peran.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Warga => 'success',
            self::Petugas => 'primary',
            self::Admin => 'danger',
        };
    }
}
