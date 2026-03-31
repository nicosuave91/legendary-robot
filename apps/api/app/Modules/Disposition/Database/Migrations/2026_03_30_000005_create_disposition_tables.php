<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('disposition_definitions', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('code')->index();
            $table->string('label');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_initial')->default(false);
            $table->boolean('is_terminal')->default(false);
            $table->json('allowed_next_codes');
            $table->json('prerequisites')->nullable();
            $table->json('role_permissions')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->string('created_by')->nullable()->index();
            $table->timestampsTz();
            $table->unique(['tenant_id', 'code']);
        });

        Schema::create('client_disposition_history', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('client_id')->index();
            $table->string('actor_user_id')->nullable()->index();
            $table->string('from_disposition_code')->nullable();
            $table->string('to_disposition_code')->index();
            $table->text('reason')->nullable();
            $table->json('warnings_snapshot')->nullable();
            $table->timestampTz('occurred_at')->index();
            $table->timestampsTz();
            $table->index(['tenant_id', 'client_id', 'occurred_at']);
        });

        $seededByTenant = [];

        DB::table('users')->select('tenant_id', 'id')->orderBy('created_at')->get()
            ->groupBy('tenant_id')
            ->each(function ($users, string $tenantId) use (&$seededByTenant): void {
                if (isset($seededByTenant[$tenantId])) {
                    return;
                }

                $creatorId = (string) ($users->first()->id ?? '');
                $definitions = [
                    ['code' => 'lead', 'label' => 'Lead', 'sort_order' => 10, 'is_initial' => true, 'is_terminal' => false, 'allowed_next_codes' => ['qualified', 'inactive'], 'prerequisites' => []],
                    ['code' => 'qualified', 'label' => 'Qualified', 'sort_order' => 20, 'is_initial' => false, 'is_terminal' => false, 'allowed_next_codes' => ['applied', 'inactive'], 'prerequisites' => [['type' => 'any_contact_method', 'severity' => 'blocking']]],
                    ['code' => 'applied', 'label' => 'Applied', 'sort_order' => 30, 'is_initial' => false, 'is_terminal' => false, 'allowed_next_codes' => ['active', 'inactive'], 'prerequisites' => [['type' => 'application_exists', 'severity' => 'blocking']]],
                    ['code' => 'active', 'label' => 'Active', 'sort_order' => 40, 'is_initial' => false, 'is_terminal' => false, 'allowed_next_codes' => ['inactive'], 'prerequisites' => [['type' => 'approved_application_exists', 'severity' => 'blocking']]],
                    ['code' => 'inactive', 'label' => 'Inactive', 'sort_order' => 50, 'is_initial' => false, 'is_terminal' => true, 'allowed_next_codes' => [], 'prerequisites' => []],
                ];

                foreach ($definitions as $definition) {
                    DB::table('disposition_definitions')->updateOrInsert(
                        ['tenant_id' => $tenantId, 'code' => $definition['code']],
                        [
                            'id' => 'disp-' . $tenantId . '-' . $definition['code'],
                            'label' => $definition['label'],
                            'description' => $definition['label'] . ' lifecycle state',
                            'sort_order' => $definition['sort_order'],
                            'is_initial' => $definition['is_initial'],
                            'is_terminal' => $definition['is_terminal'],
                            'allowed_next_codes' => json_encode($definition['allowed_next_codes'], JSON_THROW_ON_ERROR),
                            'prerequisites' => json_encode($definition['prerequisites'], JSON_THROW_ON_ERROR),
                            'role_permissions' => null,
                            'is_active' => true,
                            'created_by' => $creatorId ?: null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }

                $seededByTenant[$tenantId] = true;
            });

        DB::table('clients')
            ->leftJoin('client_disposition_history', 'client_disposition_history.client_id', '=', 'clients.id')
            ->whereNull('client_disposition_history.client_id')
            ->select('clients.id', 'clients.tenant_id', 'clients.created_by', 'clients.status', 'clients.created_at')
            ->orderBy('clients.created_at')
            ->get()
            ->each(function ($client): void {
                $status = in_array((string) $client->status, ['lead', 'qualified', 'applied', 'active', 'inactive'], true)
                    ? (string) $client->status
                    : (((string) $client->status === 'active') ? 'active' : (((string) $client->status === 'inactive') ? 'inactive' : 'lead'));

                DB::table('client_disposition_history')->insert([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => (string) $client->tenant_id,
                    'client_id' => (string) $client->id,
                    'actor_user_id' => $client->created_by ? (string) $client->created_by : null,
                    'from_disposition_code' => null,
                    'to_disposition_code' => $status,
                    'reason' => 'Backfilled from legacy client.status during Sprint 7 migration.',
                    'warnings_snapshot' => null,
                    'occurred_at' => $client->created_at ?? now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('clients')->where('id', $client->id)->update(['status' => $status, 'updated_at' => now()]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_disposition_history');
        Schema::dropIfExists('disposition_definitions');
    }
};
