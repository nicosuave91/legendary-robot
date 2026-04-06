<?php

declare(strict_types=1);

namespace App\Modules\Communications\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Shared\Tenancy\TenantScoped;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $client_id
 * @property string $communication_thread_id
 * @property string $provider_name
 * @property string $alias_local_part
 * @property string $inbound_address
 * @property string|null $label
 * @property bool $is_active
 * @property array<string, mixed>|null $metadata
 * @property-read CommunicationThread $thread
 */
final class CommunicationMailbox extends Model
{
    use TenantScoped;

    protected $table = 'communication_mailboxes';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'tenant_id',
        'client_id',
        'communication_thread_id',
        'provider_name',
        'alias_local_part',
        'inbound_address',
        'label',
        'is_active',
        'metadata',
        'created_by',
        'updated_by',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(CommunicationThread::class, 'communication_thread_id');
    }
}
