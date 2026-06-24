<?php

namespace App\Services\Security;

class SensitiveDataMasker
{
    /**
     * @param mixed $value
     * @return mixed
     */
    public function mask(mixed $value): mixed
    {
        if (! config('security.masking.enabled', true)) {
            return $value;
        }

        if (is_array($value)) {
            return $this->maskArray($value);
        }

        return $value;
    }

    /**
     * @param array<string|int, mixed> $payload
     * @return array<string|int, mixed>
     */
    public function maskArray(array $payload): array
    {
        $masked = [];
        $sensitiveKeys = array_map('strtolower', config('security.masking.keys', []));

        foreach ($payload as $key => $value) {
            $keyString = strtolower((string) $key);

            if ($this->isSensitiveKey($keyString, $sensitiveKeys)) {
                $masked[$key] = $this->maskScalar($value);
                continue;
            }

            $masked[$key] = is_array($value) ? $this->maskArray($value) : $this->maskPossibleSensitiveScalar($value);
        }

        return $masked;
    }

    /** @param array<int, string> $sensitiveKeys */
    private function isSensitiveKey(string $key, array $sensitiveKeys): bool
    {
        foreach ($sensitiveKeys as $sensitiveKey) {
            if ($sensitiveKey !== '' && str_contains($key, $sensitiveKey)) {
                return true;
            }
        }

        return false;
    }

    private function maskScalar(mixed $value): string
    {
        if (is_scalar($value)) {
            $string = (string) $value;
            $last = substr($string, -4);

            return $last === '' ? '[masked]' : '[masked:' . $last . ']';
        }

        return '[masked]';
    }

    private function maskPossibleSensitiveScalar(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            [$local, $domain] = explode('@', $value, 2);
            return substr($local, 0, 1) . '***@' . $domain;
        }

        return $value;
    }
}
