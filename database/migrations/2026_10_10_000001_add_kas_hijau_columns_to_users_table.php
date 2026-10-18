<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan kolom khusus Kas Hijau ke tabel users bawaan Laravel:
 *  - phone          : nomor HP untuk kontak & notifikasi.
 *  - role           : peran sederhana (warga | petugas | admin). Nilai
 *                     dipetakan ke App\Enums\UserRole di level aplikasi;
 *                     permission granular mengikuti via spatie/laravel-permission.
 *  - balance_points : saldo poin terkini (cerminan balance_after pada
 *                     entri point_ledgers terakhir user).
 *
 * Catatan: migrasi ini mengasumsikan tabel users masih memakai struktur
 * bawaan Laravel. Sesuaikan bila kolom phone sudah ada sebelumnya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('phone', 20)->nullable()->after('name')
                ->comment('Nomor HP warga/petugas untuk kontak & notifikasi.');

            // String + enum cast (bukan SQL ENUM) agar mudah dikembangkan;
            // nilai divalidasi di level aplikasi melalui App\Enums\UserRole.
            $table->string('role', 20)->default('warga')->index()->after('phone')
                ->comment('warga | petugas | admin (lihat App\Enums\UserRole).');

            $table->unsignedBigInteger('balance_points')->default(0)->after('role')
                ->comment('Saldo poin terkini; riwayat lengkap ada di point_ledgers.');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['role']);
            $table->dropColumn(['phone', 'role', 'balance_points']);
        });
    }
};
