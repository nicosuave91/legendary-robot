<?php

declare(strict_types=1);

namespace App\Modules\WorkflowBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Shared\Tenancy\TenantScoped;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $workflow_id
 * @property int $version_number
 * @property string $lifecycle_state
 * @property array<string, mixed> $trigger_definition
 * @property array<int, mixed> $steps_definition
 * @property string $checksum
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property string|null $published_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class WorkflowVersion extends Model
{
    use TenantScoped;

    protected $table = 'workflow_versions';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * @var list<string>
     */
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

    /**
     * @var array<string, string>
     */
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
