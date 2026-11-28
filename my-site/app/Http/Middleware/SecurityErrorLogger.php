<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SecurityErrorLogger
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        if ($response->getStatusCode() >= 500 && !$request->attributes->get('server_error_logged', false)) {
            Log::channel('security')->error('server_error', [
                'event' => 'server_error',
                'status' => $response->getStatusCode(),
                'method' => strtoupper($request->method()),
                'route' => $request->route()?->getName() ?? 'unnamed',
                'request_id' => $request->attributes->get('request_id'),
            ]);
        }
        return $response;
    }
}
