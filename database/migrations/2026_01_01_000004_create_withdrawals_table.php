<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pengajuan penarikan saldo poin warga menjadi uang tunai.
 *
 * Invariant bisnis: points_amount tidak boleh melebihi users.balance_points
 * pada saat pengajuan (divalidasi di WithdrawalService — Langkah 2).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('withdrawals', function (Blueprint $table): void {
            $table->id();
            $table->string('withdrawal_code', 30)->unique();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->unsignedBigInteger('points_amount');
            $table->decimal('cash_amount', 12, 2);
            $table->string('payment_method', 30); // bank_transfer | e_wallet | cash
            $table->string('account_number', 50)->nullable();
            $table->string('status', 20)->default('requested')->index();
            $table->timestamps();

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};
