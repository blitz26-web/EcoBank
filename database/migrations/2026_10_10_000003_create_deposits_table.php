<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deposits', function (Blueprint $table): void {
            $table->id();
            $table->string('deposit_code', 30)->unique();

            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('officer_id')->nullable()->constrained('users')->nullOnDelete();

            $table->unsignedBigInteger('total_points')->default(0);
            $table->string('status', 20)->default('pending');

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
