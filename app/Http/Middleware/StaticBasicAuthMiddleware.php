<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StaticBasicAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Set static username and password
        $username = env('STATIC_API_BASIC_AUTH_USER');
        $password = env('STATIC_API_BASIC_AUTH_PASS');

        // Get Authorization header
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Basic ')) {
            return response('Unauthorized', 401, ['WWW-Authenticate' => 'Basic']);
        }

        // Decode Base64 credentials
        $encodedCredentials = substr($authHeader, 6);
        $decodedCredentials = base64_decode($encodedCredentials);
        [$inputUser, $inputPass] = explode(':', $decodedCredentials, 2) + [null, null];

        // Validate credentials
        if ($inputUser !== $username || $inputPass !== $password) {
            return response('Unauthorized', 401, ['WWW-Authenticate' => 'Basic']);
        }

        return $next($request);
    }
}
