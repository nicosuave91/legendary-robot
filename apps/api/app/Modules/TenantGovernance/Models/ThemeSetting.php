<?php

declare(strict_types=1);

namespace App\Modules\TenantGovernance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Shared\Tenancy\TenantScoped;

final class ThemeSetting extends Model
{
    use TenantScoped;

    protected $table = 'theme_settings';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'primary_color',
        'secondary_color',
        'tertiary_color',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
