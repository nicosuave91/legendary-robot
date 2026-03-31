<?php

declare(strict_types=1);

namespace App\Modules\Clients\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Shared\Tenancy\TenantScoped;

final class ClientStatusHistory extends Model
{
    use TenantScoped;

    protected $table = 'client_status_history';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id','tenant_id','client_id','actor_user_id','from_status','to_status','reason','occurred_at'];
    protected $casts = ['occurred_at' => 'datetime'];

    public function client(): BelongsTo { return $this->belongsTo(Client::class, 'client_id'); }
    public function actor(): BelongsTo { return $this->belongsTo(User::class, 'actor_user_id'); }
}
