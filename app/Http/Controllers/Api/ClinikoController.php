<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ClinikoController extends Controller
{
    public function __invoke(Request $request, $any)
    {
        // Validate or perform any authentication checks here as needed

        // Build the full API URL
        $apiUrl = 'https://api.au1.cliniko.com/v1/' . $any;

        // Get the HTTP method from the request
        $method = $request->method();

        $user = env('CLINIKO_USER');

        // Perform the HTTP request using Guzzle
        $response = Http::acceptJson()
            ->withBasicAuth($user, '')
            ->{$method}($apiUrl, $request->all());

        // Return the API response to the client
        return response($response->body(), $response->status())
            ->header('Content-Type', 'application/json');
    }
}
