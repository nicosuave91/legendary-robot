<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Modules\IdentityAccess\Models\Permission;
use App\Modules\IdentityAccess\Models\Role;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Onboarding\Models\OnboardingState;
use App\Modules\Onboarding\Models\UserProfile;
use App\Modules\Applications\Models\Application;
use App\Modules\Applications\Models\ApplicationRuleApplication;
use App\Modules\Applications\Models\ApplicationStatusHistory;
use App\Modules\Clients\Models\Client;
use App\Modules\Clients\Models\ClientNote;
use App\Modules\Disposition\Models\ClientDispositionHistory;
use App\Modules\Disposition\Models\DispositionDefinition;
use App\Modules\TenantGovernance\Models\Tenant;
use App\Modules\TenantGovernance\Models\ThemeSetting;
use App\Modules\TenantGovernance\Models\TenantIndustryConfiguration;
use App\Modules\TenantGovernance\Support\DefaultIndustryCapabilities;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->firstOrCreate([
            'id' => 'tenant-default',
        ], [
            'name' => 'Default Workspace',
        ]);

        ThemeSetting::query()->firstOrCreate([
            'tenant_id' => $tenant->id,
        ], [
            'primary_color' => '#1d4ed8',
            'secondary_color' => '#0f172a',
            'tertiary_color' => '#64748b',
        ]);

        $permissions = [
            'identity-access.auth.read-self' => 'Read own auth context',
            'identity-access.auth.sign-out' => 'Sign out current session',
            'settings.profile.read' => 'Read profile settings',
            'settings.profile.update' => 'Update profile settings',
            'settings.accounts.read' => 'Read tenant accounts',
            'settings.accounts.create' => 'Create tenant accounts',
            'settings.accounts.update' => 'Update tenant accounts',
            'settings.accounts.decommission' => 'Decommission tenant accounts',
            'settings.theme.read' => 'Read tenant theme settings',
            'settings.theme.update' => 'Update tenant theme settings',
            'settings.industry-configurations.read' => 'Read tenant industry configuration versions',
            'settings.industry-configurations.create' => 'Create tenant industry configuration versions',
            'dashboard.summary.read' => 'Read homepage dashboard summary',
            'dashboard.production.read' => 'Read homepage production metrics',
            'clients.read' => 'Read client records',
            'clients.read.all' => 'Read all tenant client records',
            'clients.create' => 'Create client records',
            'clients.update' => 'Update client records',
            'clients.notes.create' => 'Create client notes',
            'clients.documents.create' => 'Create client documents',
            'clients.communications.read' => 'Read client communications',
            'clients.communications.sms.send' => 'Send outbound SMS',
            'clients.communications.email.send' => 'Send outbound email',
            'clients.communications.call.create' => 'Create outbound call requests',
            'clients.disposition.read' => 'Read client disposition state',
            'clients.disposition.transition' => 'Transition client disposition state',
            'clients.applications.read' => 'Read client applications',
            'clients.applications.create' => 'Create client applications',
            'clients.applications.status.transition' => 'Transition application status',
            'rules.read' => 'Read governed rules',
            'rules.create' => 'Create governed rules',
            'rules.update-draft' => 'Update draft rule versions',
            'rules.publish' => 'Publish immutable rule versions',
            'rules.execution-logs.read' => 'Read rule execution evidence',
            'workflows.read' => 'Read governed workflows',
            'workflows.create' => 'Create governed workflows',
            'workflows.update-draft' => 'Update draft workflow versions',
            'workflows.publish' => 'Publish immutable workflow versions',
            'workflows.runs.read' => 'Read workflow run evidence',
            'imports.read' => 'Read governed imports ledger',
            'imports.create' => 'Upload governed import files',
            'imports.validate' => 'Validate staged imports',
            'imports.commit' => 'Commit validated staged imports',
            'notifications.read' => 'Read persistent notifications',
            'notifications.dismiss' => 'Dismiss persistent notifications',
            'audit.read' => 'Read tenant audit search',
            'onboarding.state.read' => 'Read onboarding state',
            'onboarding.profile.confirm' => 'Confirm onboarding profile',
            'onboarding.industry.select' => 'Select onboarding industry',
            'onboarding.complete' => 'Complete onboarding',
        ];

        foreach ($permissions as $name => $displayName) {
            Permission::query()->firstOrCreate([
                'id' => $name,
            ], [
                'name' => $name,
                'display_name' => $displayName,
            ]);
        }

        $ownerRole = Role::query()->firstOrCreate(['id' => 'owner'], ['name' => 'owner', 'display_name' => 'Owner']);
        $adminRole = Role::query()->firstOrCreate(['id' => 'admin'], ['name' => 'admin', 'display_name' => 'Admin']);
        $userRole = Role::query()->firstOrCreate(['id' => 'user'], ['name' => 'user', 'display_name' => 'User']);

        $ownerRole->permissions()->sync(Permission::query()->pluck('id')->all());
        $adminRole->permissions()->sync(Permission::query()->pluck('id')->all());
        $userRole->permissions()->sync(Permission::query()->whereIn('name', [
            'identity-access.auth.read-self',
            'identity-access.auth.sign-out',
            'settings.profile.read',
            'settings.profile.update',
            'dashboard.summary.read',
            'dashboard.production.read',
            'clients.read',
            'clients.notes.create',
            'clients.documents.create',
            'clients.communications.read',
            'clients.communications.sms.send',
            'clients.communications.email.send',
            'clients.communications.call.create',
            'clients.disposition.read',
            'clients.disposition.transition',
            'clients.applications.read',
            'clients.applications.create',
            'clients.applications.status.transition',
            'rules.read',
            'rules.execution-logs.read',
            'workflows.read',
            'workflows.runs.read',
            'notifications.read',
            'notifications.dismiss',
            'onboarding.state.read',
            'onboarding.profile.confirm',
            'onboarding.industry.select',
            'onboarding.complete',
        ])->pluck('id')->all());

        $owner = User::query()->withTrashed()->firstOrCreate([
            'id' => 'owner-user',
        ], [
            'tenant_id' => $tenant->id,
            'name' => 'Tenant Owner',
            'email' => 'owner@example.com',
            'password' => Hash::make('Password123!'),
            'status' => 'active',
        ]);
        $owner->roles()->sync([$ownerRole->id]);
        UserProfile::query()->firstOrCreate([
            'id' => 'owner-profile',
        ], [
            'tenant_id' => $tenant->id,
            'user_id' => $owner->id,
            'first_name' => 'Tenant',
            'last_name' => 'Owner',
            'profile_confirmed_at' => now(),
        ]);
        OnboardingState::query()->firstOrCreate([
            'id' => 'owner-onboarding',
        ], [
            'tenant_id' => $tenant->id,
            'user_id' => $owner->id,
            'state' => 'not_applicable',
            'exempted_at' => now(),
        ]);

        $admin = User::query()->withTrashed()->firstOrCreate([
            'id' => 'admin-user',
        ], [
            'tenant_id' => $tenant->id,
            'name' => 'Operations Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('Password123!'),
            'status' => 'active',
            'created_by' => $owner->id,
        ]);
        $admin->roles()->sync([$adminRole->id]);
        UserProfile::query()->firstOrCreate([
            'id' => 'admin-profile',
        ], [
            'tenant_id' => $tenant->id,
            'user_id' => $admin->id,
            'first_name' => 'Operations',
            'last_name' => 'Admin',
        ]);
        OnboardingState::query()->firstOrCreate([
            'id' => 'admin-onboarding',
        ], [
            'tenant_id' => $tenant->id,
            'user_id' => $admin->id,
            'state' => 'required',
            'required_at' => now(),
        ]);

        $user = User::query()->withTrashed()->firstOrCreate([
            'id' => 'standard-user',
        ], [
            'tenant_id' => $tenant->id,
            'name' => 'Team Member',
            'email' => 'user@example.com',
            'password' => Hash::make('Password123!'),
            'status' => 'active',
            'created_by' => $admin->id,
        ]);
        $user->roles()->sync([$userRole->id]);
        UserProfile::query()->firstOrCreate([
            'id' => 'user-profile',
        ], [
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'first_name' => 'Team',
            'last_name' => 'Member',
        ]);
        OnboardingState::query()->firstOrCreate([
            'id' => 'user-onboarding',
        ], [
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'state' => 'required',
            'required_at' => now(),
        ]);

        foreach (DefaultIndustryCapabilities::all() as $industry => $capabilities) {
            TenantIndustryConfiguration::query()->firstOrCreate([
                'tenant_id' => $tenant->id,
                'industry' => $industry,
                'version' => 'v1',
            ], [
                'id' => strtolower($industry) . '-config-v1',
                'status' => 'published',
                'is_active' => true,
                'capabilities' => $capabilities,
                'notes' => 'Seeded baseline version.',
                'created_by' => $owner->id,
                'published_at' => now(),
                'activated_at' => now(),
            ]);
        }

        $seededClient = Client::query()->withoutGlobalScopes()->firstOrCreate([
            'id' => 'client-jamie-foster',
        ], [
            'tenant_id' => $tenant->id,
            'owner_user_id' => $admin->id,
            'created_by' => $admin->id,
            'display_name' => 'Jamie Foster',
            'first_name' => 'Jamie',
            'last_name' => 'Foster',
            'company_name' => 'Foster Family Holdings',
            'primary_email' => 'jamie.foster@example.com',
            'primary_phone' => '804-555-0101',
            'preferred_contact_channel' => 'email',
            'status' => 'active',
            'last_activity_at' => now(),
        ]);

        Client::query()->withoutGlobalScopes()->firstOrCreate([
            'id' => 'client-horizon-medical',
        ], [
            'tenant_id' => $tenant->id,
            'owner_user_id' => $user->id,
            'created_by' => $admin->id,
            'display_name' => 'Horizon Medical',
            'company_name' => 'Horizon Medical',
            'primary_email' => 'intake@horizon-medical.example.com',
            'primary_phone' => '804-555-0102',
            'preferred_contact_channel' => 'phone',
            'status' => 'lead',
            'last_activity_at' => now(),
        ]);

        ClientNote::query()->withoutGlobalScopes()->firstOrCreate([
            'id' => 'note-jamie-foster-001',
        ], [
            'tenant_id' => $tenant->id,
            'client_id' => $seededClient->id,
            'author_user_id' => $admin->id,
            'source_type' => 'user',
            'body' => 'Seeded note to make the homepage KPI and workspace baseline immediately visible after migration.',
            'is_editable' => true,
        ]);

        foreach ([
            ['code' => 'lead', 'label' => 'Lead', 'sort_order' => 10, 'is_initial' => true, 'is_terminal' => false, 'allowed_next_codes' => ['qualified', 'inactive'], 'prerequisites' => []],
            ['code' => 'qualified', 'label' => 'Qualified', 'sort_order' => 20, 'is_initial' => false, 'is_terminal' => false, 'allowed_next_codes' => ['applied', 'inactive'], 'prerequisites' => [['type' => 'any_contact_method', 'severity' => 'blocking']]],
            ['code' => 'applied', 'label' => 'Applied', 'sort_order' => 30, 'is_initial' => false, 'is_terminal' => false, 'allowed_next_codes' => ['active', 'inactive'], 'prerequisites' => [['type' => 'application_exists', 'severity' => 'blocking']]],
            ['code' => 'active', 'label' => 'Active', 'sort_order' => 40, 'is_initial' => false, 'is_terminal' => false, 'allowed_next_codes' => ['inactive'], 'prerequisites' => [['type' => 'approved_application_exists', 'severity' => 'blocking']]],
            ['code' => 'inactive', 'label' => 'Inactive', 'sort_order' => 50, 'is_initial' => false, 'is_terminal' => true, 'allowed_next_codes' => [], 'prerequisites' => []],
        ] as $definition) {
            DispositionDefinition::query()->withoutGlobalScopes()->firstOrCreate([
                'tenant_id' => $tenant->id,
                'code' => $definition['code'],
            ], [
                'id' => 'seed-disposition-' . $definition['code'],
                'label' => $definition['label'],
                'description' => $definition['label'] . ' lifecycle state',
                'sort_order' => $definition['sort_order'],
                'is_initial' => $definition['is_initial'],
                'is_terminal' => $definition['is_terminal'],
                'allowed_next_codes' => $definition['allowed_next_codes'],
                'prerequisites' => $definition['prerequisites'],
                'role_permissions' => null,
                'is_active' => true,
                'created_by' => $owner->id,
            ]);
        }

        ClientDispositionHistory::query()->withoutGlobalScopes()->firstOrCreate([
            'tenant_id' => $tenant->id,
            'client_id' => $seededClient->id,
            'to_disposition_code' => 'active',
        ], [
            'id' => 'seed-disposition-history-client-jamie',
            'actor_user_id' => $admin->id,
            'from_disposition_code' => null,
            'reason' => 'Seeded active client disposition.',
            'warnings_snapshot' => null,
            'occurred_at' => now(),
        ]);

        ClientDispositionHistory::query()->withoutGlobalScopes()->firstOrCreate([
            'tenant_id' => $tenant->id,
            'client_id' => 'client-horizon-medical',
            'to_disposition_code' => 'lead',
        ], [
            'id' => 'seed-disposition-history-client-horizon',
            'actor_user_id' => $admin->id,
            'from_disposition_code' => null,
            'reason' => 'Seeded lead client disposition.',
            'warnings_snapshot' => null,
            'occurred_at' => now(),
        ]);

        $seededApplication = Application::query()->withoutGlobalScopes()->firstOrCreate([
            'id' => 'application-jamie-foster-001',
        ], [
            'tenant_id' => $tenant->id,
            'client_id' => $seededClient->id,
            'application_number' => 'APP-SEED001',
            'owner_user_id' => $admin->id,
            'product_type' => 'mortgage',
            'external_reference' => 'EXT-001',
            'amount_requested' => 350000,
            'status' => 'in_review',
            'submitted_at' => now()->subDay(),
            'metadata' => ['seeded' => true],
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        ApplicationStatusHistory::query()->withoutGlobalScopes()->firstOrCreate([
            'tenant_id' => $tenant->id,
            'application_id' => $seededApplication->id,
            'to_status' => 'in_review',
        ], [
            'id' => 'application-status-jamie-foster-001',
            'actor_user_id' => $admin->id,
            'from_status' => null,
            'reason' => 'Seeded application status history.',
            'occurred_at' => now()->subHours(12),
            'metadata' => null,
        ]);

        ApplicationRuleApplication::query()->withoutGlobalScopes()->firstOrCreate([
            'tenant_id' => $tenant->id,
            'application_id' => $seededApplication->id,
            'rule_key' => 'application.high_value.review',
        ], [
            'id' => 'application-rule-jamie-foster-001',
            'rule_version' => 'sprint7-v1',
            'trigger_event' => 'application.created',
            'outcome' => 'info',
            'title' => 'High-value application review note',
            'note_body' => 'Seeded rule-note evidence for the Sprint 7 application workspace.',
            'is_blocking' => false,
            'evidence' => ['threshold' => 250000, 'amountRequested' => 350000],
            'applied_at' => now()->subHours(11),
        ]);

        \App\Modules\Notifications\Models\Notification::query()->withoutGlobalScopes()->firstOrCreate([
            'id' => 'seed-notification-import-ready',
        ], [
            'tenant_id' => $tenant->id,
            'target_user_id' => $admin->id,
            'audience_scope' => 'user',
            'notification_type' => 'import.validation',
            'category' => 'imports',
            'title' => 'Seeded import notification',
            'body' => 'Sprint 9 seeded notification proving the persistent center can render durable items.',
            'tone' => 'info',
            'action_url' => '/app/imports',
            'source_event_type' => 'seed',
            'source_event_id' => 'seed-notification-import-ready',
            'payload_snapshot' => ['seeded' => true],
            'emitted_at' => now()->subMinutes(15),
        ]);
    }
}
