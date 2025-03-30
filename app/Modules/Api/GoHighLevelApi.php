<?php

namespace App\Modules\Api;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class GoHighLevelApi extends BaseApi
{
    protected string $cachePrefix = 'ghl';
    protected string $baseUrl = 'https://rest.gohighlevel.com/v1/';

    protected function getData(array $data, string $method): array|null
    {
        if ($method == 'get' && !$data) return  null;
        return $data;
    }
}
