<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Matches\StoreMatchRequest;
use App\Http\Requests\Matches\UpdateMatchRequest;
use App\Http\Resources\MatchResource;
use App\Models\GameMatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * The headline resource for the app: the cricket match itself,
 * with its nested performance row, and (lazily) photos.
 */
class MatchController extends Controller
{
    /**
     * Listing endpoint with filtering. Query params:
     *   - format=T20|ODI|Test|Club|Other
     *   - result=won|lost|drawn|tied|no_result
     *   - venue_id=<int>
     *   - opponent_team_id=<int>
     *   - from=YYYY-MM-DD&to=YYYY-MM-DD
     *   - per_page=<int> (defaults to 15, capped at 100)
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) min($request->integer('per_page', 15), 100);

        $query = $request->user()
            ->matches()
            ->with(['ownTeam', 'opponentTeam', 'venue', 'performance'])
            ->orderBy('match_date', 'desc');

        // Apply only the filters the caller actually sent — anything else
        // would silently exclude rows.
        $query->when($request->filled('format'), fn ($q) =>
            $q->where('format', $request->string('format')));

        $query->when($request->filled('result'), fn ($q) =>
            $q->where('result', $request->string('result')));

        $query->when($request->filled('venue_id'), fn ($q) =>
            $q->where('venue_id', $request->integer('venue_id')));

        $query->when($request->filled('opponent_team_id'), fn ($q) =>
            $q->where('opponent_team_id', $request->integer('opponent_team_id')));

        $query->when($request->filled('from'), fn ($q) =>
            $q->whereDate('match_date', '>=', $request->date('from')));

        $query->when($request->filled('to'), fn ($q) =>
            $q->whereDate('match_date', '<=', $request->date('to')));

        $matches = $query->paginate($perPage);

        return MatchResource::collection($matches)->response();
    }

    public function store(StoreMatchRequest $request): JsonResponse
    {
        // Match + performance go in/out together. A transaction stops us
        // ending up with a match row but no performance (or vice versa)
        // if the second insert blows up.
        $match = DB::transaction(function () use ($request) {
            $payload = $request->validated();
            $performancePayload = $payload['performance'] ?? null;
            unset($payload['performance']);

            $match = $request->user()->matches()->create($payload);

            if ($performancePayload) {
                $match->performance()->create($performancePayload);
            }

            return $match;
        });

        $match->load(['ownTeam', 'opponentTeam', 'venue', 'performance']);

        return (new MatchResource($match))->response()->setStatusCode(201);
    }

    public function show(Request $request, GameMatch $match): MatchResource
    {
        $this->ensureOwnership($request, $match);

        $match->load(['ownTeam', 'opponentTeam', 'venue', 'performance', 'photos']);

        return new MatchResource($match);
    }

    public function update(UpdateMatchRequest $request, GameMatch $match): MatchResource
    {
        $this->ensureOwnership($request, $match);

        DB::transaction(function () use ($request, $match) {
            $payload = $request->validated();
            $performancePayload = $payload['performance'] ?? null;
            unset($payload['performance']);

            $match->update($payload);

            if ($performancePayload !== null) {
                // Upsert: there's only ever one performance per match.
                $match->performance()
                    ->updateOrCreate(['match_id' => $match->id], $performancePayload);
            }
        });

        $match->load(['ownTeam', 'opponentTeam', 'venue', 'performance']);

        return new MatchResource($match);
    }

    public function destroy(Request $request, GameMatch $match): JsonResponse
    {
        $this->ensureOwnership($request, $match);

        // Cascading FKs handle performance + photos.
        $match->delete();

        return response()->json(null, 204);
    }

    private function ensureOwnership(Request $request, GameMatch $match): void
    {
        abort_unless($match->user_id === $request->user()->id, 404);
    }
}
