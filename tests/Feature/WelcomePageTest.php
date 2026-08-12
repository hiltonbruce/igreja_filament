<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WelcomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_page_renders_brand_and_modules_for_guests(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Eliú', false);
        $response->assertSee('Secretaria', false);
        $response->assertSee('Administração', false);
        $response->assertSee('Tesouraria', false);
        $response->assertSee('Acessar o sistema', false);
        $response->assertSee('Entrar', false);
        $response->assertDontSee('Sair', false);
        $response->assertSee(url('/secretary'), false);
        $response->assertSee(url('/admin'), false);
        $response->assertSee(route('login'), false);
    }

    public function test_welcome_page_shows_logout_when_authenticated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertOk();
        $response->assertSee('Sair', false);
        $response->assertSee('Sair da conta', false);
        $response->assertSee(route('logout'), false);
        $response->assertDontSee('Entrar', false);
        $response->assertDontSee('Acessar o sistema', false);
    }

    public function test_logout_route_logs_user_out_and_redirects_home(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect('/');
        $this->assertGuest();
    }
}
