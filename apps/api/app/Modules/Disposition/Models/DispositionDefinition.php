<?php

declare(strict_types=1);

namespace App\Modules\Disposition\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Shared\Tenancy\TenantScoped;

final class DispositionDefinition extends Model
{
    use TenantScoped;

    protected $table = 'disposition_definitions';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'tenant_id',
        'code',
        'label',
        'description',
        'sort_order',
        'is_initial',
        'is_terminal',
        'allowed_next_codes',
        'prerequisites',
        'role_permissions',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'allowed_next_codes' => 'array',
        'prerequisites' => 'array',
        'role_permissions' => 'array',
        'is_initial' => 'boolean',
        'is_terminal' => 'boolean',
        'is_active' => 'boolean',
    ];
}
