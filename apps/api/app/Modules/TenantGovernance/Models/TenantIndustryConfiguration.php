<?php

declare(strict_types=1);

namespace App\Modules\TenantGovernance\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Shared\Tenancy\TenantScoped;

final class TenantIndustryConfiguration extends Model
{
    use TenantScoped;

    protected $table = 'tenant_industry_configs';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'tenant_id',
        'industry',
        'version',
        'status',
        'is_active',
        'capabilities',
        'notes',
        'created_by',
        'published_at',
        'activated_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'capabilities' => 'array',
        'published_at' => 'datetime',
        'activated_at' => 'datetime',
    ];
}
