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
 * @property string|null $uploaded_by_user_id
 * @property string|null $provenance
 * @property string|null $attachment_category
 * @property string $original_filename
 * @property string $stored_filename
 * @property string $storage_disk
 * @property string|null $storage_path
 * @property string|null $storage_reference
 * @property string $mime_type
 * @property int $size_bytes
 * @property string $checksum_sha256
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property-read User|null $uploadedBy
 */
final class ClientDocument extends Model
{
    use TenantScoped;

    protected $table = 'client_documents';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = ['id','tenant_id','client_id','uploaded_by_user_id','provenance','attachment_category','original_filename','stored_filename','storage_disk','storage_path','storage_reference','mime_type','size_bytes','checksum_sha256'];

    /**
     * @var array<string, string>
     */
    protected $casts = ['size_bytes' => 'integer'];

    public function client(): BelongsTo { return $this->belongsTo(Client::class, 'client_id'); }
    public function uploadedBy(): BelongsTo { return $this->belongsTo(User::class, 'uploaded_by_user_id'); }
}
