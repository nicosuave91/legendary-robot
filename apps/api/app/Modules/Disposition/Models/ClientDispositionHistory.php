<?php

declare(strict_types=1);

namespace App\Modules\Disposition\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Clients\Models\Client;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Shared\Tenancy\TenantScoped;

final class ClientDispositionHistory extends Model
{
    use TenantScoped;

    protected $table = 'client_disposition_history';
    public $incrementing = false;
    protected $keyType = 'string';

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
