<?php

declare(strict_types=1);

namespace App\Modules\WorkflowBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\Shared\Tenancy\TenantScoped;

final class WorkflowRun extends Model
{
    use TenantScoped;

    protected $table = 'workflow_runs';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'tenant_id',
        'workflow_id',
        'workflow_version_id',
        'trigger_event',
        'subject_type',
        'subject_id',
        'status',
        'current_step_index',
        'idempotency_key',
        'correlation_id',
        'trigger_payload_snapshot',
        'runtime_context',
        'queued_at',
        'started_at',
        'completed_at',
        'failed_at',
        'failure_summary',
    ];

    protected $casts = [
        'trigger_payload_snapshot' => 'array',
        'runtime_context' => 'array',
        'failure_summary' => 'array',
        'queued_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class, 'workflow_id');
    }

    public function workflowVersion(): BelongsTo
    {
        return $this->belongsTo(WorkflowVersion::class, 'workflow_version_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(WorkflowRunLog::class, 'workflow_run_id');
    }
}