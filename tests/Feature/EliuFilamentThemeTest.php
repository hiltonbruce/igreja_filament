<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EliuFilamentThemeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_uses_eliu_branding(): void
    {
        $response = $this->get('/admin/login');

        $response->assertOk();
        $response->assertSee('Eliú', false);
        $response->assertSee('Manrope', false);
        $response->assertSee('fraunces', false);
        $response->assertSee('build/assets/theme-', false);
        $response->assertSee('eliu-atmosphere', false);
        $response->assertSee('eliu-atmosphere__grid', false);
        $response->assertSee('eliu-arch-light', false);
        $response->assertSee('eliu-arch-filament', false);
    }

    public function test_secretary_login_uses_eliu_branding(): void
    {
        $response = $this->get('/secretary/login');

        $response->assertOk();
        $response->assertSee('Eliú', false);
        $response->assertSee('Manrope', false);
        $response->assertSee('fraunces', false);
        $response->assertSee('build/assets/theme-', false);
        $response->assertSee('eliu-atmosphere', false);
        $response->assertSee('eliu-atmosphere__grid', false);
        $response->assertSee('eliu-arch-light', false);
        $response->assertSee('eliu-arch-filament', false);
    }

    public function test_authenticated_topbar_links_to_every_panel(): void
    {
        $user = User::factory()->create();

        foreach (['/admin', '/secretary'] as $path) {
            $response = $this->actingAs($user)->get($path);

            $response->assertOk();
            $response->assertSee('eliu-panel-switcher', false);
            $response->assertSee('Secretaria', false);
            $response->assertSee('Administração', false);
            $response->assertSee(url('/secretary'), false);
            $response->assertSee(url('/admin'), false);
        }
    }
}
