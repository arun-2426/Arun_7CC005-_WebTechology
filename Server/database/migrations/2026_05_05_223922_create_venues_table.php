<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Grounds where matches are played. Stored per-user for the same reason
 * as teams — your "Home Ground" probably isn't anyone else's.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 160);
            $table->string('city', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->enum('pitch_type', ['turf', 'matting', 'astro', 'concrete', 'other'])
                  ->default('turf');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venues');
    }
};
