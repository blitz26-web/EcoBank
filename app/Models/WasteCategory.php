<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WasteCategory extends Model
{
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

    public function depositItems(): HasMany
    {
        return $this->hasMany(DepositItem::class);
    }

    public function calculatePoints(float $weightInKg): int
    {
        return (int) round($weightInKg * $this->point_per_unit);
    }
}
