<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WasteCategory extends Model
{
    /** @use HasFactory<\Database\Factories\WasteCategoryFactory> */
    use HasFactory;

    public const DEFAULT_UNIT = 'kg';

    protected $fillable = [
        'name',
        'unit',
        'point_per_unit',
    ];

    protected function casts(): array
    {
        return [
            'point_per_unit' => 'integer',
        ];
    }

    /**
     * Semua baris item setoran yang memakai kategori ini.
     */
    public function depositItems(): HasMany
    {
        return $this->hasMany(DepositItem::class);
    }

    /**
     * Hitung poin untuk berat tertentu (satuan mengikuti kolom `unit`).
     *
     * Perhitungan SELALU dilakukan di sisi server agar tidak dapat
     * dimanipulasi oleh input dari klien. Hasil dibulatkan ke bilangan
     * bulat terdekat karena saldo poin disimpan sebagai integer.
     *
     * Contoh: 1.25 kg x 10 poin/kg = 12.5 => 13 poin.
     */
    public function calculatePoints(float $weightInKg): int
    {
        return (int) round($weightInKg * $this->point_per_unit);
    }
}
