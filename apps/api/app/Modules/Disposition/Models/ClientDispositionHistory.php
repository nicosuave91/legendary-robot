\
<?php

declare(strict_types=1);

namespace App\Modules\Disposition\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Clients\Models\Client;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Shared\Tenancy\TenantScoped;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $client_id
 * @property string|null $actor_user_id
 * @property string|null $from_disposition_code
 * @property string $to_disposition_code
 * @property string|null $reason
 * @property array<string, mixed>|null $warnings_snapshot
 * @property \Illuminate\Support\Carbon|null $occurred_at
 * @property-read User|null $actor
 */
final class ClientDispositionHistory extends Model
{
    use TenantScoped;

    protected $table = 'client_disposition_history';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'tenant_id',
        'client_id',
        'actor_user_id',
        'from_disposition_code',
        'to_disposition_code',
        'reason',
        'warnings_snapshot',
        'occurred_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'warnings_snapshot' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
