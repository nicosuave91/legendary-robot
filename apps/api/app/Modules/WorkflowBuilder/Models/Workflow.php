<?php

declare(strict_types=1);

namespace App\Modules\WorkflowBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\Shared\Tenancy\TenantScoped;

final class Workflow extends Model
{
    use TenantScoped;

    protected $table = 'workflows';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'tenant_id',
        'workflow_key',
        'name',
        'description',
        'status',
        'latest_published_version_id',
        'current_draft_version_id',
        'created_by',
        'updated_by',
        'retired_at',
    ];

    protected $casts = [
        'retired_at' => 'datetime',
    ];

    public function latestPublishedVersion(): BelongsTo
    {
        return $this->belongsTo(WorkflowVersion::class, 'latest_published_version_id');
    }

    public function currentDraftVersion(): BelongsTo
    {
        return $this->belongsTo(WorkflowVersion::class, 'current_draft_version_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(WorkflowVersion::class, 'workflow_id');
    }
}