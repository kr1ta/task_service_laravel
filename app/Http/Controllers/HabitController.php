<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Habit;

class HabitController extends Controller
{
    public function create(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $validatedData = collect($validatedData); // Преобразуем в коллекцию

        $task = Habit::create([
            'user_id' => $request->attributes->get('user_id'),
            'title' => $validatedData->get('title'),
            'description' => $validatedData->get('description'),
        ]);

        return response()->json([
            'message' => 'Привычка успешно создана!',
            'task' => $task,
        ], 201);
    }

    public function index(Request $request)
    {
        \Log::info("request: {$request}");
        $userId = $request->attributes->get('user_id');

        $habits = Habit::where('user_id', $userId)->get();

        return response()->json($habits, 200);
    }

    public function show(Request $request, $id)
    {
        $habit = Habit::find($id);

        if (!$habit) {
            return response()->json([
                'message' => 'Habit not found'
            ], 404);
        }

        return response()->json($habit, 200);
    }
}