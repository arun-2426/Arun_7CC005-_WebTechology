<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_crud_full_cycle(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        // Create
        $create = $this->postJson('/api/teams', [
            'name' => 'Wolverhampton XI',
            'type' => 'own',
        ])->assertCreated()->json();
        $teamId = $create['data']['id'];

        // List
        $this->getJson('/api/teams')->assertOk()->assertJsonCount(1, 'data');

        // Show
        $this->getJson("/api/teams/{$teamId}")
             ->assertOk()
             ->assertJsonPath('data.name', 'Wolverhampton XI');

        // Update
        $this->putJson("/api/teams/{$teamId}", ['name' => 'Wolves XI'])
             ->assertOk()
             ->assertJsonPath('data.name', 'Wolves XI');

        // Delete
        $this->deleteJson("/api/teams/{$teamId}")->assertNoContent();
        $this->assertDatabaseMissing('teams', ['id' => $teamId]);
    }

    public function test_a_user_cannot_see_anothers_team(): void
    {
        $alice = User::factory()->create();
        $bob   = User::factory()->create();
        $alicesTeam = Team::factory()->for($alice)->create();

        $this->actingAs($bob, 'sanctum')
             ->getJson("/api/teams/{$alicesTeam->id}")
             ->assertNotFound();
    }

    public function test_duplicate_team_name_for_same_user_is_rejected(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $this->postJson('/api/teams', ['name' => 'Wolves', 'type' => 'own'])
             ->assertCreated();

        $this->postJson('/api/teams', ['name' => 'Wolves', 'type' => 'opponent'])
             ->assertStatus(422)
             ->assertJsonValidationErrors('name');
    }
}
