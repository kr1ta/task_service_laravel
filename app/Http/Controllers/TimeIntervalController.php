<?php

namespace App\Http\Controllers;

use App\Events\IntervalStarted;
use App\Events\IntervalStopped;
use App\Models\TimeInterval;
use App\Services\TypeResolver;
use Illuminate\Http\Request;

class TimeIntervalController extends Controller
{
    public function start(Request $request, $type, $id)
    {
        try {
            $validatedData = $this->validateStartRequest($request);

            if (! $this->isSupportedType($type) || ! $this->objectExists($type, $id)) {
                return $this->handleErrorResponse('not_found', 'Unsupported type or object does not exist.', 400);
            }

            $modelClass = TypeResolver::getModelClass($type);
            $activeInterval = $this->getLatestInterval($modelClass, $id);

            if ($activeInterval && ! $this->isIntervalCompletedByDuration($activeInterval)) {
                return $this->handleErrorResponse('conflict', 'Interval already started! Try to stop it!', 409);
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

            return $this->successResponse($timeInterval, 201);
        } catch (\Exception $e) {
            return $this->handleServerError($e);
        }
    }

    public function stop(Request $request, $type, $id)
    {
        try {
            if (! $this->isSupportedType($type) || ! $this->objectExists($type, $id)) {
                return $this->handleErrorResponse('not_found', 'Unsupported type or object does not exist.', 400);
            }

            $modelClass = TypeResolver::getModelClass($type);
            $activeInterval = $this->getLatestInterval($modelClass, $id);

            if (! $activeInterval || $this->isIntervalCompletedByDuration($activeInterval)) {
                return $this->handleErrorResponse('not_found', 'No active time interval found for the given object.', 404);
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

            return $this->successResponse($activeInterval->fresh(), 200);
        } catch (\Exception $e) {
            return $this->errorResponse('server_error', $e->getMessage(), $e->getCode());
        }
    }

    private function validateStartRequest(Request $request)
    {
        return $request->validate([
            'duration' => 'required|integer',
        ]);
    }

    private function getLatestInterval($modelClass, $id)
    {
        return TimeInterval::where('intervalable_id', $id)
            ->where('intervalable_type', $modelClass)
            ->latest('start_time')
            ->first();
    }

    private function buildMessageForEvent($type, $id, $duration, $modelClass, Request $request, $updateType)
    {
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

    private function isSupportedType($type): bool
    {
        return in_array($type, ['task', 'habit']);
    }

    private function objectExists($type, $id): bool
    {
        $tableName = TypeResolver::getTableName($type);

        return \DB::table($tableName)->where('id', $id)->exists();
    }

    private function isIntervalCompletedByDuration(TimeInterval $timeInterval): bool
    {
        return now()->greaterThanOrEqualTo($timeInterval->finish_time);
    }
}
