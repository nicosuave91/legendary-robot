<?php

declare(strict_types=1);

namespace App\Modules\WorkflowBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Shared\Tenancy\TenantScoped;

final class WorkflowRunLog extends Model
{
    use TenantScoped;

    protected $table = 'workflow_run_logs';
    public $incrementing = false;
    protected $keyType = 'string';

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

    protected $casts = [
        'payload_snapshot' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function workflowRun(): BelongsTo
    {
        return $this->belongsTo(WorkflowRun::class, 'workflow_run_id');
    }
}