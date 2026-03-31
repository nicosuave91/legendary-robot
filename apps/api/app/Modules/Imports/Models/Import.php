<?php

declare(strict_types=1);

namespace App\Modules\Imports\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\Shared\Tenancy\TenantScoped;

final class Import extends Model
{
    use TenantScoped;

    protected $table = 'imports';
    public $incrementing = false;
    protected $keyType = 'string';

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
