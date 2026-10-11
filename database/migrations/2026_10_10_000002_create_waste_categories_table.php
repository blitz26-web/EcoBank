<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kategori sampah beserta tarif poin per satuan takarannya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waste_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100)->unique()
                ->comment('Nama kategori, mis. Botol PET, Kertas HVS, Kaleng Aluminium.');
            $table->string('unit', 20)->default('kg')
                ->comment('Satuan takaran, default kilogram.');
            $table->unsignedInteger('point_per_unit')
                ->comment('Jumlah poin per satu satuan (per kg).');

            $table->timestamps();

            $table->index('unit');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waste_categories');
    }
};
