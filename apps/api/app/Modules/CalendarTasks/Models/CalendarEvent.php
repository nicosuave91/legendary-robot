<?php

declare(strict_types=1);

namespace App\Modules\CalendarTasks\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\Clients\Models\Client;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Shared\Tenancy\TenantScoped;

final class CalendarEvent extends Model
{
    use TenantScoped;

    protected $table = 'events';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'tenant_id',
        'client_id',
        'owner_user_id',
        'created_by',
        'updated_by',
        'title',
        'description',
        'event_type',
        'status',
        'starts_at',
        'ends_at',
        'is_all_day',
        'location',
        'metadata',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_all_day' => 'boolean',
        'metadata' => 'array',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(EventTask::class, 'event_id');
    }
}
