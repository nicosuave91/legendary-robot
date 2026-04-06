<?php

declare(strict_types=1);

namespace App\Modules\Communications\Services;

final class PhoneNumberNormalizer
{
    public function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        if (str_starts_with(strtolower($trimmed), 'whatsapp:')) {
            $trimmed = substr($trimmed, strlen('whatsapp:'));
        }

        $hasPlusPrefix = str_starts_with($trimmed, '+');
        $digits = preg_replace('/\D+/', '', $trimmed) ?? '';

        if ($digits === '') {
            return null;
        }

        if ($hasPlusPrefix) {
            return '+' . $digits;
        }

        if (strlen($digits) == 10) {
            return '+1' . $digits;
        }

        if (strlen($digits) == 11 && str_starts_with($digits, '1')) {
            return '+' . $digits;
        }

        return '+' . $digits;
    }

    public function same(?string $left, ?string $right): bool
    {
        $normalizedLeft = $this->normalize($left);
        $normalizedRight = $this->normalize($right);

        return $normalizedLeft !== null && $normalizedLeft === $normalizedRight;
    }
}
