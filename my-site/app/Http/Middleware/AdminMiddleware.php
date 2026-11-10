<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
<<<<<<< HEAD
=======
        // Если пользователь не вошёл или не админ — отдаём строгий 403 Forbidden
>>>>>>> c8bb101774e5a91fd1eb9bfa65342f30c67ce747
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Доступ запрещён. Требуются права администратора.');
        }

        return $next($request);
    }
}