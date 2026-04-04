\
<?php

declare(strict_types=1);

namespace App\Modules\CalendarTasks\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Shared\Tenancy\TenantScoped;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $event_id
 * @property string|null $assigned_user_id
 * @property string $title
 * @property string|null $description
 * @property string $status
 * @property int $sort_order
 * @property bool $is_required
 * @property \Illuminate\Support\Carbon|null $due_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property string|null $blocked_reason
 * @property array<string, mixed>|null $metadata
 * @property-read User|null $assignedUser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TaskStatusHistory> $history
 */
final class EventTask extends Model
{
    use TenantScoped;

    protected $table = 'event_tasks';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'tenant_id',
        'event_id',
        'assigned_user_id',
        'created_by',
        'updated_by',
        'title',
        'description',
        'status',
        'sort_order',
        'is_required',
        'due_at',
        'completed_at',
        'blocked_reason',
        'metadata',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'sort_order' => 'integer',
        'is_required' => 'boolean',
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(CalendarEvent::class, 'event_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function history(): HasMany
    {
        return $this->hasMany(TaskStatusHistory::class, 'event_task_id');
    }
}
