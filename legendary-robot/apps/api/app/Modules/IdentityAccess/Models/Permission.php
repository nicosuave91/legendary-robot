<?php

declare(strict_types=1);

namespace App\Modules\IdentityAccess\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $name
 * @property string $display_name
 */
final class Permission extends Model
{
    protected $table = 'permissions';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'name',
        'display_name',
    ];
}
