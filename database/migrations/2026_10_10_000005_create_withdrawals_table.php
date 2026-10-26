<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('withdrawals', function (Blueprint $table): void {
            $table->id();
            $table->string('withdrawal_code', 30)->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();

            $table->unsignedBigInteger('points_amount');
            $table->decimal('cash_amount', 12, 2);
            $table->string('payment_method', 30);
            $table->string('account_number', 50)->nullable();
            $table->string('status', 20)->default('requested');

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
