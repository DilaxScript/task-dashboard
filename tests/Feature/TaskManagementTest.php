<?php

namespace Tests\Feature;

use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_tasks(): void
    {
        $task = Task::query()->create([
            'title' => 'Prepare sprint review',
            'description' => 'Collect progress notes from the team.',
            'status' => TaskStatus::Pending,
        ]);

        $response = $this->get(route('tasks.index'));

        $response
            ->assertOk()
            ->assertViewIs('dashboard')
            ->assertSee($task->title);
    }

    public function test_task_can_be_created(): void
    {
        $response = $this->postJson(route('tasks.store'), [
            'title' => 'Build dashboard',
            'description' => 'Create the responsive task management interface.',
            'status' => TaskStatus::InProgress->value,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('task.title', 'Build dashboard')
            ->assertJsonPath('task.status', TaskStatus::InProgress->value);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Build dashboard',
            'status' => TaskStatus::InProgress->value,
        ]);
    }

    public function test_task_creation_is_validated(): void
    {
        $response = $this->postJson(route('tasks.store'), [
            'title' => '',
            'description' => '',
            'status' => 'Blocked',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'description', 'status']);
    }

    public function test_task_status_can_be_updated(): void
    {
        $task = Task::query()->create([
            'title' => 'Review pull request',
            'description' => 'Review backend validation and UI behavior.',
            'status' => TaskStatus::Pending,
        ]);

        $response = $this->patchJson(route('tasks.status.update', $task), [
            'status' => TaskStatus::Completed->value,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('task.status', TaskStatus::Completed->value);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => TaskStatus::Completed->value,
        ]);
    }

    public function test_invalid_status_update_is_rejected(): void
    {
        $task = Task::query()->create([
            'title' => 'Write documentation',
            'description' => 'Document local setup and application decisions.',
            'status' => TaskStatus::Pending,
        ]);

        $this->patchJson(route('tasks.status.update', $task), [
            'status' => 'Cancelled',
        ])->assertUnprocessable()->assertJsonValidationErrors('status');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => TaskStatus::Pending->value,
        ]);
    }

    public function test_task_can_be_edited(): void
    {
        $task = Task::query()->create([
            'title' => 'Draft task',
            'description' => 'This content needs to be updated.',
            'status' => TaskStatus::Pending,
        ]);

        $response = $this->putJson(route('tasks.update', $task), [
            'title' => 'Updated task',
            'description' => 'The task details have been updated.',
            'status' => TaskStatus::InProgress->value,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('task.title', 'Updated task')
            ->assertJsonPath('task.status', TaskStatus::InProgress->value);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated task',
            'status' => TaskStatus::InProgress->value,
        ]);
    }

    public function test_task_can_be_deleted(): void
    {
        $task = Task::query()->create([
            'title' => 'Temporary task',
            'description' => 'This task should be removed.',
            'status' => TaskStatus::Completed,
        ]);

        $this->deleteJson(route('tasks.destroy', $task))
            ->assertOk()
            ->assertJsonPath('message', 'Task deleted successfully.');

        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }
}
