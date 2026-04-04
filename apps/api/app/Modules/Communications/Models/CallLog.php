<?php

declare(strict_types=1);

namespace App\Modules\Communications\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Clients\Models\Client;
use App\Modules\Shared\Tenancy\TenantScoped;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string|null $client_id
 * @property string $direction
 * @property string $lifecycle_status
 * @property string|null $provider_name
 * @property string|null $provider_call_id
 * @property string|null $from_number
 * @property string|null $to_number
 * @property string|null $correlation_key
 * @property \Illuminate\Support\Carbon|null $queued_at
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $ended_at
 * @property int|null $duration_seconds
 * @property string|null $failure_code
 * @property string|null $failure_message
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class CallLog extends Model
{
    use TenantScoped;

    protected $table = 'call_logs';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = ['id', 'tenant_id', 'client_id', 'direction', 'lifecycle_status', 'provider_name', 'provider_call_id', 'from_number', 'to_number', 'correlation_key', 'queued_at', 'started_at', 'ended_at', 'duration_seconds', 'failure_code', 'failure_message', 'initiated_by'];

    /**
     * @var array<string, string>
     */
    protected $casts = ['queued_at' => 'datetime', 'started_at' => 'datetime', 'ended_at' => 'datetime', 'duration_seconds' => 'integer'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
