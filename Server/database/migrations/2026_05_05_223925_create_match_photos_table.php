<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Photos attached to a match — scorecards, action shots, ticket stubs.
 *
 * We only store the path on disk, not the binary. Files live under
 * storage/app/public/match-photos and are served via Laravel's storage
 * symlink. That makes backups, swapping to S3 later, or moving to
 * mi-linux straightforward.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')
                  ->constrained('matches')
                  ->cascadeOnDelete();
            $table->string('file_path');
            $table->string('caption', 200)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_photos');
    }
};
