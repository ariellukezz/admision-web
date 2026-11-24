<?php

namespace Tests\Feature\Auth;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DnidSsoLoginTest extends TestCase
{
    public function test_login_redirect_uses_pkce_and_callback_can_redeem_the_code_server_side(): void
    {
        config([
            'dnid.auth_url' => 'https://dnid.test/service_dnid',
            'dnid.client_id' => 'admision',
            'dnid.callback_url' => 'http://admision.test/auth/dnid/callback',
        ]);

        $start = $this->get('/auth/dnid');
        $start->assertRedirect();

        $query = [];
        parse_str((string) parse_url($start->headers->get('Location'), PHP_URL_QUERY), $query);
        $this->assertSame('admision', $query['client_id']);
        $this->assertSame('http://admision.test/auth/dnid/callback', $query['redirect_uri']);
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9_-]{48}$/', $query['state']);
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9_-]{43}$/', $query['code_challenge']);

        $codeVerifier = session('dnid.oauth.verifier');
        $this->assertIsString($codeVerifier);
        $this->assertSame(
            rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '='),
            $query['code_challenge'],
        );

        Http::fake([
            'https://dnid.test/service_dnid/oauth/token' => Http::response([
                'detail' => 'invalid authorization code',
            ], 400),
        ]);

        $this->get('/auth/dnid/callback?'.http_build_query([
            'code' => str_repeat('a', 32),
            'state' => $query['state'],
        ]))
            ->assertRedirect('/login')
            ->assertSessionHas('status', 'No se pudo validar el acceso DNId. Inténtelo nuevamente.');

        Http::assertSent(fn ($request) => $request->url() ===
            'https://dnid.test/service_dnid/oauth/token'
            && $request->data()['client_id'] === 'admision'
            && $request->data()['redirect_uri'] === 'http://admision.test/auth/dnid/callback'
            && $request->data()['code_verifier'] === $codeVerifier);
    }
}
