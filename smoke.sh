#!/usr/bin/env bash
set -e

API=http://127.0.0.1:8765/api

echo '== register =='
TOKEN=$(curl -s -X POST $API/auth/register \
  -H 'Content-Type: application/json' -H 'Accept: application/json' \
  -d '{"name":"E2E2","email":"e2e2@test.local","password":"PassWord1","password_confirmation":"PassWord1"}' \
  | python3 -c 'import json,sys; print(json.load(sys.stdin)["token"])')
echo "  token=$TOKEN"

echo '== create teams =='
OWN=$(curl -s -X POST $API/teams \
  -H "Authorization: Bearer $TOKEN" -H 'Content-Type: application/json' -H 'Accept: application/json' \
  -d '{"name":"Wolves XI","type":"own"}' \
  | python3 -c 'import json,sys; print(json.load(sys.stdin)["data"]["id"])')
OPP=$(curl -s -X POST $API/teams \
  -H "Authorization: Bearer $TOKEN" -H 'Content-Type: application/json' -H 'Accept: application/json' \
  -d '{"name":"Birmingham CC","type":"opponent"}' \
  | python3 -c 'import json,sys; print(json.load(sys.stdin)["data"]["id"])')
echo "  own=$OWN  opp=$OPP"

echo '== create venue =='
VEN=$(curl -s -X POST $API/venues \
  -H "Authorization: Bearer $TOKEN" -H 'Content-Type: application/json' -H 'Accept: application/json' \
  -d '{"name":"Edgbaston","city":"Birmingham","country":"England","pitch_type":"turf"}' \
  | python3 -c 'import json,sys; print(json.load(sys.stdin)["data"]["id"])')
echo "  venue=$VEN"

echo '== log match =='
PAYLOAD=$(cat <<JSON
{
  "match_date": "2026-04-15",
  "format": "T20",
  "own_team_id": $OWN,
  "opponent_team_id": $OPP,
  "venue_id": $VEN,
  "result": "won",
  "own_team_score": "184/6",
  "opponent_score": "160/9",
  "performance": {
    "batting_runs": 64,
    "batting_balls": 41,
    "batting_fours": 7,
    "batting_sixes": 2,
    "batting_dismissal": "caught"
  }
}
JSON
)
curl -s -X POST $API/matches \
  -H "Authorization: Bearer $TOKEN" -H 'Content-Type: application/json' -H 'Accept: application/json' \
  -d "$PAYLOAD" | python3 -m json.tool | head -30

echo '== summary =='
curl -s -H "Authorization: Bearer $TOKEN" -H 'Accept: application/json' $API/stats/summary | python3 -m json.tool
