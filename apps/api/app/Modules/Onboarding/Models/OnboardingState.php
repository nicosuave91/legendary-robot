<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Shared\Tenancy\TenantScoped;

final class OnboardingState extends Model
{
    use TenantScoped;

    protected $table = 'onboarding_states';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'tenant_id',
        'user_id',
        'state',
        'required_at',
        'started_at',
        'completed_at',
        'exempted_at',
        'reset_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'required_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'exempted_at' => 'datetime',
        'reset_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
