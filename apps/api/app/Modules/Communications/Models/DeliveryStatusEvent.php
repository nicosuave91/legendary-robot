<?php

declare(strict_types=1);

namespace App\Modules\Communications\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Shared\Tenancy\TenantScoped;

final class DeliveryStatusEvent extends Model
{
    use TenantScoped;

    protected $table = 'delivery_status_events';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id', 'tenant_id', 'client_id', 'subject_type', 'subject_id', 'provider_name', 'provider_reference', 'provider_event_id', 'provider_event_type', 'provider_status', 'occurred_at', 'received_at', 'correlation_key', 'signature_verified', 'dedupe_hash', 'raw_payload', 'status_before', 'status_after'];
    protected $casts = ['raw_payload' => 'array', 'signature_verified' => 'boolean', 'occurred_at' => 'datetime', 'received_at' => 'datetime'];
}
