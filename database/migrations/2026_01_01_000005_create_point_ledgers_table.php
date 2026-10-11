<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Buku besar (ledger) mutasi poin — audit trail yang bersifat IMMUTABLE.
 *
 * Aturan:
 * - Setiap perubahan users.balance_points WAJIB menghasilkan satu baris baru
 *   di sini (credit untuk penambahan, debit untuk pengurangan).
 * - Baris ledger TIDAK PERNAH diupdate maupun dihapus; koreksi saldo selalu
 *   dilakukan melalui entri baru.
 * - reference_type + reference_id (polymorphic) menghubungkan mutasi dengan
 *   transaksi sumbernya (Deposit, Withdrawal, dll).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('point_ledgers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('type', 10); // credit | debit
            $table->unsignedBigInteger('amount');
            $table->morphs('reference');
            $table->string('description')->nullable();
            $table->unsignedBigInteger('balance_after');
            $table->timestamps();

            // Rekonstruksi riwayat saldo per warga secara kronologis.
            $table->index(['user_id', 'created_at']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_ledgers');
    }
};
