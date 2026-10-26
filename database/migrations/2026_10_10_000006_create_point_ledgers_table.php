<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('point_ledgers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();

            $table->string('type', 10);
            $table->unsignedBigInteger('amount');

            $table->morphs('reference');

            $table->string('description')->nullable();
            $table->unsignedBigInteger('balance_after');

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
