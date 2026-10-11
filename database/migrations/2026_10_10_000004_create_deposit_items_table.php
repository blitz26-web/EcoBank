<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rincian jenis sampah per penyetoran (detail transaksi).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deposit_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('deposit_id')->constrained()->cascadeOnDelete()
                ->comment('Setoran induk; item ikut terhapus bersama setorannya.');
            $table->foreignId('waste_category_id')->constrained()->restrictOnDelete()
                ->comment('Kategori sampah; hapus kategori diblokir bila sudah dipakai.');

            $table->decimal('weight_in_kg', 8, 2)
                ->comment('Berat sampah dalam kilogram.');
            $table->unsignedBigInteger('points_earned')->default(0)
                ->comment('weight_in_kg x point_per_unit; dihitung ulang oleh server.');

            $table->timestamps();

            $table->index('waste_category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deposit_items');
    }
};
