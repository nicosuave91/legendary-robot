<?php

declare(strict_types=1);

namespace App\Modules\Communications\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Shared\Tenancy\TenantScoped;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $channel
 * @property string $provider_name
 * @property string $endpoint_kind
 * @property string $address_display
 * @property string $address_normalized
 * @property string|null $label
 * @property bool $is_active
 * @property bool $is_default_outbound
 * @property array<string, mixed>|null $metadata
 */
final class CommunicationEndpoint extends Model
{
    use TenantScoped;

    protected $table = 'communication_endpoints';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'tenant_id',
        'channel',
        'provider_name',
        'endpoint_kind',
        'address_display',
        'address_normalized',
        'label',
        'is_active',
        'is_default_outbound',
        'metadata',
        'created_by',
        'updated_by',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_default_outbound' => 'boolean',
        'metadata' => 'array',
    ];
}
