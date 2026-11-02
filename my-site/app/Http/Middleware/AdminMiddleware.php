<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Если пользователь не вошёл или не админ — отдаём строгий 403 Forbidden
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Доступ запрещён. Требуются права администратора.');
        }

        return $next($request);
    }
}