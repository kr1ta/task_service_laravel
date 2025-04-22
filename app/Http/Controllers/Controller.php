<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function successResponse($data, $statusCode = 200): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'errors' => [],
        ], $statusCode);
    }

    protected function errorResponse(string $code, string $message, int $statusCode): JsonResponse
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
