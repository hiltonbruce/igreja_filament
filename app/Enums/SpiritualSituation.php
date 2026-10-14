<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum SpiritualSituation: string implements HasColor, HasLabel
{
    case Disciplined = 'disciplined';
    case InCommunion = 'in_communion';
    case Deceased = 'deceased';
    case ChangedChurch = 'changed_church';
    case Withdrew = 'withdrew';
    case Transferred = 'transferred';

    public function getLabel(): string
    {
        return match ($this) {
            self::Disciplined => __('custom.Disciplined'),
            self::InCommunion => __('custom.In communion'),
            self::Deceased => __('custom.Deceased'),
            self::ChangedChurch => __('custom.Changed church'),
            self::Withdrew => __('custom.Withdrew'),
            self::Transferred => __('custom.Transferred'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::InCommunion => 'success',
            self::Disciplined => 'warning',
            self::Deceased => 'gray',
            self::ChangedChurch => 'info',
            self::Withdrew => 'danger',
            self::Transferred => 'primary',
        };
    }

    public function requiresReason(): bool
    {
        return in_array($this, [self::Disciplined, self::Withdrew, self::ChangedChurch], true);
    }

    /**
     * Situações que a secretaria escolhe no cadastro.
     * Transferido entra na lista apenas quando o registro já foi gerado por uma carta de mudança.
     *
     * @return array<string, string>
     */
    public static function manualOptions(?self $current = null): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            if ($case === self::Transferred && $current !== self::Transferred) {
                continue;
            }

            $options[$case->value] = $case->getLabel();
        }

        return $options;
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
