<?php

namespace App\Http\Controllers;

use App\Events\IntervalStarted;
use App\Events\IntervalStopped;
use App\Models\TimeInterval;
use App\Services\ResponseHelperService;
use App\Services\TypeResolver;
use Illuminate\Http\Request;

class TimeIntervalController extends Controller
{
    public function start(Request $request, $type, $id)
    {
        try {
            $validatedData = $this->validateStartRequest($request);

            if (! $this->isSupportedType($type) || ! $this->objectExists($type, $id)) {
                return ResponseHelperService::error([
                    [
                        'code' => 'not_found',
                        'message' => 'Unsupported type or object does not exist.',
                    ],
                ], 400);
            }

            $modelClass = TypeResolver::getModelClass($type);
            $activeInterval = $this->getLatestInterval($modelClass, $id);

            if ($activeInterval && ! $this->isIntervalCompletedByDuration($activeInterval)) {
                return ResponseHelperService::error([
                    [
                        'code' => 'conflict',
                        'message' => 'Interval already started! Try to stop it!',
                    ],
                ], 409);
            }

            $startTime = now();
            $duration = $validatedData['duration'];
            $finishTime = $startTime->copy()->addSeconds($duration);

            $timeInterval = TimeInterval::create([
                'intervalable_id' => $id,
                'intervalable_type' => $modelClass,
                'duration' => $duration,
                'start_time' => $startTime,
                'finish_time' => $finishTime,
            ]);

            $message = $this->buildMessageForEvent($type, $id, $duration, $modelClass, $request, 'start');

            event(new IntervalStarted($message));

            return ResponseHelperService::success($timeInterval, 201);
        } catch (\Exception $e) {
            return ResponseHelperService::error([
                [
                    'code' => 'server_error',
                    'message' => $e->getMessage(),
                ],
            ], 500);
        }
    }

    public function stop(Request $request, $type, $id)
    {
        try {
            if (! $this->isSupportedType($type) || ! $this->objectExists($type, $id)) {
                return ResponseHelperService::error([
                    [
                        'code' => 'not_found',
                        'message' => 'Unsupported type or object does not exist.',
                    ],
                ], 400);
            }

            $modelClass = TypeResolver::getModelClass($type);
            $activeInterval = $this->getLatestInterval($modelClass, $id);

            if (! $activeInterval || $this->isIntervalCompletedByDuration($activeInterval)) {
                return ResponseHelperService::error([
                    [
                        'code' => 'not_found',
                        'message' => 'No active time interval found for the given object.',
                    ],
                ], 404);
            }

            $spentTime = now()->diffInSeconds($activeInterval->start_time);
            $unspentTime = $activeInterval->duration - $spentTime;

            $activeInterval->update([
                'finish_time' => now(),
                'duration' => $spentTime,
            ]);

            $message = $this->buildMessageForEvent($type, $id, $spentTime, $modelClass, $request, 'stop');
            $message['unspent_time'] = $unspentTime;
            $message['early_completed'] = true;

            event(new IntervalStopped($message));

            return ResponseHelperService::success($activeInterval->fresh(), 200);
        } catch (\Exception $e) {
            return ResponseHelperService::error([
                [
                    'code' => 'server_error',
                    'message' => $e->getMessage(),
                ],
            ], 500);
        }
    }

    private function validateStartRequest(Request $request): array
    {
        return $request->validate([
            'duration' => 'required|integer',
        ]);
    }

    private function getLatestInterval(string $modelClass, int $id): ?TimeInterval
    {
        return TimeInterval::where('intervalable_id', $id)
            ->where('intervalable_type', $modelClass)
            ->latest('start_time')
            ->first();
    }

    private function buildMessageForEvent(
        string $type,
        int $id,
        int $duration,
        string $modelClass,
        Request $request,
        string $updateType
    ): array {
        $modelInstance = $modelClass::find($id);
        $tags = $modelInstance->tags ?? [];

        $tagStats = [];
        foreach ($tags as $tag) {
            $tagStats[$tag->id] = [
                'time' => $duration,
                'interval_amount' => $updateType === 'start' ? 1 : 0,
            ];
        }

        return [
            'intervalable_id' => $id,
            'duration' => $duration,
            'intervalable_type' => $modelClass,
            'type' => $type,
            'user_id' => $request->attributes->get('user_id'),
            'update_type' => $updateType,
            'tag_stats' => $tagStats,
        ];
    }

    private function isSupportedType(string $type): bool
    {
        return in_array($type, ['tasks', 'habits']);
    }

    private function objectExists(string $type, int $id): bool
    {
        $tableName = TypeResolver::getTableName($type);

        return \DB::table($tableName)->where('id', $id)->exists();
    }

    private function isIntervalCompletedByDuration(TimeInterval $timeInterval): bool
    {
        return now()->greaterThanOrEqualTo($timeInterval->finish_time);
    }
}
