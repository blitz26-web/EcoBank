<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepositItem extends Model
{
    /** @use HasFactory<\Database\Factories\DepositItemFactory> */
    use HasFactory;

    protected $fillable = [
        'deposit_id',
        'waste_category_id',
        'weight_in_kg',
        'points_earned',
    ];

    protected function casts(): array
    {
        return [
            'weight_in_kg' => 'decimal:2',
            'points_earned' => 'integer',
        ];
    }

    /**
     * Setoran induk dari item ini.
     */
    public function deposit(): BelongsTo
    {
        return $this->belongsTo(Deposit::class);
    }

    /**
     * Kategori sampah untuk item ini.
     */
    public function wasteCategory(): BelongsTo
    {
        return $this->belongsTo(WasteCategory::class);
    }
}
