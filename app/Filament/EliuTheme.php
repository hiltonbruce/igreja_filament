<?php

namespace App\Filament;

use Filament\Enums\ThemeMode;
use Filament\Panel;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;

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
            )
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_AFTER,
                fn (): string => view('filament.eliu.panel-switcher', [
                    'panels' => self::panelLinks(),
                ])->render(),
            );
    }

    /**
     * Links shown in every panel topbar so the user can move between panels.
     *
     * @return list<array{id: string, label: string, url: string, active: bool}>
     */
    public static function panelLinks(): array
    {
        $currentId = filament()->getCurrentPanel()?->getId();

        return collect(filament()->getPanels())
            ->sortBy(fn (Panel $panel): int => match ($panel->getId()) {
                'secretary' => 0,
                'admin' => 1,
                default => 2,
            })
            ->map(function (Panel $panel) use ($currentId): ?array {
                $url = $panel->getUrl();

                if (! is_string($url) || $url === '') {
                    return null;
                }

                return [
                    'id' => $panel->getId(),
                    'label' => self::panelLabel($panel),
                    'url' => $url,
                    'active' => $panel->getId() === $currentId,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private static function panelLabel(Panel $panel): string
    {
        return match ($panel->getId()) {
            'admin' => 'Administração',
            'secretary' => 'Secretaria',
            default => Str::headline($panel->getId()),
        };
    }
}
