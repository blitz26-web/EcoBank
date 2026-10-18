<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Buku besar (ledger) mutasi poin — audit trail keuangan Kas Hijau.
 *
 * Aturan imutabilitas: entri TIDAK pernah diubah/dihapus. Koreksi saldo
 * dilakukan dengan entri baru yang berlawanan arah (kompensasi).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('point_ledgers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete()
                ->comment('Pemilik mutasi poin; restrict agar audit trail tidak pernah hilang.');

            $table->string('type', 10)
                ->comment('credit (poin masuk) | debit (poin keluar) — App\Enums\LedgerType.');
            $table->unsignedBigInteger('amount')
                ->comment('Nilai mutasi selalu positif; arah ditentukan oleh kolom type.');

            // Kolom polimorfik: reference_type + reference_id + index gabungan.
            // Menunjuk model pemicu mutasi (Deposit, Withdrawal, dll).
            $table->morphs('reference');

            $table->string('description')->nullable()
                ->comment('Keterangan yang mudah dibaca manusia untuk laporan & audit.');
            $table->unsignedBigInteger('balance_after')
                ->comment('Saldo poin user SETELAH mutasi ini terjadi.');

            $table->timestamps();

            $table->index(['user_id', 'created_at'], 'point_ledgers_user_created_index');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_ledgers');
    }
};
