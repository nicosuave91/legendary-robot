<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\Shared\Tenancy\TenantScoped;

final class Notification extends Model
{
    use TenantScoped;

    protected $table = 'notifications';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'tenant_id',
        'target_user_id',
        'audience_scope',
        'notification_type',
        'category',
        'title',
        'body',
        'tone',
        'action_url',
        'source_event_type',
        'source_event_id',
        'payload_snapshot',
        'emitted_at',
    ];

    protected $casts = [
        'payload_snapshot' => 'array',
        'emitted_at' => 'datetime',
    ];

    public function reads(): HasMany
    {
        return $this->hasMany(NotificationRead::class, 'notification_id');
    }

    public function dismissals(): HasMany
    {
        return $this->hasMany(ToastDismissal::class, 'notification_id');
    }
}
