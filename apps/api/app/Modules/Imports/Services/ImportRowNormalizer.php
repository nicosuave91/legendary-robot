<?php

declare(strict_types=1);

namespace App\Modules\Imports\Services;

final class ImportRowNormalizer
{
    /**
     * @param array<string, string|null> $row
     * @return array<string, mixed>
     */
    public function normalizeClientsRow(array $row): array
    {
        $firstName = $this->cleanString($row['first_name'] ?? $row['firstname'] ?? null);
        $lastName = $this->cleanString($row['last_name'] ?? $row['lastname'] ?? null);
        $companyName = $this->cleanString($row['company_name'] ?? $row['company'] ?? null);
        $displayName = $this->cleanString($row['display_name'] ?? null);

        if ($displayName === null) {
            $displayName = trim(implode(' ', array_filter([$firstName, $lastName])));
            $displayName = $displayName !== '' ? $displayName : ($companyName ?: null);
        }

        return [
            'displayName' => $displayName,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'companyName' => $companyName,
            'primaryEmail' => $this->cleanString($row['primary_email'] ?? $row['email'] ?? null),
            'primaryPhone' => $this->cleanString($row['primary_phone'] ?? $row['phone'] ?? null),
            'preferredContactChannel' => $this->normalizeContactChannel($row['preferred_contact_channel'] ?? $row['preferred_contact'] ?? null),
            'addressLine1' => $this->cleanString($row['address_line_1'] ?? $row['address1'] ?? null),
            'addressLine2' => $this->cleanString($row['address_line_2'] ?? $row['address2'] ?? null),
            'city' => $this->cleanString($row['city'] ?? null),
            'stateCode' => $this->cleanString($row['state_code'] ?? $row['state'] ?? null),
            'postalCode' => $this->cleanString($row['postal_code'] ?? $row['zip'] ?? null),
            'dateOfBirth' => $this->normalizeDate($row['date_of_birth'] ?? $row['dob'] ?? null),
        ];
    }

    private function cleanString(?string $value): ?string
    {
        $value = $value !== null ? trim($value) : null;

        return $value === '' ? null : $value;
    }

    private function normalizeDate(?string $value): ?string
    {
        $value = $this->cleanString($value);
        if ($value === null) {
            return null;
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return $value;
        }
    }

    private function normalizeContactChannel(?string $value): ?string
    {
        $value = strtolower((string) $this->cleanString($value));

        return match ($value) {
            'email', 'sms', 'phone' => $value,
            default => null,
        };
    }
}
