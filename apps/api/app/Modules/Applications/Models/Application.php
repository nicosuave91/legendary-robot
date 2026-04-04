<?php

declare(strict_types=1);

namespace App\Modules\Applications\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\Clients\Models\Client;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Shared\Tenancy\TenantScoped;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $client_id
 * @property string $application_number
 * @property string|null $owner_user_id
 * @property string $product_type
 * @property string|null $external_reference
 * @property float|int|string|null $amount_requested
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $submitted_at
 * @property array<string, mixed>|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Client $client
 * @property-read User|null $owner
 */
final class Application extends Model
{
    use TenantScoped;

    protected $table = 'applications';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * @var list<string>
     */
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

    /**
     * @var array<string, string>
     */
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
