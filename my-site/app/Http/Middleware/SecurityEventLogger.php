<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SecurityEventLogger
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $method = strtoupper($request->method());

        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true) && $response->getStatusCode() < 400) {
            Log::channel('security')->info('data_operation', [
                'event' => 'data_operation',
                'method' => $method,
                'route' => $request->route()?->getName() ?? 'unnamed',
                'request_id' => $request->attributes->get('request_id'),
            ]);
        }



        return $response;
    }
}
