<?php

declare(strict_types=1);

namespace App\Modules\Communications\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Modules\Shared\Tenancy\TenantScoped;

final class CommunicationAttachment extends Model
{
    use TenantScoped;

    protected $table = 'communication_attachments';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id', 'tenant_id', 'client_id', 'attachable_type', 'attachable_id', 'source_channel', 'provenance', 'storage_disk', 'storage_path', 'storage_reference', 'original_filename', 'stored_filename', 'mime_type', 'size_bytes', 'checksum_sha256', 'scan_status', 'provider_attachment_id', 'uploaded_by'];
    protected $casts = ['size_bytes' => 'integer'];

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }
}
