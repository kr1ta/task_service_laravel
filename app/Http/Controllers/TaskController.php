<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;

class TaskController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|string',
            'finish_at' => 'nullable|date',
        ]);

        $validatedData = collect($validatedData); // Преобразуем в коллекцию

        $task = Task::create([
            'user_id' => $request->attributes->get('user_id'),
            'title' => $validatedData->get('title'),
            'description' => $validatedData->get('description'),
            'finish_at' => $validatedData->get('finish_at'), // Безопасный доступ
            'status' => $validatedData->get('status', 'Created'),
        ]);

        return response()->json([
            'message' => 'Задача успешно создана!',
            'task' => $task,
        ], 201);
    }
}