<?php

declare(strict_types=1);

namespace App\Modules\Applications\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\Clients\Models\Client;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Shared\Tenancy\TenantScoped;

final class Application extends Model
{
    use TenantScoped;

    protected $table = 'applications';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'tenant_id',
        'client_id',
        'application_number',
        'owner_user_id',
        'product_type',
        'external_reference',
        'amount_requested',
        'status',
        'submitted_at',
        'metadata',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'metadata' => 'array',
        'amount_requested' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(ApplicationStatusHistory::class, 'application_id');
    }

    public function ruleApplications(): HasMany
    {
        return $this->hasMany(ApplicationRuleApplication::class, 'application_id');
    }
}
