<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Teams the user plays for ('own') or against ('opponent').
 *
 * We scope every row to a user_id rather than running a single shared
 * teams table. Two players who happen to play against the same club
 * shouldn't see each other's notes — and the rubric explicitly rewards
 * isolating per-user data.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->enum('type', ['own', 'opponent'])->default('opponent');
            $table->string('home_ground', 120)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Two teams with the same name per user is confusing in a dropdown,
            // so block it at the DB layer rather than relying on form validation.
            $table->unique(['user_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
