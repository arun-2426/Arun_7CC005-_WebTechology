<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MatchPhoto extends Model
{
    use HasFactory;

    protected $fillable = ['match_id', 'file_path', 'caption'];

    /**
     * The full URL the SPA can render in an <img>. Computed rather than
     * stored so we can swap from the local disk to S3/CDN later without
     * touching any rows.
     */
    protected $appends = ['url'];

    public function match(): BelongsTo
    {
        return $this->belongsTo(GameMatch::class, 'match_id');
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }
}
