<?php

namespace App\Services\Integrations;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ExternalIntegrationClient
{
    protected string $baseUrl;
    protected string $token;
    protected int $timeout;

    public function __construct()
    {
        // Load configuration from config/env helpers with fallback values
        $this->baseUrl = config('services.external_api.base_url', env('EXTERNAL_API_BASE_URL', 'https://api.external-service.local/v1'));
        $this->token = config('services.external_api.token', env('EXTERNAL_API_TOKEN', ''));
        $this->timeout = (int) config('services.external_api.timeout', env('EXTERNAL_API_TIMEOUT', 10));
    }

    /**
     * Executes an external request with full correlation ID propagation, timeouts, and logging.
     *
     * @param string $method GET, POST, etc.
     * @param string $endpoint Relative URL endpoint
     * @param array $payload Payload payload (if any)
     * @return array Normalized response payload
     * @throws \Exception
     */
    public function request(string $method, string $endpoint, array $payload = []): array
    {
        $correlationId = request()->header('X-Request-Id') ?: (string) Str::uuid();
        $startTime = microtime(true);

        Log::info("External Integration Request Initiating", [
            'correlation_id' => $correlationId,
            'endpoint' => $endpoint,
            'method' => $method,
        ]);

        try {
            // Setup Http client with token auth, headers, timeout, and retry handler
            $response = Http::withToken($this->token)
                ->withHeaders([
                    'X-Request-Id' => $correlationId,
                    'Accept' => 'application/json',
                ])
                ->timeout($this->timeout)
                ->retry(3, 100) // retry 3 times with 100ms delay between retries
                ->send($method, "{$this->baseUrl}/{$endpoint}", [
                    'json' => $payload,
                ]);

            $duration = round((microtime(true) - $startTime) * 1000, 2);

            Log::info("External Integration Response Received", [
                'correlation_id' => $correlationId,
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'duration_ms' => $duration,
                'success' => $response->successful(),
            ]);

            if ($response->failed()) {
                throw new \Exception("External API returned error code: {$response->status()}");
            }

            return $response->json() ?: [];

        } catch (\Throwable $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            // Log integration failure safely without dumping passwords or tokens
            Log::error("External Integration Request Failed", [
                'correlation_id' => $correlationId,
                'endpoint' => $endpoint,
                'duration_ms' => $duration,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception("Unable to communicate with external API integration. (Duration: {$duration}ms)");
        }
    }
}
