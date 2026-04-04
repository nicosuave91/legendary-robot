<?php

declare(strict_types=1);

namespace App\Modules\WorkflowBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Shared\Tenancy\TenantScoped;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $workflow_run_id
 * @property string $workflow_version_id
 * @property int|null $step_index
 * @property string $log_type
 * @property string $message
 * @property array<string, mixed>|null $payload_snapshot
 * @property \Illuminate\Support\Carbon|null $occurred_at
 * @property-read WorkflowRun $workflowRun
 */
final class WorkflowRunLog extends Model
{
    use TenantScoped;

    protected $table = 'workflow_run_logs';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'tenant_id',
        'workflow_run_id',
        'workflow_version_id',
        'step_index',
        'log_type',
        'message',
        'payload_snapshot',
        'occurred_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'payload_snapshot' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function workflowRun(): BelongsTo
    {
        return $this->belongsTo(WorkflowRun::class, 'workflow_run_id');
    }
}
