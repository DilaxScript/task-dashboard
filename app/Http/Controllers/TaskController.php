<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(): View
    {
        return view('dashboard', [
            'tasks' => Task::query()->latest()->get(),
            'statuses' => TaskStatus::values(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $task = Task::query()->create($this->validateTask($request));

        return response()->json([
            'message' => 'Task created successfully.',
            'task' => $task->fresh(),
        ], 201);
    }

    public function updateStatus(Request $request, Task $task): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(TaskStatus::class)],
        ]);

        $task->update($validated);

        return response()->json([
            'message' => 'Task status updated.',
            'task' => $task->fresh(),
        ]);
    }

    public function update(Request $request, Task $task): JsonResponse
    {
        $task->update($this->validateTask($request));

        return response()->json([
            'message' => 'Task updated successfully.',
            'task' => $task->fresh(),
        ]);
    }

    public function destroy(Task $task): JsonResponse
    {
        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateTask(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:1000'],
            'status' => ['required', Rule::enum(TaskStatus::class)],
        ]);
    }
}
