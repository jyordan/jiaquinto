<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GoHighLevelController extends Controller
{
    public function __invoke(Request $request, $any)
    {
        // Validate or perform any authentication checks here as needed

        // Build the full API URL
        $apiUrl = 'https://rest.gohighlevel.com/v1/' . $any;

        // Get the HTTP method from the request
        $method = $request->method();

        $token = env('GO_HIGH_LEVEL_KEY');

        // Perform the HTTP request using Guzzle
        $response = Http::acceptJson()
            ->withToken($token)
            ->{$method}($apiUrl, $request->all());

        // Return the API response to the client
        return response($response->body(), $response->status())
            ->header('Content-Type', 'application/json');
    }
}
