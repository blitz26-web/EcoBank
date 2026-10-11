<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pengajuan penukaran poin menjadi uang tunai.
 *
 * Alur status: requested -> processed | rejected.
 * Poin dipotong dari saldo warga saat pengajuan (requested) dan
 * dikembalikan bila ditolak — logikanya ada di WithdrawalService.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('withdrawals', function (Blueprint $table): void {
            $table->id();
            $table->string('withdrawal_code', 30)->unique()
                ->comment('Kode unik penarikan, mis. WDR-20261010-AB12CD.');
            $table->foreignId('user_id')->constrained()->restrictOnDelete()
                ->comment('Warga pengaju penarikan.');

            $table->unsignedBigInteger('points_amount')
                ->comment('Jumlah poin yang ditarik; tidak boleh melebihi saldo.');
            $table->decimal('cash_amount', 12, 2)
                ->comment('Nilai rupiah yang dibayarkan.');
            $table->string('payment_method', 30)
                ->comment('bank_transfer | e_wallet | cash (konstanta di model Withdrawal).');
            $table->string('account_number', 50)->nullable()
                ->comment('No. rekening / e-wallet tujuan; null untuk pembayaran tunai.');
            $table->string('status', 20)->default('requested')
                ->comment('requested | processed | rejected (App\Enums\WithdrawalStatus).');

            $table->timestamps();

            $table->index('status');
            $table->index(['user_id', 'status'], 'withdrawals_user_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};
