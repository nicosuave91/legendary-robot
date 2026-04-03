<?php

declare(strict_types=1);

namespace App\Modules\Imports\Services;

use SplFileObject;

final class ImportCsvParser
{
    /**
     * @return array<int, array<string, string|null>>
     */
    public function parse(string $absolutePath): array
    {
        $file = new SplFileObject($absolutePath);
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);

        $headers = null;
        $rows = [];

        foreach ($file as $rawRow) {
            if (!is_array($rawRow)) {
                continue;
            }

            $cells = array_map(
                static fn ($value): ?string => $value === null ? null : trim((string) $value),
                $rawRow,
            );

            if ($headers === null) {
                $headers = array_values(array_filter(array_map(
                    static fn (?string $value): string => strtolower((string) $value),
                    $cells,
                ), static fn (string $value): bool => $value !== ''));

                continue;
            }

            if ($headers === []) {
                continue;
            }

            $hasContent = collect($cells)->contains(fn ($value): bool => $value !== null && $value !== '');
            if (!$hasContent) {
                continue;
            }

            $row = [];
            foreach ($headers as $index => $header) {
                $row[$header] = $cells[$index] ?? null;
            }

            $rows[] = $row;
        }

        return $rows;
    }
}
