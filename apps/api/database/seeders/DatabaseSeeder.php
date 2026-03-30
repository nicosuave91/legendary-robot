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
    }
}
