<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use App\Models\Task;
use App\Models\TimeInterval;
use Illuminate\Support\Facades\Queue;

test('time interval can be started successfully', function () {
    // Мокируем HTTP-запрос к сервису авторизации
    Http::fake([
        config('services.validate_token.url') => Http::response([
            'valid' => true,
            'user_id' => 1,
        ], 200),
    ]);

    // Мокируем очередь для Kafka
    Queue::fake();

    // Создаем задачу
    $task = Task::factory()->create(['user_id' => 1]);

    // Выполняем POST-запрос для запуска интервала
    $response = $this->withHeaders([
        'Authorization' => 'Bearer valid-token',
    ])->postJson("/api/task/{$task->id}/start", [
        'duration' => 3600, // 1 час
    ]);

    // Проверяем, что ответ имеет статус 201 (Created)
    $response->assertStatus(201);

    // Проверяем структуру JSON-ответа
    $response->assertJsonStructure([
        'message',
        'time_interval' => [
            'id',
            'intervalable_id',
            'intervalable_type',
            'duration',
            'start_time',
            'finish_time',
        ],
    ]);

    // Проверяем, что интервал сохранен в базе данных
    $this->assertDatabaseHas('time_intervals', [
        'intervalable_id' => $task->id,
        'intervalable_type' => Task::class,
        'duration' => 3600,
    ]);
});

test('time interval can be stopped successfully', function () {
    // Мокируем HTTP-запрос к сервису авторизации
    Http::fake([
        config('services.validate_token.url') => Http::response([
            'valid' => true,
            'user_id' => 1,
        ], 200),
    ]);

    // Мокируем очередь для Kafka
    Queue::fake();


    // Создаем задачу и активный интервал
    $task = Task::factory()->create(['user_id' => 1]);
    $timeInterval = TimeInterval::factory()->create([
        'intervalable_id' => $task->id,
        'intervalable_type' => Task::class,
        'duration' => 3600, // 1 час
        'start_time' => now()->subMinutes(30), // Начало 30 минут назад
        'finish_time' => now()->addMinutes(30), // Ожидаемое завершение через 30 минут
    ]);

    // Выполняем POST-запрос для остановки интервала
    $response = $this->withHeaders([
        'Authorization' => 'Bearer valid-token',
    ])->postJson("/api/task/{$task->id}/stop");

    // Проверяем, что ответ имеет статус 200 (OK)
    $response->assertStatus(200);

    // Проверяем структуру JSON-ответа
    $response->assertJsonStructure([
        'message',
        'time_interval' => [
            'id',
            'intervalable_id',
            'intervalable_type',
            'duration',
            'start_time',
            'finish_time',
        ],
    ]);

    // Проверяем, что интервал обновлен в базе данных
    $this->assertDatabaseHas('time_intervals', [
        'id' => $timeInterval->id,
        'duration' => 1800, // 30 минут
        'finish_time' => now()->toDateTimeString(),
    ]);
});