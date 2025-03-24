<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class ValidateTokenMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'message' => 'Токен не предоставлен',
            ], 401);
        }

        $url = Config::get('services.validate_token.url');

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])->get($url);

            if ($response->status() !== 200 || !$response->json('valid')) {
                return response()->json($response->json(), 403);
            }

            $userId = $response->json('user_id');

            if (!$userId) {
                return response()->json([
                    'message' => 'Не удалось получить ID пользователя',
                ], 500);
            }

            // Добавление user_id в запрос для дальнейшего использования
            $request->attributes->add(['user_id' => $userId]);

            return $next($request);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ошибка при проверке токена',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}