<?php

namespace App\Http\Middleware;

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
            return $this->errorResponse('data_missing', 'Token not provided', 401);
        }

        $url = Config::get('services.validate_token.url');

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$token,
                'Content-Type' => 'application/json',
            ])->get($url);

            // Проверка статуса ответа и валидности токена
            if ($response->status() !== 200 || ! $response->json('valid')) {
                return $this->errorResponse('invalid_token', 'Invalid or expired token', 403);
            }

            $userId = $response->json('user_id');

            if (! $userId) {
                return $this->errorResponse('not_found', 'Failed to retrieve user ID', 500);
            }

            // Добавление user_id в запрос для дальнейшего использования
            $request->attributes->add(['user_id' => $userId]);

            return $next($request);

        } catch (\Exception $e) {
            return $this->errorResponse('validation_error', 'Token validation error: '.$e->getMessage(), 500);
        }
    }

    private function errorResponse(string $code, string $message, int $statusCode)
    {
        return response()->json([
            'data' => null,
            'errors' => [
                [
                    'code' => $code,
                    'message' => $message,
                ],
            ],
        ], $statusCode);
    }
}
