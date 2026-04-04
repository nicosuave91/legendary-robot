\
<?php

declare(strict_types=1);

namespace App\Modules\Communications\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Modules\Clients\Models\Client;
use App\Modules\Shared\Tenancy\TenantScoped;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string|null $client_id
 * @property string $communication_thread_id
 * @property string $channel
 * @property string $direction
 * @property string $lifecycle_status
 * @property string|null $provider_name
 * @property string|null $provider_message_id
 * @property string|null $provider_status
 * @property string|null $from_address
 * @property string|null $to_address
 * @property string|null $subject
 * @property string|null $body_text
 * @property string|null $body_html
 * @property string|null $idempotency_key
 * @property string|null $correlation_key
 * @property \Illuminate\Support\Carbon|null $queued_at
 * @property \Illuminate\Support\Carbon|null $submitted_at
 * @property \Illuminate\Support\Carbon|null $finalized_at
 * @property string|null $failure_code
 * @property string|null $failure_message
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class CommunicationMessage extends Model
{
    use TenantScoped;

    protected $table = 'communication_messages';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = ['id', 'tenant_id', 'client_id', 'communication_thread_id', 'channel', 'direction', 'lifecycle_status', 'provider_name', 'provider_message_id', 'provider_status', 'from_address', 'to_address', 'subject', 'body_text', 'body_html', 'idempotency_key', 'correlation_key', 'queued_at', 'submitted_at', 'finalized_at', 'failure_code', 'failure_message', 'created_by'];

    /**
     * @var array<string, string>
     */
    protected $casts = ['queued_at' => 'datetime', 'submitted_at' => 'datetime', 'finalized_at' => 'datetime'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(CommunicationThread::class, 'communication_thread_id');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(CommunicationAttachment::class, 'attachable');
    }
}
