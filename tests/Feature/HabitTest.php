<?php

use App\Models\Habit;
use Illuminate\Support\Facades\Http; // Импортируем модель Habit

test('habit creation returns 201 status', function () {
    // Мокируем HTTP-запрос к сервису авторизации
    Http::fake([
        config('services.validate_token.url') => Http::response([
            'valid' => true,
            'user_id' => 1,
        ], 200),
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer valid-token',
    ])->postJson('/api/habit', [
        'title' => 'Morning Run',
        'description' => 'Run every morning at 7 AM',
    ]);

    $response->assertStatus(201);
});

test('habit creation returns correct json structure', function () {
    // Мокируем HTTP-запрос к сервису авторизации
    Http::fake([
        config('services.validate_token.url') => Http::response([
            'valid' => true,
            'user_id' => 1,
        ], 200),
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer valid-token',
    ])->postJson('/api/habit', [
        'title' => 'Morning Run',
        'description' => 'Run every morning at 7 AM',
    ]);

    $response->assertJsonStructure([
        'data' => [
            'id',
            'user_id',
            'title',
            'description',
            'created_at',
            'updated_at',
        ],
    ]);
});

test('habit creation saves data in database', function () {
    // Мокируем HTTP-запрос к сервису авторизации
    Http::fake([
        config('services.validate_token.url') => Http::response([
            'valid' => true,
            'user_id' => 1,
        ], 200),
    ]);

    $this->withHeaders([
        'Authorization' => 'Bearer valid-token',
    ])->postJson('/api/habit', [
        'title' => 'Morning Run',
        'description' => 'Run every morning at 7 AM',
    ]);

    $this->assertDatabaseHas('habits', [
        'user_id' => 1,
        'title' => 'Morning Run',
        'description' => 'Run every morning at 7 AM',
    ]);
});

test('habit can be retrieved by id', function () {
    // Мокируем HTTP-запрос к сервису авторизации
    Http::fake([
        config('services.validate_token.url') => Http::response([
            'valid' => true,
            'user_id' => 1,
        ], 200),
    ]);

    // Создаем привычку для пользователя с user_id = 1
    $habit = Habit::factory()->create(['user_id' => 1]);

    // Выполняем GET-запрос к эндпоинту получения привычки по ID
    $response = $this->withHeaders([
        'Authorization' => 'Bearer valid-token',
    ])->getJson("/api/habit/{$habit->id}");

    // Проверяем, что ответ имеет статус 200 (OK)
    $response->assertStatus(200);

    // Проверяем, что в ответе содержится созданная привычка
    $response->assertJson([
        'data' => [
            'id' => $habit->id,
            'user_id' => 1,
            'title' => $habit->title,
            'description' => $habit->description,
            'created_at' => $habit->created_at->toISOString(),
            'updated_at' => $habit->updated_at->toISOString(),
        ],
    ]);
});

test('habit not found returns 404', function () {
    // Мокируем HTTP-запрос к сервису авторизации
    Http::fake([
        config('services.validate_token.url') => Http::response([
            'valid' => true,
            'user_id' => 1,
        ], 200),
    ]);

    // Выполняем GET-запрос к несуществующей привычке
    $response = $this->withHeaders([
        'Authorization' => 'Bearer valid-token',
    ])->getJson('/api/habit/999');

    // Проверяем, что ответ имеет статус 404 (Not Found)
    $response->assertStatus(404);

    // Проверяем, что в ответе содержится сообщение об ошибке
    $response->assertJson([
        'message' => 'Habit not found',
    ]);
});
