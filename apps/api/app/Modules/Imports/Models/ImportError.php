\
<?php

declare(strict_types=1);

namespace App\Modules\Imports\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Shared\Tenancy\TenantScoped;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $import_id
 * @property string|null $import_row_id
 * @property int|null $row_number
 * @property string|null $field_name
 * @property string $error_code
 * @property string $severity
 * @property string $message
 * @property array<string, mixed>|null $context_snapshot
 */
final class ImportError extends Model
{
    use TenantScoped;

    protected $table = 'import_errors';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'tenant_id',
        'import_id',
        'import_row_id',
        'row_number',
        'field_name',
        'error_code',
        'severity',
        'message',
        'context_snapshot',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'context_snapshot' => 'array',
    ];

    public function import(): BelongsTo
    {
        return $this->belongsTo(Import::class, 'import_id');
    }

    public function row(): BelongsTo
    {
        return $this->belongsTo(ImportRow::class, 'import_row_id');
    }
}
