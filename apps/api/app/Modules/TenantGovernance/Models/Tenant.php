<?php

declare(strict_types=1);

namespace App\Modules\TenantGovernance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class Tenant extends Model
{
    protected $table = 'tenants';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'name',
    ];

    public function themeSetting(): HasOne
    {
        return $this->hasOne(ThemeSetting::class, 'tenant_id');
    }
}
