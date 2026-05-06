<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teams\StoreTeamRequest;
use App\Http\Requests\Teams\UpdateTeamRequest;
use App\Http\Resources\TeamResource;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TeamController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        // Always scope by the logged-in user — never trust a query param to
        // tell us whose teams to return.
        $teams = $request->user()
            ->teams()
            ->orderBy('type')      // 'own' first, then 'opponent'
            ->orderBy('name')
            ->get();

        return TeamResource::collection($teams);
    }

    public function store(StoreTeamRequest $request): JsonResponse
    {
        $team = $request->user()->teams()->create($request->validated());

        return (new TeamResource($team))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Team $team): TeamResource
    {
        $this->ensureOwnership($request, $team);
        return new TeamResource($team);
    }

    public function update(UpdateTeamRequest $request, Team $team): TeamResource
    {
        $this->ensureOwnership($request, $team);
        $team->update($request->validated());

        return new TeamResource($team);
    }

    public function destroy(Request $request, Team $team): JsonResponse
    {
        $this->ensureOwnership($request, $team);
        $team->delete();

        return response()->json(null, 204);
    }

    /**
     * Tiny helper rather than a full Policy class — the rule is the same
     * everywhere ("you own this row"), and policies would be ceremony for
     * a one-line check. If the project grows we'd refactor to TeamPolicy.
     */
    private function ensureOwnership(Request $request, Team $team): void
    {
        abort_unless($team->user_id === $request->user()->id, 404);
    }
}
