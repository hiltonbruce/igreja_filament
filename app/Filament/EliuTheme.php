<?php

namespace App\Filament;

use Filament\Enums\ThemeMode;
use Filament\Panel;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;

final class EliuTheme
{
    /**
     * Shared Eliú branding for every Filament panel.
     */
    public static function apply(Panel $panel): Panel
    {
        return $panel
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->font('Manrope')
            ->brandName('Eliú')
            ->defaultThemeMode(ThemeMode::Dark)
            ->colors([
                'primary' => Color::hex('#c9a227'),
                'gray' => Color::hex('#1a3d52'),
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => Blade::render('@include(\'filament.eliu.fonts\')'),
            )
            ->renderHook(
                PanelsRenderHook::BODY_START,
                fn (): string => Blade::render('@include(\'filament.eliu.atmosphere\')'),
            );
    }
}
