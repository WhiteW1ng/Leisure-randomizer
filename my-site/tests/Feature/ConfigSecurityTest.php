<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Пара 7: заголовки безопасности, куки сессий, CSRF, секреты.
 */
class ConfigSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    // ---------- заголовки ответа ----------

    public function test_security_headers_are_sent_on_every_page(): void
    {
        foreach (['/', '/catalog', '/login', '/register'] as $url) {
            $this->get($url)
                ->assertHeader('X-Content-Type-Options', 'nosniff')
                ->assertHeader('X-Frame-Options', 'DENY')
                ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
                ->assertHeader('Permissions-Policy');
        }
    }

    public function test_csp_forbids_framing_plugins_and_foreign_sources(): void
    {
        $csp = $this->get('/')->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("frame-ancestors 'none'", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertStringContainsString("base-uri 'self'", $csp);
        $this->assertStringContainsString("form-action 'self'", $csp);
    }

    public function test_inline_scripts_carry_the_nonce_from_the_csp_header(): void
    {
        $response = $this->get('/');
        $this->assertSame(1, preg_match("/'nonce-([^']+)'/", $response->headers->get('Content-Security-Policy'), $m));

        $response->assertSee('nonce="'.$m[1].'"', false);
    }

    public function test_hsts_only_over_https(): void
    {
        $this->get('http://localhost/')->assertHeaderMissing('Strict-Transport-Security');

        $this->withServerVariables(['HTTPS' => 'on'])->get('https://localhost/')
            ->assertHeader('Strict-Transport-Security');
    }

    // ---------- куки ----------

    public function test_session_cookie_is_httponly_and_samesite(): void
    {
        $response = $this->get('/login');

        $cookie = collect($response->headers->getCookies())
            ->first(fn ($c) => $c->getName() === config('session.cookie'));

        $this->assertNotNull($cookie, 'cookie сессии не выдана');
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertContains(strtolower((string) $cookie->getSameSite()), ['lax', 'strict']);
    }

    public function test_session_config_is_safe(): void
    {
        $this->assertTrue(config('session.http_only'));
        $this->assertContains(config('session.same_site'), ['lax', 'strict']);
    }

    // ---------- CSRF ----------

    public function test_every_state_changing_form_has_csrf_token(): void
    {
        $checked = 0;

        foreach (File::allFiles(resource_path('views')) as $file) {
            $text = $file->getContents();
            preg_match_all('/<form\b[^>]*>/i', $text, $forms);

            $changing = collect($forms[0])->filter(fn ($tag) => ! preg_match('/method\s*=\s*"GET"/i', $tag))->count();
            if ($changing === 0) {
                continue;
            }

            $this->assertGreaterThanOrEqual(
                $changing,
                substr_count($text, '@csrf'),
                'В файле '.$file->getRelativePathname().' есть форма без @csrf'
            );
            $checked += $changing;
        }

        $this->assertGreaterThan(10, $checked);
    }

    public function test_csrf_protection_is_not_switched_off(): void
    {
        $bootstrap = File::get(base_path('bootstrap/app.php'));

        $this->assertStringNotContainsString('validateCsrfTokens', $bootstrap);
        $this->assertStringNotContainsString('preventRequestForgery', $bootstrap);
        $this->assertStringNotContainsString('VerifyCsrfToken', $bootstrap);
    }

    // ---------- страницы не содержат inline-обработчиков (их блокирует CSP) ----------

    public function test_views_have_no_inline_event_handlers(): void
    {
        foreach (File::allFiles(resource_path('views')) as $file) {
            $this->assertDoesNotMatchRegularExpression(
                '/\s(onclick|onsubmit|onchange|onmouseover|onmouseout|onload|onerror)\s*=/i',
                $file->getContents(),
                'Inline-обработчик в '.$file->getRelativePathname()
            );
        }
    }

    // ---------- секреты ----------

    public function test_env_example_has_no_real_secrets(): void
    {
        $example = File::get(base_path('.env.example'));

        $this->assertMatchesRegularExpression('/^APP_KEY=\s*$/m', $example);
        $this->assertDoesNotMatchRegularExpression('/base64:[A-Za-z0-9+\/=]{20,}/', $example);
        $this->assertMatchesRegularExpression('/^AWS_SECRET_ACCESS_KEY=\s*$/m', $example);
    }

    public function test_env_file_is_in_gitignore(): void
    {
        $this->assertMatchesRegularExpression('/^\.env$/m', File::get(base_path('.gitignore')));
    }
}
