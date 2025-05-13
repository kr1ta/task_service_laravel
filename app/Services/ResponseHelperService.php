<?php

namespace App\Services;

class ResponseHelperService
{
    public static function success($data = null)
    {
        return response()->json([
            'data' => $data,
            'errors' => [],
        ]);
    }

    public static function error(array $errors, int $httpCode = 400)
    {
        return response()->json([
            'data' => null,
            'errors' => $errors,
        ], $httpCode);
    }
}
