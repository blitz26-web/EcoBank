<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Header transaksi penyetoran sampah.
 *
 * Alur status: pending -> completed | cancelled.
 * Poin dikreditkan ke saldo warga HANYA saat status menjadi completed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deposits', function (Blueprint $table): void {
            $table->id();
            $table->string('deposit_code', 30)->unique()
                ->comment('Kode unik setoran, mis. DEP-20261010-7F3K9Q.');

            // Catatan keuangan tidak boleh ikut terhapus secara diam-diam:
            // pakai restrict agar data historis selalu utuh.
            $table->foreignId('user_id')->constrained()->restrictOnDelete()
                ->comment('Warga pemilik setoran.');
            $table->foreignId('officer_id')->nullable()->constrained('users')->nullOnDelete()
                ->comment('Petugas pemverifikasi; null selama masih pending.');

            $table->unsignedBigInteger('total_points')->default(0)
                ->comment('Total poin = penjumlahan points_earned pada deposit_items.');
            $table->string('status', 20)->default('pending')
                ->comment('pending | completed | cancelled (App\Enums\DepositStatus).');

            $table->timestamps();

            $table->index('status');
            $table->index(['user_id', 'status'], 'deposits_user_status_index');
            $table->index(['officer_id', 'status'], 'deposits_officer_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};
