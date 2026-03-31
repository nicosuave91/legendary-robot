<?php

declare(strict_types=1);

namespace App\Modules\WorkflowBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Shared\Tenancy\TenantScoped;

final class WorkflowVersion extends Model
{
    use TenantScoped;

    protected $table = 'workflow_versions';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'tenant_id',
        'workflow_id',
        'version_number',
        'lifecycle_state',
        'trigger_definition',
        'steps_definition',
        'checksum',
        'published_at',
        'published_by',
        'supersedes_version_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'trigger_definition' => 'array',
        'steps_definition' => 'array',
        'published_at' => 'datetime',
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class, 'workflow_id');
    }
}