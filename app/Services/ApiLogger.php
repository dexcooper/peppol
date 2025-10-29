<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class ApiLogger
{
    /**
     * Log a request to an external API
     *
     * @param string $method
     * @param string $url
     * @param array|null $requestData
     * @param array|null $responseData
     * @param int|null $status
     * @param float|null $durationMs
     */
    public static function log(
        string $method,
        string $url,
        ?array $requestData = null,
        ?array $responseData = null,
        ?int $status = null,
        ?float $durationMs = null
    ) {
        $logData = [
            'method' => $method,
            'url' => $url,
            'status' => $status,
            'duration_ms' => $durationMs,
        ];

        if ($requestData) {
            $logData['request'] = self::maskSensitive($requestData);
        }

        if ($responseData) {
            $logData['response'] = self::maskSensitive($responseData);
        }

        Log::channel('api')->info('External API call', $logData);
    }

    public static function maskSensitive(array $data): array
    {
        $sensitiveKeys = ['password', 'token', 'api_key', 'authorization', 'iban'];

        foreach ($sensitiveKeys as $key) {
            if (isset($data[$key])) {
                $data[$key] = '***masked***';
            }
        }

        return $data;
    }

}
