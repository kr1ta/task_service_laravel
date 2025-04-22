<?php

use App\Http\Controllers\HabitController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TimeIntervalController;
use Illuminate\Support\Facades\Route;

Route::middleware(['validate.token'])->group(function () {
    // создание
    Route::post('/task', [TaskController::class, 'create']);
    Route::post('/habit', [HabitController::class, 'create']);
    Route::post('/tag', [TagController::class, 'create']);

    // получение по айди
    Route::get('/task/{id}', [TaskController::class, 'show']);
    Route::get('/habit/{id}', [HabitController::class, 'show']);
    Route::get('/tag/{id}', [TagController::class, 'show']);

    Route::delete('/task/{id}', [TaskController::class, 'delete']);
    Route::delete('/habit/{id}', [HabitController::class, 'delete']);
    Route::delete('/tag/{id}', [TagController::class, 'delete']);

    // Запуск таймера
    Route::post('/{type}/{task_id}/start', [TimeIntervalController::class, 'start']);
    Route::post('/{type}/{task_id}/stop', [TimeIntervalController::class, 'stop']);

    // Просмотр задач, привычек и тегов
    Route::get('/tasks', [TaskController::class, 'index']); // Список задач
    Route::get('/habits', [HabitController::class, 'index']); // Список привычек
    Route::get('/tags', [TagController::class, 'index']); // Список тегов

    Route::get('/tag/{id}', action: [TagController::class, 'list']);

    Route::post('/{type}/{id}/tag/{tag_id}', [TagController::class, 'attach']); // добавить тег
    Route::get('/{type}/{id}/tags', [TagController::class, 'get_tag']); // узнать тег
    Route::delete('/{type}/{id}/tag/{tag_id}', [TagController::class, 'detach']); // убрать тег
});
