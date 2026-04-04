\
<?php

declare(strict_types=1);

namespace App\Modules\Applications\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Shared\Tenancy\TenantScoped;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $application_id
 * @property string|null $rule_id
 * @property string|null $rule_version_id
 * @property string $rule_key
 * @property string $rule_version
 * @property string|null $rule_name_snapshot
 * @property string $trigger_event
 * @property string $outcome
 * @property string $title
 * @property string $note_body
 * @property bool $is_blocking
 * @property array<string, mixed>|null $evidence
 * @property \Illuminate\Support\Carbon|null $applied_at
 */
final class ApplicationRuleApplication extends Model
{
    use TenantScoped;

    protected $table = 'application_rule_applications';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * @var list<string>
     */
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

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_blocking' => 'boolean',
        'evidence' => 'array',
        'applied_at' => 'datetime',
    ];
}
