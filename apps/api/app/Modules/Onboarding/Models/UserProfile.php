\
<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Shared\Tenancy\TenantScoped;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $user_id
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $phone
 * @property \Illuminate\Support\Carbon|null $birthday
 * @property string|null $address_line_1
 * @property string|null $address_line_2
 * @property string|null $city
 * @property string|null $state_code
 * @property string|null $postal_code
 * @property \Illuminate\Support\Carbon|null $profile_confirmed_at
 */
final class UserProfile extends Model
{
    use TenantScoped;

    protected $table = 'user_profiles';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'tenant_id',
        'user_id',
        'first_name',
        'last_name',
        'phone',
        'birthday',
        'address_line_1',
        'address_line_2',
        'city',
        'state_code',
        'postal_code',
        'profile_confirmed_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'birthday' => 'date',
        'profile_confirmed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
