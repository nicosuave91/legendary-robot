<?php

declare(strict_types=1);

namespace App\Modules\RulesLibrary\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\Shared\Tenancy\TenantScoped;

final class Rule extends Model
{
    use TenantScoped;

    protected $table = 'rules';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'tenant_id',
        'rule_key',
        'name',
        'description',
        'module_scope',
        'subject_type',
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
        return $this->belongsTo(RuleVersion::class, 'latest_published_version_id');
    }

    public function currentDraftVersion(): BelongsTo
    {
        return $this->belongsTo(RuleVersion::class, 'current_draft_version_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(RuleVersion::class, 'rule_id');
    }
}