<?php

declare(strict_types=1);

namespace App\Modules\IdentityAccess\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Modules\IdentityAccess\Http\Requests\CreateAccountRequest;
use App\Modules\IdentityAccess\Http\Requests\UpdateAccountRequest;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\IdentityAccess\Services\UserProvisioningService;
use App\Modules\Shared\Support\ApiResponse;

final class AccountController extends Controller
{
    public function __construct(
        private readonly UserProvisioningService $userProvisioningService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('settings.accounts.read');

        return ApiResponse::success(
            $this->userProvisioningService->listVisibleAccounts($request->user()),
            (string) $request->attributes->get('correlation_id', ''),
        );
    }

    public function store(CreateAccountRequest $request): JsonResponse
    {
        Gate::authorize('settings.accounts.create');

        $account = $this->userProvisioningService->createAccount(
            actor: $request->user(),
            payload: $request->validated(),
            correlationId: (string) $request->attributes->get('correlation_id', ''),
        );

        return ApiResponse::success(
            $account,
            (string) $request->attributes->get('correlation_id', ''),
            201,
        );
    }

    public function update(UpdateAccountRequest $request, string $userId): JsonResponse
    {
        $subject = User::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('id', $userId)
            ->firstOrFail();

        Gate::authorize('settings.accounts.update', $subject);

        return ApiResponse::success(
            $this->userProvisioningService->updateAccount(
                actor: $request->user(),
                subject: $subject,
                payload: $request->validated(),
                correlationId: (string) $request->attributes->get('correlation_id', ''),
            ),
            (string) $request->attributes->get('correlation_id', ''),
        );
    }

    public function destroy(Request $request, string $userId): JsonResponse
    {
        $subject = User::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('id', $userId)
            ->firstOrFail();

        Gate::authorize('settings.accounts.decommission', $subject);

        $this->userProvisioningService->decommissionAccount(
            actor: $request->user(),
            subject: $subject,
            correlationId: (string) $request->attributes->get('correlation_id', ''),
        );

        return response()->json(['message' => 'Account decommissioned']);
    }
}
