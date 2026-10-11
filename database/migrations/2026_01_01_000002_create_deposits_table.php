<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Header transaksi penyetoran sampah.
 *
 * Strategi onDelete:
 * - user_id   : restrict — data finansial tidak boleh hilang jika user dihapus.
 * - officer_id: null on delete — riwayat tetap ada meskipun akun petugas dihapus.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('deposits', function (Blueprint $table): void {
            $table->id();
            $table->string('deposit_code', 30)->unique();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('officer_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->unsignedBigInteger('total_points')->default(0);
            $table->string('status', 20)->default('pending')->index();
            $table->timestamps();

            // Riwayat penyetoran per warga (paling sering diquery).
            $table->index(['user_id', 'created_at']);
            $table->index('officer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};
