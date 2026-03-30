<?php

declare(strict_types=1);

namespace App\Modules\Clients\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Shared\Tenancy\TenantScoped;

final class ClientDocument extends Model
{
    use TenantScoped;

    protected $table = 'client_documents';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id','tenant_id','client_id','uploaded_by_user_id','provenance','attachment_category','original_filename','stored_filename','storage_disk','storage_path','storage_reference','mime_type','size_bytes','checksum_sha256'];
    protected $casts = ['size_bytes' => 'integer'];

    public function client(): BelongsTo { return $this->belongsTo(Client::class, 'client_id'); }
    public function uploadedBy(): BelongsTo { return $this->belongsTo(User::class, 'uploaded_by_user_id'); }
}
