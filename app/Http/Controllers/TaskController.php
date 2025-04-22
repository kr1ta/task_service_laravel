<?php

namespace App\Http\Controllers;

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

            // event(new TaskCreated($task));

            return response()->json([
                'data' => new TaskResource($task),
                'errors' => [],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'data' => null,
                'errors' => [
                    [
                        'code' => 'validation_error',
                        'message' => $e->getMessage(),
                    ],
                ],
            ], 400);
        }
    }

    public function index(Request $request)
    {
        try {
            $userId = $request->attributes->get('user_id');

            $tasks = Task::where('user_id', $userId)
                ->with('tags')
                ->get();

            return response()->json([
                'data' => TaskResource::collection($tasks),
                'errors' => [],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'data' => [],
                'errors' => [
                    [
                        'code' => 'server_error',
                        'message' => $e->getMessage(),
                    ],
                ],
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $task = Task::find($id);

            if (! $task) {
                return response()->json([
                    'data' => null,
                    'errors' => [
                        [
                            'code' => 'not_found',
                            'message' => 'Task not found',
                        ],
                    ],
                ], 404);
            }

            return response()->json([
                'data' => new TaskResource($task),
                'errors' => [],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'data' => null,
                'errors' => [
                    [
                        'code' => 'server_error',
                        'message' => $e->getMessage(),
                    ],
                ],
            ], 500);
        }
    }
}
