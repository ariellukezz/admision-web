<?php

namespace Tests\Feature\Middleware;

use App\Http\Middleware\Revisor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class RevisorMiddlewareTest extends TestCase
{
    private Revisor $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new Revisor();
    }

    public function test_unauthenticated_user_redirects_to_login(): void
    {
        // Ensure no user is authenticated
        \Mockery::close();
        $this->refreshApplication();

        $request = Request::create('/revisor');
        $response = $this->middleware->handle($request, fn () => response('OK'));

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringEndsWith('/login', $response->getTargetUrl());
    }

    public function test_admin_user_passes_middleware(): void
    {
        $admin = \Mockery::mock(User::class)->makePartial();
        $admin->shouldReceive('isAdmin')->andReturn(true);
        $admin->id_rol = 1;

        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('user')->andReturn($admin);

        $request = Request::create('/revisor');
        $response = $this->middleware->handle($request, fn () => response('OK'));

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_revisor_with_id_rol_2_passes_compatibility(): void
    {
        $revisor = \Mockery::mock(User::class)->makePartial();
        $revisor->shouldReceive('isAdmin')->andReturn(false);
        $revisor->shouldReceive('hasPermission')->andReturn(false);
        $revisor->id_rol = 2;

        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('user')->andReturn($revisor);

        $request = Request::create('/revisor');
        $response = $this->middleware->handle($request, fn () => response('OK'));

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_revisor_with_rbac_permission_passes(): void
    {
        $revisor = \Mockery::mock(User::class)->makePartial();
        $revisor->shouldReceive('isAdmin')->andReturn(false);
        $revisor->shouldReceive('hasPermission')->with('revisor.access')->andReturn(true);
        $revisor->id_rol = 2;

        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('user')->andReturn($revisor);

        $request = Request::create('/revisor');
        $response = $this->middleware->handle($request, fn () => response('OK'));

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_user_without_permissions_gets_403(): void
    {
        $user = \Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('isAdmin')->andReturn(false);
        $user->shouldReceive('hasPermission')->andReturn(false);
        $user->id_rol = 3;

        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('user')->andReturn($user);

        $request = Request::create('/revisor');

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->middleware->handle($request, fn () => response('OK'));
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}
