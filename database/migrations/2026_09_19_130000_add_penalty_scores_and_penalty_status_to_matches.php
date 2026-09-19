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
            $table->unsignedTinyInteger('home_penalty_score')->nullable()->after('away_score');
            $table->unsignedTinyInteger('away_penalty_score')->nullable()->after('home_penalty_score');
            $table->string('status', 30)->default('scheduled')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropColumn(['home_penalty_score', 'away_penalty_score']);
        });
    }
};
