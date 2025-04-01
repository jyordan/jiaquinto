<?php

namespace App\Modules\Api;

use Illuminate\Http\Client\PendingRequest;

class ClinikoApi extends BaseApi
{
    protected string $cachePrefix = 'cliniko';
    protected string $baseUrl = 'https://api.au1.cliniko.com/v1/';

    protected function setAuth(PendingRequest $pendingRequest): PendingRequest
    {
        // Perform the HTTP request
        return $pendingRequest->withBasicAuth($this->getToken(), '');
    }

    protected function getData(array $data, string $method): array|null
    {
        if ($method == 'get' && !$data) return  null;
        return $data;
    }

    protected function getBaseUrl()
    {
        $segments = explode('-', $this->token);
        $region = last($segments) ?: 'au1';
        $baseUrl = 'https://api.au1.cliniko.com/v1/';
        return str_replace('au1', $region, $baseUrl);
    }
}
