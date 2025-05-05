<?php

namespace App\Services;

use InvalidArgumentException;

class TypeResolver
{
    public static function getModelClass(string $type): string
    {
        return match ($type) {
            'tasks' => \App\Models\Task::class,
            'habits' => \App\Models\Habit::class,
            default => throw new InvalidArgumentException("Unsupported type: {$type}"),
        };
    }

    public static function allTypes(): array
    {
        return [
            'tasks' => \App\Models\Task::class,
            'habits' => \App\Models\Habit::class,
        ];
    }

    /**
     * Возвращает имя таблицы на основе типа.
     */
    public static function getTableName(string $type): string
    {
        return match ($type) {
            'tasks' => 'tasks',
            'habits' => 'habits',
            default => throw new InvalidArgumentException("Unsupported type: {$type}"),
        };
    }
}
