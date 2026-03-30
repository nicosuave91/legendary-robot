<?php

declare(strict_types=1);

namespace App\Modules\Clients\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Shared\Tenancy\TenantScoped;

final class ClientAddress extends Model
{
    use TenantScoped;

    protected $table = 'client_addresses';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id','tenant_id','client_id','address_line_1','address_line_2','city','state_code','postal_code'];

    public function client(): BelongsTo { return $this->belongsTo(Client::class, 'client_id'); }
}
