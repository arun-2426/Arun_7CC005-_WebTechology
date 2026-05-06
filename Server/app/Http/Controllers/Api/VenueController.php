<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Venues\StoreVenueRequest;
use App\Http\Requests\Venues\UpdateVenueRequest;
use App\Http\Resources\VenueResource;
use App\Models\Venue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VenueController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $venues = $request->user()->venues()->orderBy('name')->get();
        return VenueResource::collection($venues);
    }

    public function store(StoreVenueRequest $request): JsonResponse
    {
        $venue = $request->user()->venues()->create($request->validated());

        return (new VenueResource($venue))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Venue $venue): VenueResource
    {
        $this->ensureOwnership($request, $venue);
        return new VenueResource($venue);
    }

    public function update(UpdateVenueRequest $request, Venue $venue): VenueResource
    {
        $this->ensureOwnership($request, $venue);
        $venue->update($request->validated());
        return new VenueResource($venue);
    }

    public function destroy(Request $request, Venue $venue): JsonResponse
    {
        $this->ensureOwnership($request, $venue);
        $venue->delete();
        return response()->json(null, 204);
    }

    private function ensureOwnership(Request $request, Venue $venue): void
    {
        abort_unless($venue->user_id === $request->user()->id, 404);
    }
}
