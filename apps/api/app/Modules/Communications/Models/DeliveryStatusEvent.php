<?php

declare(strict_types=1);

namespace App\Modules\Communications\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Shared\Tenancy\TenantScoped;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string|null $client_id
 * @property string $subject_type
 * @property string $subject_id
 * @property string|null $provider_name
 * @property string|null $provider_reference
 * @property string|null $provider_event_id
 * @property string|null $provider_event_type
 * @property string|null $provider_status
 * @property \Illuminate\Support\Carbon|null $occurred_at
 * @property \Illuminate\Support\Carbon|null $received_at
 * @property string|null $correlation_key
 * @property bool $signature_verified
 * @property string|null $dedupe_hash
 * @property array<string, mixed>|null $raw_payload
 * @property string|null $status_before
 * @property string|null $status_after
 */
final class DeliveryStatusEvent extends Model
{
    use TenantScoped;

    protected $table = 'delivery_status_events';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = ['id', 'tenant_id', 'client_id', 'subject_type', 'subject_id', 'provider_name', 'provider_reference', 'provider_event_id', 'provider_event_type', 'provider_status', 'occurred_at', 'received_at', 'correlation_key', 'signature_verified', 'dedupe_hash', 'raw_payload', 'status_before', 'status_after'];

    /**
     * @var array<string, string>
     */
    protected $casts = ['raw_payload' => 'array', 'signature_verified' => 'boolean', 'occurred_at' => 'datetime', 'received_at' => 'datetime'];
}
