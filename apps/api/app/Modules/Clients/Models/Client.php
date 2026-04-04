<?php

declare(strict_types=1);

namespace App\Modules\Clients\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Modules\CalendarTasks\Models\CalendarEvent;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Shared\Tenancy\TenantScoped;
use App\Modules\TenantGovernance\Models\Tenant;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string|null $owner_user_id
 * @property string|null $created_by
 * @property string $display_name
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $company_name
 * @property string|null $primary_email
 * @property string|null $primary_phone
 * @property string|null $preferred_contact_channel
 * @property \Illuminate\Support\Carbon|null $date_of_birth
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $last_activity_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $notes_count
 * @property int|null $documents_count
 * @property-read ClientAddress|null $address
 * @property-read User|null $owner
 * @property-read User|null $creator
 */
final class Client extends Model
{
    use TenantScoped;

    protected $table = 'clients';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = ['id','tenant_id','owner_user_id','created_by','display_name','first_name','last_name','company_name','primary_email','primary_phone','preferred_contact_channel','date_of_birth','status','last_activity_at'];

    /**
     * @var array<string, string>
     */
    protected $casts = ['date_of_birth' => 'date','last_activity_at' => 'datetime'];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class, 'tenant_id'); }
    public function owner(): BelongsTo { return $this->belongsTo(User::class, 'owner_user_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function address(): HasOne { return $this->hasOne(ClientAddress::class, 'client_id'); }
    public function notes(): HasMany { return $this->hasMany(ClientNote::class, 'client_id'); }
    public function documents(): HasMany { return $this->hasMany(ClientDocument::class, 'client_id'); }
    public function statusHistory(): HasMany { return $this->hasMany(ClientStatusHistory::class, 'client_id'); }
    public function events(): HasMany { return $this->hasMany(CalendarEvent::class, 'client_id'); }
}
