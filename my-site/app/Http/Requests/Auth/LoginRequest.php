<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:128'],
        ];
    }

    private function safeHash(string $value): string
    {
        return hash_hmac('sha256', Str::lower(trim($value)), (string) config('app.key'));
    }

    private function keys(): array
    {
        $account = $this->safeHash((string) $this->input('email', ''));
        $ip = $this->safeHash((string) $this->ip());
        return [
            'account' => 'login:account:'.$account,
            'ip' => 'login:ip:'.$ip,
            'combo' => 'login:combo:'.$account.':'.$ip,
        ];
    }

    private function requestId(): string
    {
        return (string) $this->attributes->get('request_id', 'unknown');
    }

    private function logEvent(string $event, string $level = 'warning', array $extra = []): void
    {
        Log::channel('security')->{$level}($event, array_merge(['event' => $event, 'request_id' => $this->requestId()], $extra));
    }

    public function authenticate(): void
    {
        $keys = $this->keys();
        if (RateLimiter::tooManyAttempts($keys['account'], 10) || RateLimiter::tooManyAttempts($keys['ip'], 20)) {
            $this->logEvent('login_throttled');
            throw ValidationException::withMessages(['email' => trans('auth.failed')]);
        }

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($keys['account'], 300);
            RateLimiter::hit($keys['ip'], 300);
            $comboCount = RateLimiter::attempts($keys['combo']) + 1;
            RateLimiter::hit($keys['combo'], 300);
            $this->logEvent('login_fail');
            if ($comboCount === 10) {
                $this->logEvent('brute_force', 'alert', ['severity' => 'high']);
            }
            throw ValidationException::withMessages(['email' => trans('auth.failed')]);
        }

        RateLimiter::clear($keys['account']);
        RateLimiter::clear($keys['ip']);
        RateLimiter::clear($keys['combo']);
        $this->logEvent('login_ok', 'info');
    }
}
