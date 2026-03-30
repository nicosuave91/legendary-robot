<?php

declare(strict_types=1);

namespace App\Modules\IdentityAccess\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Modules\IdentityAccess\Http\Requests\SignInRequest;
use App\Modules\IdentityAccess\Http\Resources\AuthContextResource;
use App\Modules\IdentityAccess\Services\AuthService;
use App\Modules\Shared\Support\ApiResponse;

final class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {
    }

    public function signIn(SignInRequest $request): JsonResponse
    {
        $context = $this->authService->signIn(
            email: $request->validated('email'),
            password: $request->validated('password'),
            correlationId: (string) $request->attributes->get('correlation_id', ''),
        );

        return ApiResponse::success(
            (new AuthContextResource($context))->resolve(),
            (string) $request->attributes->get('correlation_id', ''),
        );
    }

    public function signOut(Request $request): JsonResponse
    {
        $this->authService->signOut(
            correlationId: (string) $request->attributes->get('correlation_id', ''),
        );

        return response()->json(['message' => 'Signed out']);
    }

    public function me(Request $request): JsonResponse
    {
        $context = $this->authService->me(
            correlationId: (string) $request->attributes->get('correlation_id', ''),
        );

        return ApiResponse::success(
            (new AuthContextResource($context))->resolve(),
            (string) $request->attributes->get('correlation_id', ''),
        );
    }
}
