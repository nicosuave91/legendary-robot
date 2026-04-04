\
<?php

declare(strict_types=1);

namespace App\Modules\TenantGovernance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Shared\Tenancy\TenantScoped;

/**
 * @property string $tenant_id
 * @property string $primary_color
 * @property string $secondary_color
 * @property string $tertiary_color
 */
final class ThemeSetting extends Model
{
    use TenantScoped;

    protected $table = 'theme_settings';

    /**
     * @var list<string>
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
