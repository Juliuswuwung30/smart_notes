<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetCompletedUserNotesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_completed_notes_for_a_valid_user_id()
    {
        $user = User::factory()->create();
        $notes = Note::factory()->count(2)->create(['user_id' => $user->id]);

        // Mark notes as completed
        foreach ($notes as $note) {
            $note->todolist()->create(['is_finished' => true]);
        }

        $response = $this->getJson('/notes/completed?user_id=' . $user->id);

        $response->assertStatus(200)
                 ->assertJsonCount(2)
                 ->assertJsonStructure([
                     '*' => ['id', 'title', 'content', 'updatedAt']
                 ]);
    }

    /** @test */
    public function it_returns_400_if_user_id_is_missing()
    {
        $response = $this->getJson('/notes/completed');

        $response->assertStatus(400)
                 ->assertJson([
                     'error' => [
                         'code' => '400_BAD_REQUEST',
                         'message' => 'Missing or invalid user_id parameter'
                     ]
                 ]);
    }

    /** @test */
    public function it_returns_404_if_no_completed_notes_found_for_user()
    {
        $user = User::factory()->create();

        $response = $this->getJson('/notes/completed?user_id=' . $user->id);

        $response->assertStatus(404)
                 ->assertJson([
                     'error' => [
                         'code' => '404_NOT_FOUND',
                         'message' => 'No completed notes found for the specified user'
                     ]
                 ]);
    }
}