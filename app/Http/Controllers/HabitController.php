<?php

namespace App\Http\Controllers;

use App\Http\Resources\HabitResource;
use App\Models\Habit;
use Illuminate\Http\Request;

class HabitController extends Controller
{
    public function create(Request $request)
    {
        try {
            $validatedData = $this->validateHabitData($request);

            $habit = Habit::create([
                'user_id' => $request->attributes->get('user_id'),
                'title' => $validatedData['title'],
                'description' => $validatedData['description'] ?? null,
            ]);

            return $this->successResponse(new HabitResource($habit));
        } catch (\Exception $e) {
            return $this->errorResponse('validation_error', $e->getMessage(), 400);
        }
    }

    public function index(Request $request)
    {
        try {
            \Log::info("request: {$request}");
            $userId = $request->attributes->get('user_id');

            $habits = Habit::where('user_id', $userId)->get();

            return $this->successResponse(HabitResource::collection($habits));
        } catch (\Exception $e) {
            return $this->errorResponse('server_error', $e->getMessage(), 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $habit = Habit::find($id);

            if (! $habit) {
                return $this->errorResponse('not_found', 'Habit not found', 404);
            }

            if ($habit->user_id !== $request->attributes->get('user_id')) {
                return $this->errorResponse('access_denied', 'You do not have permission to access this habit', 403);
            }

            return $this->successResponse(new HabitResource($habit));
        } catch (\Exception $e) {
            return $this->errorResponse('server_error', $e->getMessage(), 500);
        }
    }

    private function validateHabitData(Request $request)
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
    }
}
