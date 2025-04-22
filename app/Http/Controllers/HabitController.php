<?php

namespace App\Http\Controllers;

use App\Http\Resources\HabitResource;
use App\Models\Habit;
use Illuminate\Http\Request;

class HabitController extends Controller
{
    public function create(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $validatedData = collect($validatedData); // Преобразуем в коллекцию

        $habit = Habit::create([
            'user_id' => $request->attributes->get('user_id'),
            'title' => $validatedData->get('title'),
            'description' => $validatedData->get('description'),
        ]);

        return new HabitResource($habit);
    }

    public function index(Request $request)
    {
        \Log::info("request: {$request}");
        $userId = $request->attributes->get('user_id');

        $habits = Habit::where('user_id', $userId)->get();

        return HabitResource::collection($habits);
    }

    public function show(Request $request, $id)
    {
        $habit = Habit::find($id);

        if (! $habit) {
            return response()->json([
                'message' => 'Habit not found',
            ], 404);
        }

        return new HabitResource($habit);
    }
}
