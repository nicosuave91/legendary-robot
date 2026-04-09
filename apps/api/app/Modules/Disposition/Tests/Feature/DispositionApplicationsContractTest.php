<?php

declare(strict_types=1);

namespace App\Modules\Disposition\Tests\Feature;

use App\Modules\Applications\Models\ApplicationStatusHistory;
use App\Modules\Disposition\Models\ClientDispositionHistory;
use Tests\Support\SeededApiTestCase;

final class DispositionApplicationsContractTest extends SeededApiTestCase
{
    public function test_disposition_transition_endpoint_executes_runtime_state_machine_logic(): void
    {
        $this->sanctumActingAs('owner-user');

        $response = $this
            ->withHeader('X-Correlation-Id', 'corr-disposition-transition-runtime')
            ->postJson('/api/v1/clients/client-horizon-medical/disposition-transitions', [
                'targetDispositionCode' => 'qualified',
                'reason' => 'Runtime state-machine verification',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.result', 'transitioned')
            ->assertJsonPath('data.currentDisposition.code', 'qualified')
            ->assertJsonPath('data.historyEntry.toDispositionCode', 'qualified');

        $history = ClientDispositionHistory::query()
            ->withoutGlobalScopes()
            ->where('client_id', 'client-horizon-medical')
            ->latest('occurred_at')
            ->first();

        self::assertNotNull($history);
        self::assertSame('qualified', $history->to_disposition_code);
    }

    public function test_application_detail_and_status_transition_are_runtime_backed(): void
    {
        $this->sanctumActingAs('owner-user');

        $this->getJson('/api/v1/clients/client-jamie-foster/applications/application-jamie-foster-001')
            ->assertOk()
            ->assertJsonPath('data.application.applicationNumber', 'APP-SEED001')
            ->assertJsonPath('data.application.currentStatus.code', 'in_review')
            ->assertJsonPath('data.ruleNotes.0.ruleKey', 'application.high_value.review');

        $response = $this
            ->withHeader('X-Correlation-Id', 'corr-application-status-runtime')
            ->postJson('/api/v1/clients/client-jamie-foster/applications/application-jamie-foster-001/status-transitions', [
                'targetStatus' => 'approved',
                'reason' => 'Runtime status transition verification',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.application.currentStatus.code', 'approved');

        $history = ApplicationStatusHistory::query()
            ->withoutGlobalScopes()
            ->where('application_id', 'application-jamie-foster-001')
            ->latest('occurred_at')
            ->first();

        self::assertNotNull($history);
        self::assertSame('approved', $history->to_status);
    }
}
