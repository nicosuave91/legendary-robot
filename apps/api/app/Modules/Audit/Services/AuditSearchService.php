<?php

declare(strict_types=1);

namespace App\Modules\Audit\Services;

use App\Modules\Audit\Models\AuditLog;
use App\Modules\IdentityAccess\Models\User;

final class AuditSearchService
{
    public function listForUser(User $actor, array $filters = []): array
    {
        $query = AuditLog::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $actor->tenant_id)
            ->latest('created_at');

        foreach ([
            'action' => 'action',
            'subjectType' => 'subject_type',
            'subjectId' => 'subject_id',
            'actorId' => 'actor_id',
            'correlationId' => 'correlation_id',
        ] as $filterKey => $column) {
            if (($filters[$filterKey] ?? null) !== null) {
                $query->where($column, $filters[$filterKey]);
            }
        }

        if (($filters['from'] ?? null) !== null) {
            $query->where('created_at', '>=', $filters['from']);
        }

        if (($filters['to'] ?? null) !== null) {
            $query->where('created_at', '<=', $filters['to']);
        }

        if (($filters['q'] ?? null) !== null) {
            $needle = '%' . $filters['q'] . '%';
            $query->where(function ($builder) use ($needle): void {
                $builder
                    ->where('action', 'like', $needle)
                    ->orWhere('subject_type', 'like', $needle)
                    ->orWhere('subject_id', 'like', $needle)
                    ->orWhere('correlation_id', 'like', $needle);
            });
        }

        $items = $query->limit(100)->get();

        return [
            'items' => $items->map(fn (AuditLog $log): array => [
                'id' => (int) $log->id,
                'action' => (string) $log->action,
                'subjectType' => (string) $log->subject_type,
                'subjectId' => $log->subject_id,
                'actorId' => $log->actor_id,
                'actorDisplayName' => $log->actor_id,
                'correlationId' => $log->correlation_id,
                'beforeSummary' => $log->before_summary ?? [],
                'afterSummary' => $log->after_summary ?? [],
                'occurredAt' => $log->created_at?->toIso8601String(),
            ])->values()->all(),
            'meta' => [
                'total' => $items->count(),
                'page' => 1,
                'perPage' => 100,
            ],
        ];
    }
}
