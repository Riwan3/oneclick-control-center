<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AddCorrelationId
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Use incoming request ID if present, otherwise generate a UUID
        $requestId = $request->header('X-Request-Id') ?: (string) Str::uuid();

        $request->headers->set('X-Request-Id', $requestId);

        $response = $next($request);

        // Add correlation ID header to the response
        $response->headers->set('X-Request-Id', $requestId);

        return $response;
    }
}
