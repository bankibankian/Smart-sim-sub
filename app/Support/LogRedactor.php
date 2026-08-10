<?php

namespace App\Support;

/**
 * Recursively masks sensitive keys before a vendor request/response payload
 * is written to the log, per the app's outbound-API logging standard.
 */
class LogRedactor
{
    private const SENSITIVE_KEYS = [
        'password', 'pin', 'token', 'api_key', 'apikey', 'secret', 'authorization',
        'bvn', 'nin', 'licensenumber', 'cvv', 'signature',
        'bankaccno', 'payeebankaccno', 'account_no', 'accountno',
    ];

    public static function redact(array $payload): array
    {
        $result = [];

        foreach ($payload as $key => $value) {
            $result[$key] = is_array($value)
                ? self::redact($value)
                : (in_array(strtolower((string) $key), self::SENSITIVE_KEYS, true) ? '[REDACTED]' : $value);
        }

        return $result;
    }
}
