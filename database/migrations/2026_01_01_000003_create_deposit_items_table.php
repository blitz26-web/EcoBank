<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rincian item sampah dalam satu penyetoran.
 *
 * Invariant bisnis:
 * - points_earned = weight_in_kg * waste_categories.point_per_unit
 *   (dihitung server-side di DepositService, tidak pernah dari input klien).
 *
 * Strategi onDelete:
 * - deposit_id        : cascade — rincian ikut terhapus bersama header-nya.
 * - waste_category_id : restrict — kategori yang sudah terpakai tidak boleh
 *                       dihapus demi menjaga integritas riwayat.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('deposit_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('deposit_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('waste_category_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->decimal('weight_in_kg', 8, 2);
            $table->unsignedBigInteger('points_earned')->default(0);
            $table->timestamps();

            $table->index('deposit_id');
            $table->index('waste_category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposit_items');
    }
};
