<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('withdrawals', function (Blueprint $table): void {
            $table->foreignId('officer_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable()->after('status');
            $table->string('rejected_reason', 255)->nullable()->after('processed_at');
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
