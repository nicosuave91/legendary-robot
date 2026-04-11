<?php

declare(strict_types=1);

namespace App\Modules\Communications\Services;

use App\Modules\Clients\Models\Client;
use App\Modules\Clients\Services\ClientVisibilityService;
use App\Modules\IdentityAccess\Models\User;

final class ClientCommunicationAccessService
{
    public function __construct(
        private readonly ClientVisibilityService $clientVisibilityService,
    ) {
    }

    public function canRead(User $actor, Client $client): bool
    {
        return $actor->hasPermission('clients.communications.read')
            && (string) $actor->tenant_id === (string) $client->tenant_id
            && $this->clientVisibilityService->canView($actor, $client);
    }

    public function canSendSms(User $actor, Client $client): bool
    {
        return $this->canRead($actor, $client) && $actor->hasPermission('clients.communications.sms.send');
    }

    public function canSendEmail(User $actor, Client $client): bool
    {
        return $this->canRead($actor, $client) && $actor->hasPermission('clients.communications.email.send');
    }

    public function canInitiateCall(User $actor, Client $client): bool
    {
        return $this->canRead($actor, $client) && $actor->hasPermission('clients.communications.call.create');
    }
}
