<?php

namespace App\Http\Controllers;

use App\Models\TimeInterval;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TimeIntervalController extends Controller
{
    public function start(Request $request, $task_id)
    {
        $request->merge(['task_id' => $task_id]);

        $request->validate([
            'task_id' => 'required|exists:tasks,id',
        ]);

        $timeInterval = TimeInterval::where('task_id', $task_id)
            ->whereNull('finish_at') // Ищем только интервалы без finish_at
            ->latest('start_at') // Берем самый последний по времени start_at
            ->first();

        if ($timeInterval) {
            return response()->json([
                'message' => 'Interval already started! Try to stop it!',
            ], 404);
        }

        $timeInterval = TimeInterval::create([
            'task_id' => $task_id,
            'start_at' => now(),
        ]);

        return response()->json([
            'message' => 'Time interval started successfully.',
            'time_interval' => $timeInterval,
        ], 201);
    }

    public function stop(Request $request, $task_id)
    {
        $request->merge(['task_id' => $task_id]);
        $request->validate([
            'task_id' => 'required|exists:tasks,id',
        ]);

        $timeInterval = TimeInterval::where('task_id', $task_id)
            ->whereNull('finish_at') // Ищем только интервалы без finish_at
            ->latest('start_at') // Берем самый последний по времени start_at
            ->first();

        if (!$timeInterval) {
            return response()->json([
                'message' => 'No active time interval found for the given task.',
            ], 404);
        }

        $timeInterval->update([
            'finish_at' => now(),
        ]);

        $timeInterval = $timeInterval->fresh();

        $spentTime = Carbon::parse($timeInterval->finish_at)->diffInMinutes(Carbon::parse($timeInterval->start_at));

        return response()->json([
            'message' => 'Time interval stopped successfully.',
            'spent_time_in_seconds' => $spentTime,
            'time_interval' => $timeInterval,
        ], 200);
    }
}