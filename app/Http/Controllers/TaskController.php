<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Events\TaskCreated;

class TaskController extends Controller
{
    public function create(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        $validatedData = collect($validatedData); // Преобразуем в коллекцию

        $task = Task::create([
            'user_id' => $request->attributes->get('user_id'),
            'title' => $validatedData->get('title'),
            'description' => $validatedData->get('description'),
            'status' => $validatedData->get('status', 'In Progress'),
        ]);

        // event(new TaskCreated($task));

        return response()->json([
            'message' => 'Задача успешно создана!',
            'task' => $task,
        ], 201);
    }

    public function index(Request $request)
    {
        $userId = $request->attributes->get('user_id');

        $tasks = Task::where('user_id', $userId)->get();

        return response()->json($tasks, 200);
    }

    public function show(Request $request, $id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json([
                'message' => 'Task not found'
            ], 404);
        }

        return response()->json($task, 200);
    }
}