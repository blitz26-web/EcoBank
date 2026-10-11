<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kategori sampah beserta konversi poin per satuan (default: kg).
 *
 * Contoh data: (name: "Plastik PET", unit: "kg", point_per_unit: 10)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('waste_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('unit', 20)->default('kg');
            $table->unsignedInteger('point_per_unit');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waste_categories');
    }
};
