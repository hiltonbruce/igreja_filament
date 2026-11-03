<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PositionScope: string implements HasLabel
{
    case Church = 'church';
    case Unit = 'unit';
    case Both = 'both';

    public function getLabel(): string
    {
        return match ($this) {
            self::Church => __('custom.Church level'),
            self::Unit => __('custom.Unit level'),
            self::Both => __('custom.Church or unit'),
        };
    }

    public function allowsChurch(): bool
    {
        return $this === self::Church || $this === self::Both;
    }

    public function allowsUnit(): bool
    {
        return $this === self::Unit || $this === self::Both;
    }

    public static function fromMixed(mixed $value): ?self
    {
        if ($value instanceof self) {
            return $value;
        }

        if (is_string($value) || is_int($value)) {
            return self::tryFrom((string) $value);
        }

        return null;
    }
}
