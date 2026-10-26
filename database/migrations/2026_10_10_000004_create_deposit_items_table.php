<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deposit_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('deposit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('waste_category_id')->constrained()->restrictOnDelete();

            $table->decimal('weight_in_kg', 8, 2);
            $table->unsignedBigInteger('points_earned')->default(0);

            $table->timestamps();

            $table->index('waste_category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deposit_items');
    }
};
