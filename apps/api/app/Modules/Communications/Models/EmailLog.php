<?php

declare(strict_types=1);

namespace App\Modules\Communications\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Shared\Tenancy\TenantScoped;

final class EmailLog extends Model
{
    use TenantScoped;

    protected $table = 'email_logs';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id', 'tenant_id', 'client_id', 'communication_message_id', 'provider_name', 'provider_message_id', 'from_email', 'to_emails', 'cc_emails', 'bcc_emails', 'reply_to_email', 'provider_metadata', 'last_provider_event_at'];
    protected $casts = ['to_emails' => 'array', 'cc_emails' => 'array', 'bcc_emails' => 'array', 'provider_metadata' => 'array', 'last_provider_event_at' => 'datetime'];

    public function message(): BelongsTo
    {
        return $this->belongsTo(CommunicationMessage::class, 'communication_message_id');
    }
}
