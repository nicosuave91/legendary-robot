\
<?php

declare(strict_types=1);

namespace App\Modules\Disposition\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Shared\Tenancy\TenantScoped;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $code
 * @property string $label
 * @property string|null $description
 * @property int|null $sort_order
 * @property bool $is_initial
 * @property bool $is_terminal
 * @property array<int, string> $allowed_next_codes
 * @property array<string, mixed>|null $prerequisites
 * @property array<string, mixed>|null $role_permissions
 * @property bool $is_active
 */
final class DispositionDefinition extends Model
{
    use TenantScoped;

    protected $table = 'disposition_definitions';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * @var list<string>
     */
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

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'allowed_next_codes' => 'array',
        'prerequisites' => 'array',
        'role_permissions' => 'array',
        'is_initial' => 'boolean',
        'is_terminal' => 'boolean',
        'is_active' => 'boolean',
    ];
}
