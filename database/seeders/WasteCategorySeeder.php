<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\WasteCategory;
use Illuminate\Database\Seeder;

class WasteCategorySeeder extends Seeder
{
    private const CATEGORIES = [
        ['name' => 'Botol PET', 'unit' => 'kg', 'point_per_unit' => 10],
        ['name' => 'Kertas HVS', 'unit' => 'kg', 'point_per_unit' => 5],
        ['name' => 'Kardus / Duplex', 'unit' => 'kg', 'point_per_unit' => 4],
        ['name' => 'Kaleng Aluminium', 'unit' => 'kg', 'point_per_unit' => 25],
        ['name' => 'Plastik Kresek', 'unit' => 'kg', 'point_per_unit' => 3],
        ['name' => 'Beling / Kaca', 'unit' => 'kg', 'point_per_unit' => 2],
    ];

    public function run(): void
    {
        foreach (self::CATEGORIES as $category) {
            WasteCategory::query()->updateOrCreate(
                ['name' => $category['name']],
                [
                    'unit' => $category['unit'],
                    'point_per_unit' => $category['point_per_unit'],
                ],
            );

            $this->command->line(sprintf(
                'Kategori: %s (%d poin/%s)',
                $category['name'],
                $category['point_per_unit'],
                $category['unit'],
            ));
        }
    }
}
