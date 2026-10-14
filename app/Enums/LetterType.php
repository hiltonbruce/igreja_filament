<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum LetterType: string implements HasColor, HasLabel
{
    case Recommendation = 'recommendation';
    case Change = 'change';
    case InTransit = 'in_transit';

    public function getLabel(): string
    {
        return match ($this) {
            self::Recommendation => __('custom.Recommendation'),
            self::Change => __('custom.Change'),
            self::InTransit => __('custom.In transit'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Recommendation => 'info',
            self::Change => 'primary',
            self::InTransit => 'warning',
        };
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
