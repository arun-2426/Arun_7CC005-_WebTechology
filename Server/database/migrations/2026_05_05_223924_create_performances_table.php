<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The user's individual stat line for a given match.
 *
 * One match -> one performance. Could be modelled as a column set on
 * `matches`, but pulling it out keeps the matches table narrow, lets
 * us add bowling figures or fielding aggregates without disturbing
 * the master record, and reads better in the SPA.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performances', function (Blueprint $table) {
            $table->id();

            // unique() not unique-constrained at row level — there's only ever
            // meant to be one performance per match for the logged-in player.
            $table->foreignId('match_id')
                  ->unique()
                  ->constrained('matches')
                  ->cascadeOnDelete();

            // Batting
            $table->unsignedSmallInteger('batting_runs')->default(0);
            $table->unsignedSmallInteger('batting_balls')->default(0);
            $table->unsignedTinyInteger('batting_fours')->default(0);
            $table->unsignedTinyInteger('batting_sixes')->default(0);
            $table->enum('batting_dismissal', [
                'not_out', 'bowled', 'caught', 'lbw', 'run_out',
                'stumped', 'hit_wicket', 'retired', 'did_not_bat',
            ])->default('did_not_bat');

            // Bowling — overs as a string because cricket scores 4.3, not 4.5
            $table->string('bowling_overs', 8)->nullable();
            $table->unsignedTinyInteger('bowling_maidens')->default(0);
            $table->unsignedSmallInteger('bowling_runs')->default(0);
            $table->unsignedTinyInteger('bowling_wickets')->default(0);

            // Fielding
            $table->unsignedTinyInteger('fielding_catches')->default(0);
            $table->unsignedTinyInteger('fielding_stumpings')->default(0);
            $table->unsignedTinyInteger('fielding_runouts')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performances');
    }
};
