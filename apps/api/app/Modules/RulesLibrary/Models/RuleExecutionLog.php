<?php

declare(strict_types=1);

namespace App\Modules\RulesLibrary\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Shared\Tenancy\TenantScoped;

final class RuleExecutionLog extends Model
{
    use TenantScoped;

    protected $table = 'rule_execution_logs';
    public $incrementing = false;
    protected $keyType = 'string';

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