<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
        'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);

        // Заголовки безопасности (CSP, X-Frame-Options, HSTS и др.) на каждый ответ
        $middleware->append(\App\Http\Middleware\RequestId::class);
        $middleware->append(\App\Http\Middleware\SecurityEventLogger::class);
        $middleware->append(\App\Http\Middleware\SecurityErrorLogger::class);
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->report(function (\Throwable $e): bool {
            $request = app('request');
            $traceFrames = array_map(static fn ($frame) => [
                'file' => $frame['file'] ?? null,
                'line' => $frame['line'] ?? null,
                'class' => $frame['class'] ?? null,
                'function' => $frame['function'] ?? null,
            ], array_slice($e->getTrace(), 0, 20));
            \Illuminate\Support\Facades\Log::channel('security')->error('exception', [
                'event' => 'exception',
                'exception' => get_class($e),
                'trace_frames' => $traceFrames,
                'request_id' => $request->attributes->get('request_id', 'unknown'),
            ]);
            return false;
        });

        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return null;
            }
            $status = $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface
                ? $e->getStatusCode() : 500;
            if ($status === 403) {
                \Illuminate\Support\Facades\Log::channel('security')->warning('access_denied', [
                    'event' => 'access_denied',
                    'method' => strtoupper($request->method()),
                    'route' => $request->route()?->getName() ?? 'unnamed',
                    'request_id' => $request->attributes->get('request_id', 'unknown'),
                ]);
            }
            if (in_array($status, [403, 404], true)) {
                return response()->view('errors.'.$status, [
                    'requestId' => $request->attributes->get('request_id', 'unknown'),
                ], $status);
            }
            if ($status >= 500) {
                $request->attributes->set('server_error_logged', true);
                \Illuminate\Support\Facades\Log::channel('security')->error('server_error', [
                    'event' => 'server_error', 'status' => 500,
                    'method' => strtoupper($request->method()),
                    'route' => $request->route()?->getName() ?? 'unnamed',
                    'request_id' => $request->attributes->get('request_id', 'unknown'),
                ]);
                return response()->view('errors.500', [
                    'requestId' => $request->attributes->get('request_id', 'unknown'),
                ], 500);
            }
            return null;
        });
    })->create();