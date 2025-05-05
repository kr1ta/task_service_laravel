<?php

use App\Models\Task;
use Illuminate\Support\Facades\Http;

test('task creation returns 201 status', function () {
    // Мокируем HTTP-запрос к сервису авторизации
    Http::fake([
        config('services.validate_token.url') => Http::response([
            'valid' => true,
            'user_id' => 1,
        ], 200),
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer valid-token',
    ])->postJson('/api/tasks', [
        'title' => 'Test Task',
        'description' => 'This is a test task',
        'status' => 'In Progress',
    ]);

    $response->assertStatus(201);
});

test('task creation returns correct json structure', function () {
    // Мокируем HTTP-запрос к сервису авторизации
    Http::fake([
        config('services.validate_token.url') => Http::response([
            'valid' => true,
            'user_id' => 1,
        ], 200),
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer valid-token',
    ])->postJson('/api/tasks', [
        'title' => 'Test Task',
        'description' => 'This is a test task',
        'status' => 'In Progress',
    ]);
    $response->assertJsonStructure([
        'data' => [
            'id',
            'user_id',
            'title',
            'description',
            'status',
            'created_at',
            'updated_at',
        ],
    ]);
});

test('task creation saves data in database', function () {
    // Мокируем HTTP-запрос к сервису авторизации
    Http::fake([
        config('services.validate_token.url') => Http::response([
            'valid' => true,
            'user_id' => 1,
        ], 200),
    ]);

    $this->withHeaders([
        'Authorization' => 'Bearer valid-token',
    ])->postJson('/api/tasks', [
        'title' => 'Test Task',
        'description' => 'This is a test task',
        'status' => 'In Progress',
    ]);

    $this->assertDatabaseHas('tasks', [
        'user_id' => 1,
        'title' => 'Test Task',
        'description' => 'This is a test task',
        'status' => 'In Progress',
    ]);
});

test('user tasks can be listed', function () {
    // Мокируем HTTP-запрос к сервису авторизации
    Http::fake([
        config('services.validate_token.url') => Http::response([
            'valid' => true,
            'user_id' => 1,
        ], 200),
    ]);

    // Создаем несколько задач для пользователя с user_id = 1
    Task::factory()->count(3)->create(['user_id' => 1]);

    // Выполняем GET-запрос к эндпоинту списка задач
    $response = $this->withHeaders([
        'Authorization' => 'Bearer valid-token',
    ])->getJson('/api/tasks');

    // Проверяем, что ответ имеет статус 200 (OK)
    $response->assertStatus(200);

    // Проверяем, что в ответе содержится 3 задачи
    $response->assertJsonCount(3, 'data');
});

test('task can be retrieved by id', function () {
    // Мокируем HTTP-запрос к сервису авторизации
    Http::fake([
        config('services.validate_token.url') => Http::response([
            'valid' => true,
            'user_id' => 1,
        ], 200),
    ]);

    // Создаем задачу для пользователя с user_id = 1
    $task = Task::factory()->create(['user_id' => 1]);

    // Выполняем GET-запрос к эндпоинту получения задачи по ID
    $response = $this->withHeaders([
        'Authorization' => 'Bearer valid-token',
    ])->getJson("/api/tasks/{$task->id}");

    // Проверяем, что ответ имеет статус 200 (OK)
    $response->assertStatus(200);

    // Проверяем, что в ответе содержится созданная задача
    $response->assertJson([
        'data' => [
            'id' => $task->id,
            'user_id' => 1,
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status,
            'created_at' => $task->created_at->toISOString(),
            'updated_at' => $task->updated_at->toISOString(),
        ],
    ]);
});

test('task not found returns 404 and correct response', function () {
    // Мокируем HTTP-запрос к сервису авторизации
    Http::fake([
        config('services.validate_token.url') => Http::response([
            'valid' => true,
            'user_id' => 1,
        ], 200),
    ]);

    // Выполняем GET-запрос к несуществующей задаче
    $response = $this->withHeaders([
        'Authorization' => 'Bearer valid-token',
    ])->getJson('/api/tasks/999');

    // Проверяем, что ответ имеет статус 404 (Not Found)
    $response->assertStatus(404);

    // Проверяем, что в ответе содержится сообщение об ошибке
    $response->assertExactJson([
        'data' => null,
        'errors' => [
            [
                'code' => 'not_found',
                'message' => 'Task not found',
            ],
        ],
    ]);
});
