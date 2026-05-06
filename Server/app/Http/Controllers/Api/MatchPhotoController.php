<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MatchPhotoResource;
use App\Models\GameMatch;
use App\Models\MatchPhoto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;

/**
 * Photos belong to a match. Routes are nested:
 *   GET    /matches/{match}/photos
 *   POST   /matches/{match}/photos
 *   DELETE /matches/{match}/photos/{photo}
 */
class MatchPhotoController extends Controller
{
    public function index(Request $request, GameMatch $match): AnonymousResourceCollection
    {
        $this->ensureMatchOwnership($request, $match);
        return MatchPhotoResource::collection($match->photos()->latest()->get());
    }

    public function store(Request $request, GameMatch $match): JsonResponse
    {
        $this->ensureMatchOwnership($request, $match);

        // Strict allow-list — never trust the file extension or MIME header
        // alone. Laravel's `image` rule re-checks via getimagesize().
        $data = $request->validate([
            'photo'   => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'caption' => ['nullable', 'string', 'max:200'],
        ]);

        $path = $request->file('photo')->store('match-photos', 'public');

        $photo = $match->photos()->create([
            'file_path' => $path,
            'caption'   => $data['caption'] ?? null,
        ]);

        return (new MatchPhotoResource($photo))->response()->setStatusCode(201);
    }

    public function destroy(Request $request, GameMatch $match, MatchPhoto $photo): JsonResponse
    {
        $this->ensureMatchOwnership($request, $match);
        abort_unless($photo->match_id === $match->id, 404);

        // Delete the file too, otherwise storage just grows forever.
        Storage::disk('public')->delete($photo->file_path);
        $photo->delete();

        return response()->json(null, 204);
    }

    private function ensureMatchOwnership(Request $request, GameMatch $match): void
    {
        abort_unless($match->user_id === $request->user()->id, 404);
    }
}
