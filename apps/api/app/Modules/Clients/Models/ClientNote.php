<?php

declare(strict_types=1);

namespace App\Modules\Clients\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Shared\Tenancy\TenantScoped;

final class ClientNote extends Model
{
    use TenantScoped;

    protected $table = 'client_notes';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id','tenant_id','client_id','author_user_id','source_type','body','is_editable'];
    protected $casts = ['is_editable' => 'boolean'];

    public function client(): BelongsTo { return $this->belongsTo(Client::class, 'client_id'); }
    public function author(): BelongsTo { return $this->belongsTo(User::class, 'author_user_id'); }
}
