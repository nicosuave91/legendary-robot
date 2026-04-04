\
<?php

declare(strict_types=1);

namespace App\Modules\Imports\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\Shared\Tenancy\TenantScoped;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $import_type
 * @property string $file_format
 * @property string $status
 * @property string|null $uploaded_by_user_id
 * @property string|null $validated_by_user_id
 * @property string|null $committed_by_user_id
 * @property string $original_filename
 * @property string|null $stored_filename
 * @property string|null $storage_disk
 * @property string|null $storage_path
 * @property string|null $storage_reference
 * @property string|null $mime_type
 * @property int $size_bytes
 * @property string|null $checksum_sha256
 * @property string|null $parser_version
 * @property int $row_count
 * @property int $valid_row_count
 * @property int $invalid_row_count
 * @property int $committed_row_count
 * @property array<string, mixed>|null $summary_snapshot
 * @property array<string, mixed>|null $failure_summary
 * @property string|null $last_correlation_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $validated_at
 * @property \Illuminate\Support\Carbon|null $committed_at
 */
final class Import extends Model
{
    use TenantScoped;

    protected $table = 'imports';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'tenant_id',
        'import_type',
        'file_format',
        'status',
        'uploaded_by_user_id',
        'validated_by_user_id',
        'committed_by_user_id',
        'original_filename',
        'stored_filename',
        'storage_disk',
        'storage_path',
        'storage_reference',
        'mime_type',
        'size_bytes',
        'checksum_sha256',
        'parser_version',
        'row_count',
        'valid_row_count',
        'invalid_row_count',
        'committed_row_count',
        'summary_snapshot',
        'failure_summary',
        'last_correlation_id',
        'validated_at',
        'committed_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'summary_snapshot' => 'array',
        'failure_summary' => 'array',
        'validated_at' => 'datetime',
        'committed_at' => 'datetime',
    ];

    public function rows(): HasMany
    {
        return $this->hasMany(ImportRow::class, 'import_id');
    }

    public function errors(): HasMany
    {
        return $this->hasMany(ImportError::class, 'import_id');
    }
}
