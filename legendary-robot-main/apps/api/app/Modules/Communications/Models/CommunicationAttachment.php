<?php

declare(strict_types=1);

namespace App\Modules\Communications\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Modules\Shared\Tenancy\TenantScoped;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string|null $client_id
 * @property string $attachable_type
 * @property string $attachable_id
 * @property string|null $source_channel
 * @property string|null $provenance
 * @property string|null $storage_disk
 * @property string|null $storage_path
 * @property string|null $storage_reference
 * @property string $original_filename
 * @property string $stored_filename
 * @property string $mime_type
 * @property int $size_bytes
 * @property string|null $checksum_sha256
 * @property string|null $scan_status
 * @property \Illuminate\Support\Carbon|null $scan_requested_at
 * @property \Illuminate\Support\Carbon|null $scanned_at
 * @property string|null $scan_engine
 * @property string|null $scan_result_detail
 * @property string|null $quarantine_reason
 * @property string|null $scan_updated_by
 * @property string|null $provider_attachment_id
 */
final class CommunicationAttachment extends Model
{
    use TenantScoped;

    protected $table = 'communication_attachments';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = ['id','tenant_id','client_id','attachable_type','attachable_id','source_channel','provenance','storage_disk','storage_path','storage_reference','original_filename','stored_filename','mime_type','size_bytes','checksum_sha256','scan_status','scan_requested_at','scanned_at','scan_engine','scan_result_detail','quarantine_reason','scan_updated_by','provider_attachment_id','uploaded_by'];

    /**
     * @var array<string, string>
     */
    protected $casts = ['size_bytes' => 'integer','scan_requested_at' => 'datetime','scanned_at' => 'datetime'];

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }
}
