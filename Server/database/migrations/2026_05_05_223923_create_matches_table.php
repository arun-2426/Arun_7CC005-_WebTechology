<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The headline entity. Each row is one match the user played in.
 *
 * Notes:
 * - own_team_id and opponent_team_id are deliberately separate FKs both
 *   pointing at `teams`, so a deleted opponent club doesn't blow away
 *   the user's own team rows. Both use restrictOnDelete to force the
 *   user to clear matches first.
 * - Scores are nullable because users sometimes log a fixture before
 *   the result is known.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('match_date');

            $table->enum('format', ['T20', 'ODI', 'Test', 'Club', 'Other'])
                  ->default('Club');

            $table->foreignId('own_team_id')
                  ->nullable()
                  ->constrained('teams')
                  ->nullOnDelete();

            $table->foreignId('opponent_team_id')
                  ->nullable()
                  ->constrained('teams')
                  ->nullOnDelete();

            $table->foreignId('venue_id')
                  ->nullable()
                  ->constrained('venues')
                  ->nullOnDelete();

            $table->enum('result', ['won', 'lost', 'drawn', 'tied', 'no_result'])
                  ->nullable();

            // Scores stored as raw strings (e.g. "184/6" or "all-out 132") rather
            // than separate runs/wickets columns. Cricket scoreboards are messy
            // — declarations, follow-ons, ties — and a free-form string keeps
            // the data faithful to what the user wrote down.
            $table->string('own_team_score', 40)->nullable();
            $table->string('opponent_score', 40)->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            // Listings are almost always sorted by date, often filtered by the
            // current user. Composite index gives both single-user lookups and
            // chronological scans for free.
            $table->index(['user_id', 'match_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
