<?php

declare(strict_types=1);

namespace App\Modules\WorkflowBuilder\Services;

use App\Modules\Applications\Models\Application;
use App\Modules\Clients\Models\Client;
use App\Modules\WorkflowBuilder\Models\WorkflowRun;

final class WorkflowRuntimeContextResolver
{
    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function resolveForRun(WorkflowRun $run, array $context): array
    {
        if ((string) $run->subject_type === 'client') {
            return $context + $this->clientContext($this->clientForRun($run));
        }

        if ((string) $run->subject_type === 'application') {
            $application = $this->applicationForRun($run);
            $client = Client::query()
                ->withoutGlobalScopes()
                ->where('tenant_id', (string) $run->tenant_id)
                ->where('id', (string) $application->client_id)
                ->firstOrFail();

            return $context + [
                'applicationId' => (string) $application->id,
                'applicationNumber' => (string) $application->application_number,
                'applicationStatus' => (string) $application->status,
                'productType' => (string) $application->product_type,
            ] + $this->clientContext($client);
        }

        return $context;
    }

    private function clientForRun(WorkflowRun $run): Client
    {
        return Client::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', (string) $run->tenant_id)
            ->where('id', (string) $run->subject_id)
            ->firstOrFail();
    }

    private function applicationForRun(WorkflowRun $run): Application
    {
        return Application::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', (string) $run->tenant_id)
            ->where('id', (string) $run->subject_id)
            ->firstOrFail();
    }

    /**
     * @return array<string, mixed>
     */
    private function clientContext(Client $client): array
    {
        return [
            'clientId' => (string) $client->id,
            'clientDisplayName' => (string) $client->display_name,
            'clientEmail' => $client->primary_email,
            'clientPhone' => $client->primary_phone,
        ];
    }
}
