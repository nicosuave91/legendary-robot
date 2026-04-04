<?php

declare(strict_types=1);

namespace App\Modules\RulesLibrary\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Shared\Tenancy\TenantScoped;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $rule_id
 * @property int $version_number
 * @property string $lifecycle_state
 * @property string $trigger_event
 * @property string $severity
 * @property array<string, mixed>|null $industry_scope
 * @property array<string, mixed> $condition_definition
 * @property array<string, mixed> $action_definition
 * @property string|null $execution_label
 * @property string|null $note_template
 * @property string $checksum
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property string|null $published_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class RuleVersion extends Model
{
    use TenantScoped;

    protected $table = 'rule_versions';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'tenant_id',
        'rule_id',
        'version_number',
        'lifecycle_state',
        'trigger_event',
        'severity',
        'industry_scope',
        'condition_definition',
        'action_definition',
        'execution_label',
        'note_template',
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
        'industry_scope' => 'array',
        'condition_definition' => 'array',
        'action_definition' => 'array',
        'published_at' => 'datetime',
    ];

    public function rule(): BelongsTo
    {
        return $this->belongsTo(Rule::class, 'rule_id');
    }
}
