<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Evolusi Langkah 2: kolom administrasi untuk tabel withdrawals.
 *  - officer_id      : petugas/admin yang memproses atau menolak penarikan
 *                      (akuntabilitas tindak lanjut).
 *  - processed_at    : waktu pembayaran disalurkan.
 *  - rejected_reason : alasan penolakan, tampil ke warga dan tersimpan
 *                      untuk audit; wajib diisi lewat WithdrawalService.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('withdrawals', function (Blueprint $table): void {
            $table->foreignId('officer_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete()
                ->comment('Petugas/admin yang memproses / menolak penarikan.');
            $table->timestamp('processed_at')->nullable()->after('status')
                ->comment('Waktu penarikan ditandai selesai dibayar.');
            $table->string('rejected_reason', 255)->nullable()->after('processed_at')
                ->comment('Alasan penolakan; terisi bila status rejected.');
        });
    }

    public function down(): void
    {
        Schema::table('withdrawals', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('officer_id');
            $table->dropColumn(['processed_at', 'rejected_reason']);
        });
    }
};
