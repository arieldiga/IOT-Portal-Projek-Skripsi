<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class VendorApiClient
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.vendor_api.base_url');
        $this->apiKey  = config('services.vendor_api.api_key');
    }

    public function fetchLatest(?string $since = null, ?int $userId = null): array
    {
        $response = Http::withHeaders(['X-API-KEY' => $this->apiKey])
            ->get("{$this->baseUrl}/api/v1/sensor-data/latest", array_filter([
                'since'   => $since,
                'user_id' => $userId,
            ]));

        if ($response->failed()) {
            throw new \RuntimeException('Gagal menghubungi Vendor API Gateway: ' . $response->status());
        }

        return $response->json('data') ?? [];
    }

    public function fetchHistorical(?int $userId = null, ?string $from = null, ?string $to = null, int $limit = 100): array
    {
        $response = Http::withHeaders(['X-API-KEY' => $this->apiKey])
            ->get("{$this->baseUrl}/api/v1/sensor-data", array_filter([
                'user_id' => $userId,
                'from'    => $from,
                'to'      => $to,
                'limit'   => $limit,
            ]));

        if ($response->failed()) {
            throw new \RuntimeException('Gagal menghubungi Vendor API Gateway: ' . $response->status());
        }

        return $response->json('data') ?? [];
    }
}