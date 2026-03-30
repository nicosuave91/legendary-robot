<?php

declare(strict_types=1);

namespace App\Modules\IdentityAccess\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

final class User extends Authenticatable
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'email',
        'name',
        'password',
    ];
}
