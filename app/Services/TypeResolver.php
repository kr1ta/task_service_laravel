<?php

namespace App\Services;

use InvalidArgumentException;

class TypeResolver
{
    public static function getModelClass(string $type): string
    {
        return match ($type) {
            'task' => \App\Models\Task::class,
            'habit' => \App\Models\Habit::class,
            default => throw new InvalidArgumentException("Unsupported type: {$type}"),
        };
    }

    public static function allTypes(): array
    {
        return [
            'task' => \App\Models\Task::class,
            'habit' => \App\Models\Habit::class,
        ];
    }

    /**
     * Возвращает имя таблицы на основе типа.
     */
    public static function getTableName(string $type): string
    {
        return match ($type) {
            'task' => 'tasks',
            'habit' => 'habits',
            default => throw new InvalidArgumentException("Unsupported type: {$type}"),
        };
    }
}
