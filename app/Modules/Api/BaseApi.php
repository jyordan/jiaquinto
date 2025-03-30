<?php

namespace App\Modules\Api;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

abstract class BaseApi
{
    protected $token;
    protected string $baseUrl;
    protected string $cachePrefix;

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function request(string $any, array $request = [], string $method = 'get'): array
    {
        // Build the full API URL
        $apiUrl = $this->getUrl($any);
        $method = strtolower($method);

        $data = $this->getData($request, $method);

        // Cache only if method is GET
        if ($method === 'get') {
            // Generate a unique cache key
            $cacheKey = $this->getCacheKey([$any, $request, $method]);

            return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($apiUrl, $method, $data) {
                return $this->makeRequest($apiUrl, $method, $data);
            });
        }

        // Direct request if not GET
        return $this->makeRequest($apiUrl, $method, $data);
    }

    protected function makeRequest(string $apiUrl, string $method, mixed $data): array
    {
        $pendingRequest = $this->setAuth(Http::acceptJson());

        try {
            // Perform the HTTP request
            $response = $pendingRequest->{$method}($apiUrl, $data);
            return json_decode($response->body(), true) ?: [];
        } catch (\Throwable $th) {
            return [];
        }
    }

    protected function setAuth(PendingRequest $pendingRequest): PendingRequest
    {
        // Perform the HTTP request
        return $pendingRequest->withToken($this->getToken());
    }

    protected function getCacheKey(array $list): string
    {
        return $this->cachePrefix . '_' . md5(json_encode($list));
    }

    protected function getToken(): string
    {
        return $this->token;
    }

    protected function getUrl(string $any): string
    {
        return $this->baseUrl . $any;
    }

    protected function getData(array $data, string $method): array|null
    {
        return $data;
    }
}
