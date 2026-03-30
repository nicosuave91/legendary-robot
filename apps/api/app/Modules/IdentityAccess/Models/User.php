<?php

declare(strict_types=1);

namespace App\Modules\IdentityAccess\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Modules\TenantGovernance\Models\Tenant;
use App\Modules\Onboarding\Models\OnboardingState;
use App\Modules\Onboarding\Models\UserIndustryAssignment;
use App\Modules\Onboarding\Models\UserProfile;

final class User extends Authenticatable
{
    use HasApiTokens;
    use Notifiable;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'tenant_id',
        'name',
        'email',
        'password',
        'status',
        'deactivated_at',
        'created_by',
    ];

    /**
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'deactivated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class, 'user_id');
    }

    public function onboardingState(): HasOne
    {
        return $this->hasOne(OnboardingState::class, 'user_id');
    }

    public function industryAssignment(): HasOne
    {
        return $this->hasOne(UserIndustryAssignment::class, 'user_id');
    }

    public function hasRole(string $role): bool
    {
        return $this->roles->contains(fn (Role $item): bool => $item->name === $role);
    }

    public function hasPermission(string $permission): bool
    {
        return $this->roles->flatMap(
            fn (Role $role) => $role->permissions->pluck('name')
        )->contains($permission);
    }
}
