<?php

declare(strict_types=1);

namespace App\Modules\RulesLibrary\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Shared\Tenancy\TenantScoped;

final class RuleVersion extends Model
{
    use TenantScoped;

    protected $table = 'rule_versions';
    public $incrementing = false;
    protected $keyType = 'string';

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