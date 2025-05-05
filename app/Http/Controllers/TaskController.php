<?php

namespace App\Http\Controllers;

use App\Events\StatusUpdated;
use App\Events\TaskCreated;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function create(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'nullable|string',
            ]);

            $task = Task::create([
                'user_id' => $request->attributes->get('user_id'),
                'title' => $validatedData['title'],
                'description' => $validatedData['description'] ?? null,
                'status' => $validatedData['status'] ?? 'In Progress',
            ]);

            event(new TaskCreated($task));

            return $this->successResponse(new TaskResource($task), 201);
        } catch (\Exception $e) {
            return $this->errorResponse('validation_error', $e->getMessage(), 400);
        }
    }

    public function index(Request $request)
    {
        try {
            $userId = $request->attributes->get('user_id');

            $tasks = Task::where('user_id', $userId)
                ->with('tags')
                ->get();

            return $this->successResponse(TaskResource::collection($tasks));
        } catch (\Exception $e) {
            return $this->errorResponse('server_error', $e->getMessage(), 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $task = Task::find($id);

            if (! $task) {
                return $this->errorResponse('not_found', 'Task not found', 404);
            }

            if ($task->user_id !== $request->attributes->get('user_id')) {
                return $this->errorResponse('access_denied', 'You do not have permission to access this task', 403);
            }

            return $this->successResponse(new TaskResource($task));
        } catch (\Exception $e) {
            return $this->errorResponse('server_error', $e->getMessage(), 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $task = Task::find($id);

            if (! $task) {
                return $this->errorResponse('not_found', 'Task not found', 404);
            }

            if ($task->user_id !== $request->attributes->get('user_id')) {
                return $this->errorResponse('access_denied', 'You do not have permission to update this task', 403);
            }

            $validatedData = $request->validate([
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|nullable|string',
                'status' => 'sometimes|in:In Progress,Completed',
            ]);

            if (isset($validatedData['status']) && $validatedData['status'] !== $task->status) {
                event(new StatusUpdated(
                    [
                        'new_status' => $validatedData['status'],
                        'update_type' => 'status',
                        'user_id' => $request->attributes->get('user_id'),
                    ]
                ));
            }

            $task->update([
                'title' => $validatedData['title'] ?? $task->title,
                'description' => $validatedData['description'] ?? $task->description,
                'status' => $validatedData['status'] ?? $task->status,
            ]);

            return $this->successResponse(new TaskResource($task));
        } catch (\Exception $e) {
            return $this->errorResponse('validation_error', $e->getMessage(), 400);
        }
    }

    public function delete(Request $request, $id)
    {
        try {
            $task = Task::find($id);

            if (! $task) {
                return $this->errorResponse('not_found', 'Task not found', 404);
            }

            if ($task->user_id !== $request->attributes->get('user_id')) {
                return $this->errorResponse('access_denied', 'You do not have permission to delete this task', 403);
            }

            $task->delete();

            return $this->successResponse(null, 204); // 204 No Content
        } catch (\Exception $e) {
            return $this->errorResponse('server_error', $e->getMessage(), 500);
        }
    }
}
