<?php

use App\Models\Habit;
use App\Models\Tag;
use App\Models\Task;
use Illuminate\Support\Facades\Http;

test('tag creation returns 200 status', function () {
    // Мокируем HTTP-запрос к сервису авторизации
    Http::fake([
        config('services.validate_token.url') => Http::response([
            'data' => [
                'valid' => true,
                'user_id' => 1,
            ],
        ], 200),
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer valid-token',
    ])->postJson('/api/tags', [
        'name' => 'Workout',
    ]);

    // Проверяем, что ответ имеет статус 200
    $response->assertStatus(200);
});

test('tag creation returns correct json structure', function () {
    // Мокируем HTTP-запрос к сервису авторизации
    Http::fake([
        config('services.validate_token.url') => Http::response([
            'data' => [
                'valid' => true,
                'user_id' => 1,
            ],
        ], 200),
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer valid-token',
    ])->postJson('/api/tags', [
        'name' => 'Workout',
    ]);

    // Проверяем структуру JSON-ответа
    $response->assertJsonStructure([
        'data' => [
            'id',
            'user_id',
            'name',
        ],
    ]);
});

test('tag creation saves data in database', function () {
    // Мокируем HTTP-запрос к сервису авторизации
    Http::fake([
        config('services.validate_token.url') => Http::response([
            'data' => [
                'valid' => true,
                'user_id' => 1,
            ],
        ], 200),
    ]);
    $this->withHeaders([
        'Authorization' => 'Bearer valid-token',
    ])->postJson('/api/tags', [
        'name' => 'Workout',
    ]);

    // Проверяем, что тег сохранен в базе данных
    $this->assertDatabaseHas('tags', [
        'user_id' => 1,
        'name' => 'Workout',
    ]);
});

test('tags can be listed', function () {
    // Мокируем HTTP-запрос к сервису авторизации
    Http::fake([
        config('services.validate_token.url') => Http::response([
            'data' => [
                'valid' => true,
                'user_id' => 1,
            ],
        ], 200),
    ]);
    // Создаем несколько тегов для пользователя с user_id = 1
    Tag::factory()->count(3)->create(['user_id' => 1]);

    // Выполняем GET-запрос к эндпоинту списка тегов
    $response = $this->withHeaders([
        'Authorization' => 'Bearer valid-token',
    ])->getJson('/api/tags');

    // Проверяем, что ответ имеет статус 200 (OK)
    $response->assertStatus(200);

    // Проверяем, что в ответе содержится 3 тега
    $response->assertJsonCount(3, 'data');
});

test('tag not found returns 404 and correct response', function () {
    // Мокируем HTTP-запрос к сервису авторизации
    Http::fake([
        config('services.validate_token.url') => Http::response([
            'data' => [
                'valid' => true,
                'user_id' => 1,
            ],
        ], 200),
    ]);

    // Выполняем GET-запрос к несуществующему тегу
    $response = $this->withHeaders([
        'Authorization' => 'Bearer valid-token',
    ])->getJson('/api/tags/999');

    // Проверяем, что ответ имеет статус 404 (Not Found)
    $response->assertStatus(404);

    // Проверяем, что в ответе содержится сообщение об ошибке
    $response->assertExactJson([
        'data' => null,
        'errors' => [
            [
                'code' => 'not_found',
                'message' => 'Tag not found',
            ],
        ],
    ]);
});

test('tag can be attached to a task', function () {
    // Мокируем HTTP-запрос к сервису авторизации
    Http::fake([
        config('services.validate_token.url') => Http::response([
            'data' => [
                'valid' => true,
                'user_id' => 1,
            ],
        ], 200),
    ]);

    // Создаем задачу и тег
    $task = Task::factory()->create(['user_id' => 1]);
    $tag = Tag::factory()->create(['user_id' => 1]);

    // Выполняем POST-запрос для прикрепления тега к задаче
    $response = $this->withHeaders([
        'Authorization' => 'Bearer valid-token',
    ])->postJson("/api/tasks/{$task->id}/attach-tag/{$tag->id}");

    // Проверяем, что ответ имеет статус 200 (OK)
    $response->assertStatus(200);

    // Проверяем, что тег прикреплен к задаче
    $this->assertTrue($task->tags()->where('tags.id', $tag->id)->exists());
});

test('attach fails if tag or task does not exist', function () {
    // Мокируем HTTP-запрос к сервису авторизации
    Http::fake([
        config('services.validate_token.url') => Http::response([
            'data' => [
                'valid' => true,
                'user_id' => 1,
            ],
        ], 200),
    ]);

    // Выполняем POST-запрос для несуществующей задачи или тега
    $response = $this->withHeaders([
        'Authorization' => 'Bearer valid-token',
    ])->postJson('/api/tasks/999/tags/999/attach-tag');

    // Проверяем, что ответ имеет статус 404 (Not Found)
    $response->assertStatus(404);
});

test('tag can be detached from a task', function () {
    // Мокируем HTTP-запрос к сервису авторизации
    Http::fake([
        config('services.validate_token.url') => Http::response([
            'data' => [
                'valid' => true,
                'user_id' => 1,
            ],
        ], 200),
    ]);

    // Создаем задачу и тег
    $task = Task::factory()->create(['user_id' => 1]);
    $tag = Tag::factory()->create(['user_id' => 1]);

    // Прикрепляем тег к задаче
    $task->tags()->attach($tag->id);

    // Выполняем POST-запрос для открепления тега от задачи
    $response = $this->withHeaders([
        'Authorization' => 'Bearer valid-token',
    ])->deleteJson("/api/tasks/{$task->id}/detach-tag/{$tag->id}");

    // Проверяем, что ответ имеет статус 200 (OK)
    $response->assertStatus(200);

    // Проверяем, что тег откреплен от задачи
    $this->assertFalse($task->tags()->where('tags.id', $tag->id)->exists());
});

test('tags list returns all models', function () {
    // Мокируем HTTP-запрос к сервису авторизации
    Http::fake([
        config('services.validate_token.url') => Http::response([
            'data' => [
                'valid' => true,
                'user_id' => 1,
            ],
        ], 200),
    ]);

    // Создаем тег
    $tag = Tag::factory()->create(['user_id' => 1]);

    // Создаем задачу и привычку, связанные с этим тегом
    $task = Task::factory()->create(['user_id' => 1]);
    $habit = Habit::factory()->create(['user_id' => 1]);

    $task->tags()->attach($tag->id);
    $habit->tags()->attach($tag->id);

    // Выполняем GET-запрос к эндпоинту списка моделей для тега
    $response = $this->withHeaders([
        'Authorization' => 'Bearer valid-token',
    ])->getJson("/api/tags/{$tag->id}");

    // Проверяем, что ответ имеет статус 200 (OK)
    $response->assertStatus(200);

    // Проверяем, что в ответе содержатся задачи и привычки
    $response->assertJson([
        'data' => [
            'tasks' => [
                [
                    'id' => $task->id,
                    'user_id' => 1,
                    'title' => $task->title,
                ],
            ],
            'habits' => [
                [
                    'id' => $habit->id,
                    'user_id' => 1,
                    'title' => $habit->title,
                ],
            ],
        ],
    ]);
});

test('tags can be retrieved for a task', function () {
    // Мокируем HTTP-запрос к сервису авторизации
    Http::fake([
        config('services.validate_token.url') => Http::response([
            'data' => [
                'valid' => true,
                'user_id' => 1,
            ],
        ], 200),
    ]);

    // Создаем задачу и тег
    $task = Task::factory()->create(['user_id' => 1]);
    $tag = Tag::factory()->create(['user_id' => 1]);

    // Прикрепляем тег к задаче
    $task->tags()->attach($tag->id);

    // Выполняем GET-запрос для получения тегов задачи
    $response = $this->withHeaders([
        'Authorization' => 'Bearer valid-token',
    ])->getJson("/api/tasks/{$task->id}/tags");

    // Проверяем, что ответ имеет статус 200 (OK)
    $response->assertStatus(200);

    // Проверяем, что в ответе содержится созданный тег
    $response->assertJson([
        'data' => [
            [
                'id' => $tag->id,
                'user_id' => $tag->user_id,
                'name' => $tag->name,
            ],
        ],
        'errors' => [],
    ]);
});

test('tags can be retrieved for a habit', function () {
    // Мокируем HTTP-запрос к сервису авторизации
    Http::fake([
        config('services.validate_token.url') => Http::response([
            'data' => [
                'valid' => true,
                'user_id' => 1,
            ],
        ], 200),
    ]);

    // Создаем привычку и тег
    $habit = Habit::factory()->create(['user_id' => 1]);
    $tag = Tag::factory()->create(['user_id' => 1]);

    // Прикрепляем тег к привычке
    $habit->tags()->attach($tag->id);

    // Выполняем GET-запрос для получения тегов привычки
    $response = $this->withHeaders([
        'Authorization' => 'Bearer valid-token',
    ])->getJson("/api/habits/{$habit->id}/tags");

    // Проверяем, что ответ имеет статус 200 (OK)
    $response->assertStatus(200);

    // Проверяем, что в ответе содержится созданный тег
    $response->assertJson([
        'data' => [
            [
                'id' => $tag->id,
                'user_id' => $tag->user_id,
                'name' => $tag->name,
            ],
        ],
        'errors' => [],
    ]);
});

test('get tags fails if model does not exist', function () {
    // Мокируем HTTP-запрос к сервису авторизации
    Http::fake([
        config('services.validate_token.url') => Http::response([
            'data' => [
                'valid' => true,
                'user_id' => 1,
            ],
        ], 200),
    ]);

    // Выполняем GET-запрос для несуществующей задачи
    $response = $this->withHeaders([
        'Authorization' => 'Bearer valid-token',
    ])->getJson('/api/tasks/999/tags');

    $response->assertStatus(404);
});

test('get tags fails if type is invalid', function () {
    // Мокируем HTTP-запрос к сервису авторизации
    Http::fake([
        config('services.validate_token.url') => Http::response([
            'data' => [
                'valid' => true,
                'user_id' => 1,
            ],
        ], 200),
    ]);

    // Выполняем GET-запрос для недопустимого типа
    $response = $this->withHeaders([
        'Authorization' => 'Bearer valid-token',
    ])->getJson('/api/invalid-type/1/tags');

    // Проверяем, что ответ имеет статус 500 (server_error)
    $response->assertStatus(500);

    // Проверяем, что в ответе содержится сообщение об ошибке
    $response->assertJson([
        'data' => null,
        'errors' => [
            [
                'code' => 'server_error',
                'message' => 'Unsupported type: invalid-type',
            ],
        ],
    ]);
});
