<?php

declare(strict_types=1);

namespace App\Modules\Imports\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\Shared\Tenancy\TenantScoped;

final class ImportRow extends Model
{
    use TenantScoped;

    protected $table = 'import_rows';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'tenant_id',
        'import_id',
        'row_number',
        'row_status',
        'raw_payload',
        'normalized_payload',
        'target_subject_type',
        'target_subject_id',
        'failure_summary',
        'validated_at',
        'committed_at',
    ];

    protected $casts = [
        'raw_payload' => 'array',
        'normalized_payload' => 'array',
        'failure_summary' => 'array',
        'validated_at' => 'datetime',
        'committed_at' => 'datetime',
    ];

    public function import(): BelongsTo
    {
        return $this->belongsTo(Import::class, 'import_id');
    }

    public function errors(): HasMany
    {
        return $this->hasMany(ImportError::class, 'import_row_id');
    }
}
