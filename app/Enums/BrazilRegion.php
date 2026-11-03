<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum BrazilRegion: string implements HasLabel
{
    case North = 'norte';
    case Northeast = 'nordeste';
    case Midwest = 'centro_oeste';
    case Southeast = 'sudeste';
    case South = 'sul';

    public function getLabel(): string
    {
        return match ($this) {
            self::North => __('custom.North'),
            self::Northeast => __('custom.Northeast'),
            self::Midwest => __('custom.Midwest'),
            self::Southeast => __('custom.Southeast'),
            self::South => __('custom.South'),
        };
    }

    /**
     * @return list<string>
     */
    public function stateAbbreviations(): array
    {
        return match ($this) {
            self::North => ['AC', 'AP', 'AM', 'PA', 'RO', 'RR', 'TO'],
            self::Northeast => ['AL', 'BA', 'CE', 'MA', 'PB', 'PE', 'PI', 'RN', 'SE'],
            self::Midwest => ['DF', 'GO', 'MT', 'MS'],
            self::Southeast => ['ES', 'MG', 'RJ', 'SP'],
            self::South => ['PR', 'RS', 'SC'],
        };
    }

    public static function fromStateAbbreviation(?string $abbreviation): ?self
    {
        if ($abbreviation === null || $abbreviation === '') {
            return null;
        }

        $abbreviation = strtoupper($abbreviation);

        foreach (self::cases() as $region) {
            if (in_array($abbreviation, $region->stateAbbreviations(), true)) {
                return $region;
            }
        }

        return null;
    }
}
