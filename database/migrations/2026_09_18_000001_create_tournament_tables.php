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
        // 1. Kategori Turnamen (Kelompok Usia / Instansi / Umum)
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Tahapan / Babak Turnamen (Babak Grup, Perempat Final, Semifinal, Final)
        Schema::create('stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', ['group', 'knockout'])->default('group');
            $table->integer('order_num')->default(1);
            $table->timestamps();
        });

        // 3. Grup Turnamen (Grup A, Grup B, atau 1 Grup Tunggal)
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stage_id')->constrained('stages')->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        // 4. Tim Futsal
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 10)->nullable();
            $table->string('logo')->nullable();
            $table->string('manager_name')->nullable();
            $table->string('manager_contact')->nullable();
            $table->timestamps();
        });

        // 5. Pemain Futsal
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('jersey_number');
            $table->enum('position', ['GK', 'DEF', 'FLA', 'PIV'])->default('FLA');
            $table->string('photo')->nullable();
            $table->boolean('is_captain')->default(false);
            $table->timestamps();

            $table->unique(['team_id', 'jersey_number']);
        });

        // 6. Pertandingan (Matches)
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('stage_id')->nullable()->constrained('stages')->nullOnDelete();
            $table->foreignId('group_id')->nullable()->constrained('groups')->nullOnDelete();
            $table->foreignId('home_team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('away_team_id')->constrained('teams')->cascadeOnDelete();
            $table->integer('home_score')->default(0);
            $table->integer('away_score')->default(0);
            $table->dateTime('match_date');
            $table->string('venue')->default('Arena Futsal Utama');
            $table->enum('status', [
                'scheduled',
                'first_half',
                'half_time',
                'second_half',
                'extra_time',
                'finished',
                'postponed',
            ])->default('scheduled');
            $table->integer('current_minute')->default(0);
            $table->timestamps();
        });

        // 7. Lini Masa & Statistik Pertandingan (Match Events)
        Schema::create('match_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('player_id')->nullable()->constrained('players')->nullOnDelete();
            $table->foreignId('assist_player_id')->nullable()->constrained('players')->nullOnDelete();
            $table->enum('event_type', [
                'goal',
                'own_goal',
                'yellow_card',
                'red_card',
                'second_yellow',
                'assist',
            ]);
            $table->integer('minute')->default(1);
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_events');
        Schema::dropIfExists('matches');
        Schema::dropIfExists('players');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('groups');
        Schema::dropIfExists('stages');
        Schema::dropIfExists('categories');
    }
};
