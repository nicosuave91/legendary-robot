<?php

declare(strict_types=1);

namespace App\Modules\Imports\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\Shared\Tenancy\TenantScoped;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $import_id
 * @property int $row_number
 * @property string $row_status
 * @property array<string, mixed>|null $raw_payload
 * @property array<string, mixed>|null $normalized_payload
 * @property string|null $target_subject_type
 * @property string|null $target_subject_id
 * @property array<string, mixed>|null $failure_summary
 * @property \Illuminate\Support\Carbon|null $validated_at
 * @property \Illuminate\Support\Carbon|null $committed_at
 */
final class ImportRow extends Model
{
    use TenantScoped;

    protected $table = 'import_rows';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * @var list<string>
     */
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

    /**
     * @var array<string, string>
     */
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
