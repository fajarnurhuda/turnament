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
        Schema::create('referees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('license')->nullable();
            $table->string('phone')->nullable();
            $table->string('city')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->foreignId('referee_1_id')->nullable()->constrained('referees')->nullOnDelete();
            $table->foreignId('referee_2_id')->nullable()->constrained('referees')->nullOnDelete();
            $table->foreignId('referee_3_id')->nullable()->constrained('referees')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropForeign(['referee_1_id']);
            $table->dropForeign(['referee_2_id']);
            $table->dropForeign(['referee_3_id']);
            $table->dropColumn(['referee_1_id', 'referee_2_id', 'referee_3_id']);
        });

        Schema::dropIfExists('referees');
    }
};
