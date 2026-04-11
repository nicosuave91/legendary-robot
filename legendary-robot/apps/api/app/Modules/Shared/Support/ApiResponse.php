<?php

declare(strict_types=1);

namespace App\Modules\Shared\Support;

use Illuminate\Http\JsonResponse;

final class ApiResponse
{
    /**
     * @param array<string, mixed> $data
     */
    public static function success(array $data, string $correlationId, int $status = 200): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'meta' => [
                'apiVersion' => 'v1',
                'correlationId' => $correlationId,
            ],
        ], $status);
    }
}
