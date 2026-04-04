<?php

declare(strict_types=1);

namespace App\Modules\Audit\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Shared\Tenancy\TenantScoped;

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
