<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Aggregated stats for the dashboard. Returns a single JSON payload so
 * the SPA's dashboard renders in one round-trip rather than a flurry of
 * individual /matches calls.
 */
class StatsController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        // Headline counters
        $totals = DB::table('matches')
            ->where('user_id', $userId)
            ->selectRaw('
                COUNT(*) AS matches_total,
                SUM(CASE WHEN result = "won"  THEN 1 ELSE 0 END) AS wins,
                SUM(CASE WHEN result = "lost" THEN 1 ELSE 0 END) AS losses,
                SUM(CASE WHEN result = "drawn" THEN 1 ELSE 0 END) AS draws,
                SUM(CASE WHEN result = "tied" THEN 1 ELSE 0 END) AS ties,
                SUM(CASE WHEN result = "no_result" THEN 1 ELSE 0 END) AS no_results
            ')
            ->first();

        // Batting + bowling — joined to performances. left join because a
        // user might have a match with no performance recorded yet.
        $batting = DB::table('matches')
            ->where('matches.user_id', $userId)
            ->leftJoin('performances', 'matches.id', '=', 'performances.match_id')
            ->selectRaw('
                COALESCE(SUM(performances.batting_runs), 0)  AS total_runs,
                COALESCE(SUM(performances.batting_balls), 0) AS total_balls,
                COALESCE(SUM(performances.batting_fours), 0) AS total_fours,
                COALESCE(SUM(performances.batting_sixes), 0) AS total_sixes,
                COALESCE(SUM(CASE WHEN performances.batting_dismissal NOT IN ("not_out","did_not_bat")
                    THEN 1 ELSE 0 END), 0) AS times_out
            ')
            ->first();

        $bowling = DB::table('matches')
            ->where('matches.user_id', $userId)
            ->leftJoin('performances', 'matches.id', '=', 'performances.match_id')
            ->selectRaw('
                COALESCE(SUM(performances.bowling_runs), 0)    AS total_runs_conceded,
                COALESCE(SUM(performances.bowling_wickets), 0) AS total_wickets,
                COALESCE(SUM(performances.bowling_maidens), 0) AS total_maidens
            ')
            ->first();

        // Runs over time — useful as a chart on the dashboard.
        $runsTimeline = DB::table('matches')
            ->where('matches.user_id', $userId)
            ->leftJoin('performances', 'matches.id', '=', 'performances.match_id')
            ->orderBy('matches.match_date')
            ->select('matches.match_date', 'performances.batting_runs')
            ->limit(50) // most recent 50 to keep the payload small
            ->get();

        $battingAverage = $batting->times_out > 0
            ? round($batting->total_runs / $batting->times_out, 2)
            : null;

        $strikeRate = $batting->total_balls > 0
            ? round(($batting->total_runs / $batting->total_balls) * 100, 2)
            : null;

        return response()->json([
            'totals'   => [
                'matches_total' => (int) $totals->matches_total,
                'wins'          => (int) $totals->wins,
                'losses'        => (int) $totals->losses,
                'draws'         => (int) $totals->draws,
                'ties'          => (int) $totals->ties,
                'no_results'    => (int) $totals->no_results,
            ],
            'batting'  => [
                'total_runs'     => (int) $batting->total_runs,
                'total_balls'    => (int) $batting->total_balls,
                'total_fours'    => (int) $batting->total_fours,
                'total_sixes'    => (int) $batting->total_sixes,
                'times_out'      => (int) $batting->times_out,
                'average'        => $battingAverage,
                'strike_rate'    => $strikeRate,
            ],
            'bowling'  => [
                'total_runs_conceded' => (int) $bowling->total_runs_conceded,
                'total_wickets'       => (int) $bowling->total_wickets,
                'total_maidens'       => (int) $bowling->total_maidens,
            ],
            'timeline' => $runsTimeline,
        ]);
    }
}
