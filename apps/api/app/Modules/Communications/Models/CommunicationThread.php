<?php

declare(strict_types=1);

namespace App\Modules\Communications\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\Clients\Models\Client;
use App\Modules\Shared\Tenancy\TenantScoped;

final class CommunicationThread extends Model
{
    use TenantScoped;

    protected $table = 'communication_threads';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id', 'tenant_id', 'client_id', 'channel', 'participant_key', 'subject_hint', 'created_by', 'last_activity_at'];
    protected $casts = ['last_activity_at' => 'datetime'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(CommunicationMessage::class, 'communication_thread_id');
    }
}
