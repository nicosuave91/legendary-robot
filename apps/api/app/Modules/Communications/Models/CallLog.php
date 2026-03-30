<?php

declare(strict_types=1);

namespace App\Modules\Communications\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Clients\Models\Client;
use App\Modules\Shared\Tenancy\TenantScoped;

final class CallLog extends Model
{
    use TenantScoped;

    protected $table = 'call_logs';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id', 'tenant_id', 'client_id', 'direction', 'lifecycle_status', 'provider_name', 'provider_call_id', 'from_number', 'to_number', 'correlation_key', 'queued_at', 'started_at', 'ended_at', 'duration_seconds', 'failure_code', 'failure_message', 'initiated_by'];
    protected $casts = ['queued_at' => 'datetime', 'started_at' => 'datetime', 'ended_at' => 'datetime', 'duration_seconds' => 'integer'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
