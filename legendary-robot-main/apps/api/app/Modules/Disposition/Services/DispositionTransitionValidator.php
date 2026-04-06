<?php

declare(strict_types=1);

namespace App\Modules\Disposition\Services;

use App\Modules\Applications\Models\Application;
use App\Modules\Clients\Models\Client;
use App\Modules\IdentityAccess\Models\User;

final class DispositionTransitionValidator
{
    public function __construct(
        private readonly DispositionProjectionService $projectionService,
    ) {
    }

    public function validate(User $actor, Client $client, string $targetCode): array
    {
        $current = $this->projectionService->currentForClient($client);
        $availableTransitions = $this->projectionService->availableTransitionsForClient($client);
        $allowedCodes = collect($availableTransitions)->pluck('code')->all();
        $warnings = [];
        $blockingIssues = [];

        if (!in_array($targetCode, $allowedCodes, true)) {
            $blockingIssues[] = [
                'code' => 'invalid_disposition_transition',
                'message' => 'The requested lifecycle move is not allowed from the current disposition.',
                'severity' => 'blocking',
            ];
        }

        if ($targetCode === 'qualified' && empty($client->primary_email) && empty($client->primary_phone)) {
            $blockingIssues[] = [
                'code' => 'contact_method_required',
                'message' => 'A primary email or phone number is required before moving this client to Qualified.',
                'severity' => 'blocking',
            ];
        }

        if ($targetCode === 'qualified' && empty($client->owner_user_id)) {
            $warnings[] = [
                'code' => 'owner_missing',
                'message' => 'This client has no assigned owner. Proceeding remains auditable but should be reviewed.',
                'severity' => 'warning',
            ];
        }

        if ($targetCode === 'applied' && !Application::query()->where('tenant_id', $client->tenant_id)->where('client_id', $client->id)->exists()) {
            $blockingIssues[] = [
                'code' => 'application_required',
                'message' => 'At least one application must exist before moving this client to Applied.',
                'severity' => 'blocking',
            ];
        }

        if ($targetCode === 'active' && !Application::query()->where('tenant_id', $client->tenant_id)->where('client_id', $client->id)->where('status', 'approved')->exists()) {
            $blockingIssues[] = [
                'code' => 'approved_application_required',
                'message' => 'An approved application is required before moving this client to Active.',
                'severity' => 'blocking',
            ];
        }

        return [
            'currentDisposition' => $current,
            'availableTransitions' => $availableTransitions,
            'warnings' => $warnings,
            'blockingIssues' => $blockingIssues,
        ];
    }
}
