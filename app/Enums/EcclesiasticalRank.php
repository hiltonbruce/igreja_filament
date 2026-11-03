<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EcclesiasticalRank: string implements HasColor, HasLabel
{
    case Auxiliary = 'auxiliary';
    case Deacon = 'deacon';
    case Presbyter = 'presbyter';
    case Evangelist = 'evangelist';
    case Pastor = 'pastor';

    public function getLabel(): string
    {
        return match ($this) {
            self::Auxiliary => __('custom.Work assistant'),
            self::Deacon => __('custom.Deacon'),
            self::Presbyter => __('custom.Presbyter'),
            self::Evangelist => __('custom.Evangelist'),
            self::Pastor => __('custom.Pastor'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Auxiliary => 'gray',
            self::Deacon => 'info',
            self::Presbyter => 'success',
            self::Evangelist => 'warning',
            self::Pastor => 'danger',
        };
    }

    /**
     * Posição na hierarquia; quanto maior, mais alta a patente.
     */
    public function order(): int
    {
        return match ($this) {
            self::Auxiliary => 1,
            self::Deacon => 2,
            self::Presbyter => 3,
            self::Evangelist => 4,
            self::Pastor => 5,
        };
    }

    /**
     * Coluna de data de consagração correspondente na tabela members.
     */
    public function consecrationDateColumn(): string
    {
        return match ($this) {
            self::Auxiliary => 'auxiliary_consecrated_at',
            self::Deacon => 'deacon_consecrated_at',
            self::Presbyter => 'presbyter_consecrated_at',
            self::Evangelist => 'evangelist_consecrated_at',
            self::Pastor => 'pastor_consecrated_at',
        };
    }

    /**
     * Patente mais alta entre as datas de consagração preenchidas.
     *
     * @param  array<string, mixed>  $dates  Mapa coluna => data (null quando não consagrado).
     */
    public static function highestWithDates(array $dates): ?self
    {
        $highest = null;

        foreach (self::cases() as $rank) {
            if (! filled($dates[$rank->consecrationDateColumn()] ?? null)) {
                continue;
            }

            if ($highest === null || $rank->order() > $highest->order()) {
                $highest = $rank;
            }
        }

        return $highest;
    }
}
