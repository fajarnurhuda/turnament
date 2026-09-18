<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->unsignedInteger('timer_seconds')->default(0)->after('current_minute');
            $table->boolean('timer_running')->default(false)->after('timer_seconds');
            $table->timestamp('timer_started_at')->nullable()->after('timer_running');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropColumn(['timer_seconds', 'timer_running', 'timer_started_at']);
        });
    }
};
