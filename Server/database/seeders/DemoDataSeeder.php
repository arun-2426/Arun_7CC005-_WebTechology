<?php

namespace Database\Seeders;

use App\Models\GameMatch;
use App\Models\Performance;
use App\Models\Team;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Populates the database with a realistic single-season demo dataset
 * for the user "demo@cricket.test". Lets the marker (or you) log in
 * and see populated tables, charts, and filters straight away —
 * without first having to register and log fifteen matches by hand.
 *
 * Numbers are hand-picked rather than randomised so the dashboard
 * tells a coherent story: a club season starting slowly, peaking
 * mid-summer with a couple of big innings, dipping in autumn.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // ----- Demo user --------------------------------------------------
        $user = User::updateOrCreate(
            ['email' => 'demo@cricket.test'],
            [
                'name' => 'Demo Player',
                'password' => Hash::make('password123'),
            ],
        );

        // ----- Teams (3 own + 12 opponent = 15 teams) ---------------------
        $own = collect([
            ['name' => 'Wolverhampton CC 1st XI',  'home_ground' => 'Danescourt'],
            ['name' => 'Wolverhampton CC 2nd XI',  'home_ground' => 'Danescourt'],
            ['name' => 'University of Wolverhampton', 'home_ground' => 'Walsall Road Sports Ground'],
        ])->map(fn ($t) => Team::create([
            'user_id'     => $user->id,
            'name'        => $t['name'],
            'type'        => 'own',
            'home_ground' => $t['home_ground'],
        ]));

        $opponents = collect([
            'Brewood CC',
            'Wombourne CC',
            'Penkridge CC',
            'Codsall CC',
            'Tettenhall CC',
            'Bridgnorth CC',
            'Stafford CC',
            'Cannock CC',
            'Kinver CC',
            'Old Hill CC',
            'Himley CC',
            'Aston Manor CC',
        ])->map(fn ($name) => Team::create([
            'user_id' => $user->id,
            'name'    => $name,
            'type'    => 'opponent',
        ]));

        // ----- Venues -----------------------------------------------------
        $venues = collect([
            ['name' => 'Danescourt',                     'city' => 'Wolverhampton', 'pitch_type' => 'turf'],
            ['name' => 'Walsall Road Sports Ground',     'city' => 'Wolverhampton', 'pitch_type' => 'turf'],
            ['name' => 'Brewood Recreation Ground',      'city' => 'Brewood',       'pitch_type' => 'turf'],
            ['name' => 'Wombourne Cricket Club',         'city' => 'Wombourne',     'pitch_type' => 'turf'],
            ['name' => 'Cannock Park',                   'city' => 'Cannock',       'pitch_type' => 'turf'],
            ['name' => 'Stafford County Ground',         'city' => 'Stafford',      'pitch_type' => 'turf'],
            ['name' => 'Bridgnorth Cricket Club',        'city' => 'Bridgnorth',    'pitch_type' => 'turf'],
            ['name' => 'Penkridge Pavilion',             'city' => 'Penkridge',     'pitch_type' => 'matting'],
            ['name' => 'Old Hill Cricket Ground',        'city' => 'Cradley Heath', 'pitch_type' => 'turf'],
            ['name' => 'Himley Hall Park',               'city' => 'Himley',        'pitch_type' => 'turf'],
        ])->map(fn ($v) => Venue::create([
            'user_id'    => $user->id,
            'name'       => $v['name'],
            'city'       => $v['city'],
            'country'    => 'United Kingdom',
            'pitch_type' => $v['pitch_type'],
        ]));

        // ----- 15 matches with performances -------------------------------
        // Layout per row:
        //   [date, format, own_idx, opp_idx, venue_idx, result, own_score, opp_score,
        //    bat_runs, bat_balls, bat_4s, bat_6s, dismissal,
        //    bowl_overs, bowl_maidens, bowl_runs, bowl_wkts,
        //    field_ct, field_st, field_ro, notes]
        $matches = [
            // Pre-season friendly — rust on, low score
            ['2024-11-15', 'Club',  1, 0,  0, 'lost',      '112 all-out', '113/3',
                14, 28, 1, 0, 'caught',   '3.0', 0, 22, 0,  0, 0, 0,
                'Cold morning, struggled to time the ball'],

            // Indoor winter friendly
            ['2024-12-08', 'Club',  1, 5,  0, 'won',       '141/6',  '120 all-out',
                32, 38, 4, 1, 'not_out',  '4.0', 0, 28, 1,  1, 0, 0,
                'Indoor nets warm-up — felt fluent'],

            // 2025 season opener
            ['2025-04-12', 'Club',  0, 1,  2, 'won',       '187/8',  '160 all-out',
                28, 41, 3, 0, 'bowled',   '6.0', 1, 32, 2,  0, 0, 0,
                'Season opener — bowling rhythm came back quickly'],

            // First half-century of the year
            ['2025-04-26', 'Club',  0, 2,  3, 'won',       '215/4',  '198 all-out',
                64, 41, 8, 2, 'not_out',  '5.0', 0, 41, 1,  1, 0, 0,
                'First fifty of the season, brought up with a six'],

            // Tough loss, stand-out fielding
            ['2025-05-10', 'T20',   1, 6,  4, 'lost',      '142/7',  '143/4',
                12, 9,  2, 1, 'caught',   '4.0', 0, 38, 1,  2, 0, 1,
                'Two slip catches and a run-out, but could not hold the chase'],

            // Big innings
            ['2025-05-24', 'Club',  0, 3,  5, 'won',       '248/3',  '189 all-out',
                102, 88, 12, 3, 'not_out', '4.0', 1, 22, 0, 0, 0, 0,
                'Career-best — second hundred ever, against a quality attack'],

            // Tied match — rare and useful for dashboard variety
            ['2025-06-14', 'Club',  0, 4,  1, 'tied',      '174 all-out', '174/9',
                21, 30, 2, 0, 'lbw',      '7.0', 0, 41, 3,  1, 0, 0,
                'Tied off the last ball — three wickets in the final over'],

            // Drawn (Test-style 2-day fixture)
            ['2025-06-28', 'Test',  0, 7,  6, 'drawn',     '301/6 dec', '244/8',
                47, 92, 5, 0, 'caught',   '12.0', 3, 58, 2, 0, 0, 0,
                'Two-day game — declared after lunch, opposition held on'],

            // Big bowling day
            ['2025-07-12', 'ODI',   0, 8,  7, 'won',       '231/9',  '180 all-out',
                18, 27, 1, 1, 'run_out',  '8.0', 1, 34, 4,  1, 0, 0,
                'Career-best bowling — four-for on a dry matting wicket'],

            // Forgettable batting day
            ['2025-07-26', 'Club',  1, 9,  8, 'lost',      '98 all-out',  '99/2',
                0,  3,  0, 0, 'bowled',   '4.0', 0, 32, 1,  0, 0, 0,
                'Golden duck, bowled middle stump — one of those days'],

            // No-result (rained off after innings break)
            ['2025-08-09', 'Club',  0, 10, 9, 'no_result', '156/4',  '12/0',
                38, 44, 4, 1, 'not_out',  '0.0', 0, 0,  0,  0, 0, 0,
                'Rain stopped play after 3 overs of their reply'],

            // Solid all-round day
            ['2025-08-23', 'T20',   1, 11, 0, 'won',       '163/5',  '147/9',
                41, 28, 5, 2, 'caught',   '4.0', 0, 27, 2,  1, 0, 0,
                'Hit the ball cleanly, gave away nothing at the death'],

            // Tail-end of the season — stumpings + keepers' day
            ['2025-09-13', 'Club',  2, 0,  1, 'lost',      '122 all-out', '124/4',
                18, 22, 2, 0, 'stumped',  '0.0', 0, 0,  0,  2, 1, 0,
                'Kept wicket for the uni 1st XI — busy day behind the stumps'],

            // Late-season win
            ['2025-09-27', 'Club',  0, 1,  2, 'won',       '195/7',  '170 all-out',
                57, 49, 7, 1, 'caught',   '6.0', 1, 30, 2,  1, 0, 0,
                'Knock to set up the chase, removed both openers later'],

            // Most recent — fresh on the dashboard
            ['2026-04-20', 'T20',   0, 6,  4, 'won',       '178/5',  '152/8',
                73, 52, 9, 3, 'not_out',  '4.0', 0, 31, 1,  0, 0, 0,
                'Pre-season tour match — finished unbeaten, backed up with a wicket'],
        ];

        foreach ($matches as $m) {
            $match = GameMatch::create([
                'user_id'          => $user->id,
                'match_date'       => $m[0],
                'format'           => $m[1],
                'own_team_id'      => $own[$m[2]]->id,
                'opponent_team_id' => $opponents[$m[3]]->id,
                'venue_id'         => $venues[$m[4]]->id,
                'result'           => $m[5],
                'own_team_score'   => $m[6],
                'opponent_score'   => $m[7],
                'notes'            => $m[20],
            ]);

            Performance::create([
                'match_id'           => $match->id,
                'batting_runs'       => $m[8],
                'batting_balls'      => $m[9],
                'batting_fours'      => $m[10],
                'batting_sixes'      => $m[11],
                'batting_dismissal'  => $m[12],
                'bowling_overs'      => $m[13],
                'bowling_maidens'    => $m[14],
                'bowling_runs'       => $m[15],
                'bowling_wickets'    => $m[16],
                'fielding_catches'   => $m[17],
                'fielding_stumpings' => $m[18],
                'fielding_runouts'   => $m[19],
            ]);
        }
    }
}
