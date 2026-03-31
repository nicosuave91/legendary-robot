<?php

declare(strict_types=1);

namespace App\Modules\Communications\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Modules\Clients\Models\Client;
use App\Modules\Shared\Tenancy\TenantScoped;

final class CommunicationMessage extends Model
{
    use TenantScoped;

    protected $table = 'communication_messages';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id', 'tenant_id', 'client_id', 'communication_thread_id', 'channel', 'direction', 'lifecycle_status', 'provider_name', 'provider_message_id', 'provider_status', 'from_address', 'to_address', 'subject', 'body_text', 'body_html', 'idempotency_key', 'correlation_key', 'queued_at', 'submitted_at', 'finalized_at', 'failure_code', 'failure_message', 'created_by'];
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
