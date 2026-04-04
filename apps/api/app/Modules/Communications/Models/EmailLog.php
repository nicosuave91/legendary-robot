\
<?php

declare(strict_types=1);

namespace App\Modules\Communications\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Shared\Tenancy\TenantScoped;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string|null $client_id
 * @property string $communication_message_id
 * @property string|null $provider_name
 * @property string|null $provider_message_id
 * @property string|null $from_email
 * @property array<int, string>|null $to_emails
 * @property array<int, string>|null $cc_emails
 * @property array<int, string>|null $bcc_emails
 * @property string|null $reply_to_email
 * @property array<string, mixed>|null $provider_metadata
 * @property \Illuminate\Support\Carbon|null $last_provider_event_at
 */
final class EmailLog extends Model
{
    use TenantScoped;

    protected $table = 'email_logs';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = ['id', 'tenant_id', 'client_id', 'communication_message_id', 'provider_name', 'provider_message_id', 'from_email', 'to_emails', 'cc_emails', 'bcc_emails', 'reply_to_email', 'provider_metadata', 'last_provider_event_at'];

    /**
     * @var array<string, string>
     */
    protected $casts = ['to_emails' => 'array', 'cc_emails' => 'array', 'bcc_emails' => 'array', 'provider_metadata' => 'array', 'last_provider_event_at' => 'datetime'];

    public function message(): BelongsTo
    {
        return $this->belongsTo(CommunicationMessage::class, 'communication_message_id');
    }
}
