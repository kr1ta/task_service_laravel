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
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            $habit = Habit::create([
                'user_id' => $request->attributes->get('user_id'),
                'title' => $validatedData['title'],
                'description' => $validatedData['description'] ?? null,
            ]);

            return response()->json([
                'data' => new HabitResource($habit),
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
            \Log::info("request: {$request}");
            $userId = $request->attributes->get('user_id');

            $habits = Habit::where('user_id', $userId)->get();

            return response()->json([
                'data' => HabitResource::collection($habits),
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
            $habit = Habit::find($id);

            if (! $habit) {
                return response()->json([
                    'data' => null,
                    'errors' => [
                        [
                            'code' => 'not_found',
                            'message' => 'Habit not found',
                        ],
                    ],
                ], 404);
            }

            return response()->json([
                'data' => new HabitResource($habit),
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
