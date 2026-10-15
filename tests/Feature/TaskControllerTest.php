<?php

namespace Tests\Feature;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_tasks(): void
    {
        Task::factory()->count(3)->create();

        $response = $this->getJson('/api/tasks');

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
    }

    public function test_index_returns_latest_first(): void
    {
        $old = Task::factory()->create(['title' => 'Old task']);
        $new = Task::factory()->create(['title' => 'New task']);

        $response = $this->getJson('/api/tasks');

        $response->assertOk();
        $this->assertEquals($new->id, $response->json('data.0.id'));
        $this->assertEquals($old->id, $response->json('data.1.id'));
    }

    public function test_can_create_a_task(): void
    {
        $response = $this->postJson('/api/tasks', [
            'title' => 'Write tests',
            'description' => 'Cover all CRUD endpoints',
            'completed' => false,
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.title', 'Write tests');
        $response->assertJsonPath('data.description', 'Cover all CRUD endpoints');
        $response->assertJsonPath('data.completed', false);

        $this->assertDatabaseHas('tasks', ['title' => 'Write tests']);
    }

    public function test_can_create_a_task_with_only_title(): void
    {
        $response = $this->postJson('/api/tasks', [
            'title' => 'Minimal task',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.title', 'Minimal task');
        $response->assertJsonPath('data.description', null);
        $response->assertJsonPath('data.completed', false);
    }

    public function test_create_validates_required_title(): void
    {
        $response = $this->postJson('/api/tasks', [
            'description' => 'Missing title',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('title');
    }

    public function test_create_validates_title_length(): void
    {
        $response = $this->postJson('/api/tasks', [
            'title' => str_repeat('x', 256),
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('title');
    }

    public function test_can_show_a_task(): void
    {
        $task = Task::factory()->create();

        $response = $this->getJson("/api/tasks/{$task->id}");

        $response->assertOk();
        $response->assertJsonPath('data.id', $task->id);
        $response->assertJsonPath('data.title', $task->title);
    }

    public function test_show_returns_404_for_missing_task(): void
    {
        $response = $this->getJson('/api/tasks/999');

        $response->assertNotFound();
    }

    public function test_can_update_a_task(): void
    {
        $task = Task::factory()->create(['completed' => false]);

        $response = $this->putJson("/api/tasks/{$task->id}", [
            'title' => 'Updated title',
            'completed' => true,
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.title', 'Updated title');
        $response->assertJsonPath('data.completed', true);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated title',
            'completed' => true,
        ]);
    }

    public function test_can_partially_update_a_task(): void
    {
        $task = Task::factory()->create(['title' => 'Original']);

        $response = $this->putJson("/api/tasks/{$task->id}", [
            'completed' => true,
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.title', 'Original');
        $response->assertJsonPath('data.completed', true);
    }

    public function test_update_validates_title_when_present(): void
    {
        $task = Task::factory()->create();

        $response = $this->putJson("/api/tasks/{$task->id}", [
            'title' => '',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('title');
    }

    public function test_can_delete_a_task(): void
    {
        $task = Task::factory()->create();

        $response = $this->deleteJson("/api/tasks/{$task->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_delete_returns_404_for_missing_task(): void
    {
        $response = $this->deleteJson('/api/tasks/999');

        $response->assertNotFound();
    }
}
