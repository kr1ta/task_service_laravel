<?php

namespace App\Http\Middleware;

use App\Services\ResponseHelperService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class ValidateTokenMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (! $token) {
            return ResponseHelperService::error([
                [
                    'code' => 'data_missing',
                    'message' => 'Token not provided',
                ],
            ], 401);
        }

        $url = Config::get('services.validate_token.url');

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$token,
                'Content-Type' => 'application/json',
            ])->get($url);

            // Проверяем, что запрос прошёл успешно
            if ($response->failed()) {
                return ResponseHelperService::error([
                    [
                        'code' => 'server_error',
                        'message' => 'Failed to validate token. Service unavailable.',
                    ],
                ], 502); // Bad Gateway
            }

            $responseData = $response->json();

            // Проверяем структуру ответа
            if (! isset($responseData['data']['valid'])) {
                return ResponseHelperService::error([
                    [
                        'code' => 'server_error',
                        'message' => 'Unexpected response format from token validation service.',
                    ],
                ], 502);
            }

            $valid = $responseData['data']['valid'];

            if (! $valid) {
                return ResponseHelperService::error([
                    [
                        'code' => 'invalid_token',
                        'message' => 'Invalid or expired token.',
                    ],
                ]);
            }

            $userId = $responseData['data']['user_id'] ?? null;

            if (! $userId) {
                return ResponseHelperService::error([
                    [
                        'code' => 'not_found',
                        'message' => 'User ID not found in token validation response.',
                    ],
                ], 500);
            }

            // Добавляем user_id в атрибуты запроса для дальнейшего использования
            $request->attributes->add(['user_id' => $userId]);

            return $next($request);

        } catch (\Exception $e) {
            return ResponseHelperService::error([
                [
                    'code' => 'server_error',
                    'message' => 'Token validation error: '.$e->getMessage(),
                ],
            ], 500);
        }
    }
}
