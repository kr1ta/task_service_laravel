<?php

namespace App\Http\Controllers;

use App\Events\IntervalStarted;
use App\Events\IntervalStopped;
use App\Models\TimeInterval;
use App\Services\TypeResolver;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TimeIntervalController extends Controller
{
    public function start(Request $request, $type, $id)
    {
        try {
            // Валидация входных данных
            $validatedData = $request->validate([
                'duration' => 'required|integer',
            ]);

            \Log::info("request: {$request}");

            // Проверка поддерживаемого типа и существования объекта
            if (!$this->isSupportedType($type)) {
                return $this->handleErrorResponse('unsupported_type', 'Unsupported type or object does not exist.', 400);
            }

            if (!$this->objectExists($type, $id)) {
                return $this->handleErrorResponse('not_found', 'Object does not exist.', 404);
            }

            $modelClass = TypeResolver::getModelClass($type);

            // Проверка активного интервала
            $timeInterval = TimeInterval::where('intervalable_id', $id)
                ->where('intervalable_type', $modelClass)
                ->latest('start_time')
                ->first();

            if ($timeInterval && !$this->isIntervalCompletedByDuration($timeInterval)) {
                return $this->handleErrorResponse('conflict', 'Interval already started! Try to stop it!', 409);
            }

            // Создание нового интервала
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

            // Получение тегов из связанного объекта
            $modelInstance = $modelClass::find($id);
            $tags = $modelInstance->tags ?? []; // Предполагается, что tags - это коллекция

            // Формирование tag_stats
            $tagStats = [];
            foreach ($tags as $tag) {
                $tagStats[$tag->id] = [
                    'time' => $duration, // Время для start всегда равно duration
                    'interval_amount' => 1, // Увеличиваем количество интервалов на 1
                ];
            }

            // Формирование сообщения для события
            $message = [
                'intervalable_id' => $id,
                'duration' => $duration,
                'intervalable_type' => $modelClass,
                'type' => $type,
                'user_id' => $request->attributes->get('user_id'),
                'unspent_time' => 0,
                'update_type' => 'start',
                'tag_stats' => $tagStats, // Добавляем tag_stats
            ];

            \Log::info(json_encode($message, JSON_PRETTY_PRINT));

            // Отправка события
            event(new IntervalStarted($message));

            return response()->json([
                'data' => $timeInterval,
                'errors' => [],
            ], 201);
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

    public function stop(Request $request, $type, $id)
    {
        try {
            // Проверка поддерживаемого типа и существования объекта
            if (!$this->isSupportedType($type)) {
                return $this->handleErrorResponse('unsupported_type', 'Unsupported type or object does not exist.', 400);
            }

            if (!$this->objectExists($type, $id)) {
                return $this->handleErrorResponse('not_found', 'Object does not exist.', 404);
            }

            $modelClass = TypeResolver::getModelClass($type);

            // Получение активного интервала
            $timeInterval = TimeInterval::where('intervalable_id', $id)
                ->where('intervalable_type', $modelClass)
                ->latest('start_time')
                ->first();

            if (!$timeInterval || $this->isIntervalCompletedByDuration($timeInterval)) {
                return $this->handleErrorResponse('not_found', 'No active time interval found for the given object.', 404);
            }

            // Вычисление затраченного времени и остатка
            $spentTime = now()->diffInSeconds($timeInterval->start_time);
            $unspentTime = $timeInterval->duration - $spentTime;

            // Обновление интервала
            $timeInterval->update([
                'finish_time' => now(),
                'duration' => $spentTime,
            ]);

            // Получение тегов из связанного объекта
            $modelInstance = $modelClass::find($id);
            $tags = $modelInstance->tags ?? []; // Предполагается, что tags - это коллекция

            // Определение раннего завершения
            $expectedFinishTime = $this->getExpectedFinishTime($timeInterval);

            // Формирование tag_stats
            $tagStats = [];
            foreach ($tags as $tag) {
                $tagStats[$tag->id] = [
                    'time' => $spentTime, // Время для stop равно затраченному времени
                    'interval_amount' => 0, // Количество интервалов не увеличивается при stop
                ];
            }

            // Формирование сообщения для события
            $message = [
                'intervalable_id' => $id,
                'duration' => $spentTime,
                'intervalable_type' => $modelClass,
                'type' => $type,
                'user_id' => $request->attributes->get('user_id'),
                'unspent_time' => $unspentTime,
                'update_type' => 'stop',
                'tag_stats' => $tagStats,
                'early_completed' => true, // флаг раннего завершения
            ];

            \Log::info(json_encode($message, JSON_PRETTY_PRINT));

            // Отправка события
            event(new IntervalStopped($message));

            return response()->json([
                'data' => $timeInterval->fresh(),
                'errors' => [],
            ], 200);
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

    private function handleErrorResponse($code, $message, $statusCode)
    {
        return response()->json([
            'data' => null,
            'errors' => [
                [
                    'code' => $code,
                    'message' => $message,
                ],
            ],
        ], $statusCode);
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

    private function getExpectedFinishTime(TimeInterval $timeInterval): Carbon
    {
        return Carbon::parse($timeInterval->start_time)->addSeconds($timeInterval->duration);
    }
}