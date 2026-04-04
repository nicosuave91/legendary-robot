\
<?php

declare(strict_types=1);

namespace App\Modules\Audit\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Shared\Tenancy\TenantScoped;

/**
 * @property string|int $id
 * @property string $tenant_id
 * @property string|null $actor_id
 * @property string $action
 * @property string $subject_type
 * @property string $subject_id
 * @property string|null $correlation_id
 * @property array<string, mixed>|null $before_summary
 * @property array<string, mixed>|null $after_summary
 * @property \Illuminate\Support\Carbon|null $created_at
 */
final class AuditLog extends Model
{
    use TenantScoped;

    protected $table = 'audit_logs';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'actor_id',
        'action',
        'subject_type',
        'subject_id',
        'correlation_id',
        'before_summary',
        'after_summary',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'before_summary' => 'array',
        'after_summary' => 'array',
    ];
}
