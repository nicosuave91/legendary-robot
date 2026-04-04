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
 * @property string $rule_version_id
 * @property string $subject_type
 * @property string $subject_id
 * @property string $trigger_event
 * @property string $execution_source
 * @property string $outcome
 * @property string|null $correlation_id
 * @property string|null $actor_user_id
 * @property array<string, mixed>|null $context_snapshot
 * @property array<string, mixed>|null $outcome_summary
 * @property \Illuminate\Support\Carbon|null $executed_at
 * @property-read Rule $rule
 * @property-read RuleVersion $ruleVersion
 */
final class RuleExecutionLog extends Model
{
    use TenantScoped;

    protected $table = 'rule_execution_logs';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'tenant_id',
        'rule_id',
        'rule_version_id',
        'subject_type',
        'subject_id',
        'trigger_event',
        'execution_source',
        'outcome',
        'correlation_id',
        'actor_user_id',
        'context_snapshot',
        'outcome_summary',
        'executed_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'context_snapshot' => 'array',
        'outcome_summary' => 'array',
        'executed_at' => 'datetime',
    ];

    public function rule(): BelongsTo
    {
        return $this->belongsTo(Rule::class, 'rule_id');
    }

    public function ruleVersion(): BelongsTo
    {
        return $this->belongsTo(RuleVersion::class, 'rule_version_id');
    }
}
