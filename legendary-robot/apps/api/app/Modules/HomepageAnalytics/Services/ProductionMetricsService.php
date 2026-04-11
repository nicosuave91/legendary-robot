<?php

declare(strict_types=1);

namespace App\Modules\HomepageAnalytics\Services;

use Carbon\CarbonImmutable;
use App\Modules\Clients\Models\ClientDocument;
use App\Modules\Clients\Models\ClientNote;
use App\Modules\Clients\Services\ClientVisibilityService;
use App\Modules\IdentityAccess\Models\User;

final class ProductionMetricsService
{
    public function __construct(
        private readonly ClientVisibilityService $clientVisibilityService,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function forActor(User $actor, string $window): array
    {
        $days = match ($window) {
            '7d' => 7,
            '90d' => 90,
            default => 30,
        };

        $end = CarbonImmutable::now()->startOfDay();
        $start = $end->subDays($days - 1);

        $visibleClientIds = $this->clientVisibilityService
            ->queryForActor($actor)
            ->pluck('clients.id')
            ->all();

        $clientCounts = $this->bucketCounts(
            $this->clientVisibilityService
                ->queryForActor($actor)
                ->whereDate('clients.created_at', '>=', $start->toDateString())
                ->get(['clients.created_at'])
                ->pluck('created_at')
                ->all(),
            $start,
            $days,
        );

        $noteCounts = empty($visibleClientIds)
            ? $this->zeroBucketCounts($start, $days)
            : $this->bucketCounts(
                ClientNote::query()
                    ->whereIn('client_id', $visibleClientIds)
                    ->whereDate('created_at', '>=', $start->toDateString())
                    ->get(['created_at'])
                    ->pluck('created_at')
                    ->all(),
                $start,
                $days,
            );

        $documentCounts = empty($visibleClientIds)
            ? $this->zeroBucketCounts($start, $days)
            : $this->bucketCounts(
                ClientDocument::query()
                    ->whereIn('client_id', $visibleClientIds)
                    ->whereDate('created_at', '>=', $start->toDateString())
                    ->get(['created_at'])
                    ->pluck('created_at')
                    ->all(),
                $start,
                $days,
            );

        return [
            'range' => [
                'window' => $window,
                'startDate' => $start->toDateString(),
                'endDate' => $end->toDateString(),
                'granularity' => 'day',
            ],
            'series' => [
                [
                    'key' => 'clientsCreated',
                    'label' => 'Clients created',
                    'points' => $this->pointsFromBuckets($clientCounts),
                ],
                [
                    'key' => 'notesCreated',
                    'label' => 'Notes created',
                    'points' => $this->pointsFromBuckets($noteCounts),
                ],
                [
                    'key' => 'documentsUploaded',
                    'label' => 'Documents uploaded',
                    'points' => $this->pointsFromBuckets($documentCounts),
                ],
            ],
            'totals' => [
                'clientsCreated' => array_sum($clientCounts),
                'notesCreated' => array_sum($noteCounts),
                'documentsUploaded' => array_sum($documentCounts),
            ],
        ];
    }

    /**
     * @param array<int, mixed> $timestamps
     * @return array<string, int>
     */
    private function bucketCounts(array $timestamps, CarbonImmutable $start, int $days): array
    {
        $buckets = $this->zeroBucketCounts($start, $days);

        foreach ($timestamps as $timestamp) {
            if ($timestamp === null) {
                continue;
            }

            $bucketDate = CarbonImmutable::parse((string) $timestamp)->toDateString();
            if (array_key_exists($bucketDate, $buckets)) {
                $buckets[$bucketDate]++;
            }
        }

        return $buckets;
    }

    /**
     * @return array<string, int>
     */
    private function zeroBucketCounts(CarbonImmutable $start, int $days): array
    {
        $buckets = [];
        for ($offset = 0; $offset < $days; $offset++) {
            $bucketDate = $start->addDays($offset)->toDateString();
            $buckets[$bucketDate] = 0;
        }

        return $buckets;
    }

    /**
     * @param array<string, int> $buckets
     * @return array<int, array<string, int|string>>
     */
    private function pointsFromBuckets(array $buckets): array
    {
        $points = [];
        foreach ($buckets as $bucketDate => $value) {
            $points[] = [
                'bucketDate' => $bucketDate,
                'value' => $value,
            ];
        }

        return $points;
    }
}
