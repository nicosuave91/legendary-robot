<?php

declare(strict_types=1);

namespace App\Modules\Clients\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Shared\Tenancy\TenantScoped;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $client_id
 * @property string|null $author_user_id
 * @property string $source_type
 * @property string $body
 * @property bool $is_editable
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property-read User|null $author
 */
final class ClientNote extends Model
{
    use TenantScoped;

    protected $table = 'client_notes';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = ['id','tenant_id','client_id','author_user_id','source_type','body','is_editable'];

    /**
     * @var array<string, string>
     */
    protected $casts = ['is_editable' => 'boolean'];

    public function client(): BelongsTo { return $this->belongsTo(Client::class, 'client_id'); }
    public function author(): BelongsTo { return $this->belongsTo(User::class, 'author_user_id'); }
}
