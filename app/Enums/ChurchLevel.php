<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ChurchLevel: string implements HasColor, HasLabel
{
    case National = 'national';
    case Regional = 'regional';
    case State = 'state';
    case Municipal = 'municipal';
    case Congregation = 'congregation';

    public function getLabel(): string
    {
        return match ($this) {
            self::National => __('custom.National headquarters'),
            self::Regional => __('custom.Regional headquarters'),
            self::State => __('custom.State headquarters'),
            self::Municipal => __('custom.Municipal headquarters'),
            self::Congregation => __('custom.Congregation'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::National => 'danger',
            self::Regional => 'warning',
            self::State => 'primary',
            self::Municipal => 'success',
            self::Congregation => 'info',
        };
    }

    /**
     * @return list<self>
     */
    public function allowedParentLevels(): array
    {
        return match ($this) {
            self::National => [],
            self::Regional => [self::National],
            self::State => [self::Regional, self::National],
            self::Municipal => [self::State, self::Regional, self::National],
            self::Congregation => [self::Municipal],
        };
    }

    public function allowsEmptyParent(): bool
    {
        return $this !== self::Congregation;
    }

    public function requiresRegion(): bool
    {
        return $this === self::Regional;
    }

    /**
     * Macrorregião brasileira só se aplica a sedes regionais no Brasil.
     */
    public function requiresBrazilRegion(bool $isBrazil): bool
    {
        return $this === self::Regional && $isBrazil;
    }

    public function requiresCountry(): bool
    {
        return $this !== self::National;
    }

    public function requiresState(): bool
    {
        return in_array($this, [self::State, self::Municipal, self::Congregation], true);
    }

    public function requiresCity(): bool
    {
        return in_array($this, [self::Municipal, self::Congregation], true);
    }
}
