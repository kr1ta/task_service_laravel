<?php

use App\Http\Controllers\HabitController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TimeIntervalController;
use Illuminate\Support\Facades\Route;

Route::middleware(['validate.token'])->group(function () {
    // создание
    Route::post('/tasks', [TaskController::class, 'create']);
    Route::post('/habits', [HabitController::class, 'create']);
    Route::post('/tags', [TagController::class, 'create']);

    // получение по айди
    Route::get('/tasks/{id}', [TaskController::class, 'show']);
    Route::get('/habits/{id}', [HabitController::class, 'show']);
    Route::get('/tags/{id}', [TagController::class, 'show']);

    // Удаление
    Route::delete('/tasks/{id}', [TaskController::class, 'delete']);
    Route::delete('/habits/{id}', [HabitController::class, 'delete']);
    Route::delete('/tags/{id}', [TagController::class, 'delete']);

    // Обновление
    Route::patch('/tasks/{id}', [TaskController::class, 'update']);
    Route::patch('/habits/{id}', [HabitController::class, 'update']);
    Route::patch('/tags/{id}', [TagController::class, 'update']);

    // Запуск таймера
    Route::post('/{type}/{task_id}/start-interval', [TimeIntervalController::class, 'start']);
    Route::post('/{type}/{task_id}/stop-interval', [TimeIntervalController::class, 'stop']);

    // Просмотр задач, привычек и тегов пользователя
    Route::get('/tasks', [TaskController::class, 'index']); // Список задач
    Route::get('/habits', [HabitController::class, 'index']); // Список привычек
    Route::get('/tags', [TagController::class, 'index']); // Список тегов
    Route::get('/tags/{id}', [TagController::class, 'list']); // Список всех задач и привычек, связанных с тегом

    // Работа с тегами
    Route::post('/{type}/{id}/attach-tag/{tag_id}', [TagController::class, 'attach']); // добавить тег к задаче/привычке
    Route::get('/{type}/{id}/tags', [TagController::class, 'get_tag']); // узнать тег задачи/привычки
    Route::delete('/{type}/{id}/detach-tag/{tag_id}', [TagController::class, 'detach']); // убрать тег у задачи/привычки
});
