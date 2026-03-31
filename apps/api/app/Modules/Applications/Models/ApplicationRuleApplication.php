<?php

declare(strict_types=1);

namespace App\Modules\Applications\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Shared\Tenancy\TenantScoped;

final class ApplicationRuleApplication extends Model
{
    use TenantScoped;

    protected $table = 'application_rule_applications';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'tenant_id',
        'application_id',
        'rule_id',
        'rule_version_id',
        'rule_key',
        'rule_version',
        'rule_name_snapshot',
        'trigger_event',
        'outcome',
        'title',
        'note_body',
        'is_blocking',
        'evidence',
        'applied_at',
    ];

    protected $casts = [
        'is_blocking' => 'boolean',
        'evidence' => 'array',
        'applied_at' => 'datetime',
    ];
}
