<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;

class TaskController extends Controller
{
    public function store(Request $request)
    {
        // Валидация входных данных
        $validatedData = $request->validate([
            'id_user' => 'required|integer',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|string',
            'finish_at' => 'nullable|date',
        ]);

        $task = Task::create([
            'id_user' => $validatedData['id_user'],
            'title' => $validatedData['title'],
            'description' => $validatedData['description'] ?? null,
            'status' => $validatedData['status'] ?? 'Created',
            'finish_at' => $validatedData['finish_at'] ?? null,
        ]);

        return response()->json([
            'message' => 'Задача успешно создана!',
            'task' => $task,
        ], 201);
    }
}
