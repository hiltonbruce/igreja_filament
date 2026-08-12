<?php

namespace Tests\Feature;

use Tests\TestCase;

class EliuFilamentThemeTest extends TestCase
{
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
}
