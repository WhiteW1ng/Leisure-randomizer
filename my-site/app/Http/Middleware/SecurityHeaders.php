<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Добавляет к каждому ответу сервера заголовки безопасности.
 * Браузер читает их и сам включает защиты: запрет встраивания сайта в чужую страницу,
 * запрет чужих скриптов, запрет «угадывать» тип файла и т. д.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        // Одноразовый «пропуск» для наших собственных inline-скриптов (переключатель темы и т. п.).
        // Скрипт без этого пропуска браузер запустить не даст — так блокируется вставленный XSS-скрипт.
        // Vite добавит тот же пропуск к своим тегам. Сам пропуск (nonce) генерируем здесь,
        // чтобы он был одинаковым в заголовке и в шаблонах (config('app') не нужен).
        $nonce = base64_encode(random_bytes(16));
        $request->attributes->set('csp_nonce', $nonce);

        try {
            Vite::useCspNonce($nonce);
        } catch (\Throwable $e) {
            // в тестах Vite заменён заглушкой — это не страшно
        }

        $response = $next($request);

        $response->headers->set('Content-Security-Policy', $this->csp($nonce));
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');

        // HSTS говорит браузеру «ходи на этот сайт только по https». Отправляем только по защищённому
        // соединению: по http заголовок не имеет смысла, а на localhost мог бы навредить.
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        $response->headers->remove('X-Powered-By');

        return $response;
    }

    private function csp(string $nonce): string
    {
        // Во время разработки (npm run dev) Vite отдаёт скрипты со своего адреса — разрешаем его, но только тогда.
        $dev = '';
        $hot = public_path('hot');
        if (is_file($hot)) {
            $dev = ' '.trim(file_get_contents($hot));
            $ws = ' ws://127.0.0.1:5173 ws://localhost:5173 ws://[::1]:5173';
        }

        return implode('; ', [
            "default-src 'self'",
            // 'unsafe-eval' нужен библиотеке Alpine.js (меню, окно удаления аккаунта); план: перейти на сборку Alpine CSP
            "script-src 'self' 'nonce-{$nonce}' 'unsafe-eval'{$dev}",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com{$dev}",
            "font-src 'self' https://fonts.gstatic.com data:",
            "img-src 'self' data:",
            "connect-src 'self'{$dev}".($ws ?? ''),
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
        ]);
    }
}
