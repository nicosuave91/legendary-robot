<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Shared\Tenancy\TenantScoped;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $user_id
 * @property string $industry
 * @property string $config_version
 * @property \Illuminate\Support\Carbon|null $assigned_at
 */
final class UserIndustryAssignment extends Model
{
    use TenantScoped;

    protected $table = 'user_industry_assignments';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'tenant_id',
        'user_id',
        'industry',
        'config_version',
        'assigned_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
