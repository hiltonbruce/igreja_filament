<?php

namespace Tests\Unit;

use App\Enums\EcclesiasticalRank;
use PHPUnit\Framework\TestCase;

class EcclesiasticalRankTest extends TestCase
{
    public function test_returns_null_when_no_dates_are_set(): void
    {
        $rank = EcclesiasticalRank::highestWithDates([
            'auxiliary_consecrated_at' => null,
            'deacon_consecrated_at' => null,
            'presbyter_consecrated_at' => null,
            'evangelist_consecrated_at' => null,
            'pastor_consecrated_at' => null,
        ]);

        $this->assertNull($rank);
    }

    public function test_returns_the_highest_rank_with_a_date(): void
    {
        $rank = EcclesiasticalRank::highestWithDates([
            'auxiliary_consecrated_at' => '2020-01-01',
            'deacon_consecrated_at' => '2021-01-01',
            'presbyter_consecrated_at' => '2022-01-01',
            'evangelist_consecrated_at' => null,
            'pastor_consecrated_at' => null,
        ]);

        $this->assertSame(EcclesiasticalRank::Presbyter, $rank);
    }

    public function test_highest_rank_ignores_lower_ranks_even_with_later_dates(): void
    {
        $rank = EcclesiasticalRank::highestWithDates([
            'auxiliary_consecrated_at' => '2025-01-01',
            'deacon_consecrated_at' => null,
            'presbyter_consecrated_at' => null,
            'evangelist_consecrated_at' => null,
            'pastor_consecrated_at' => '2010-01-01',
        ]);

        $this->assertSame(EcclesiasticalRank::Pastor, $rank);
    }

    public function test_each_rank_maps_to_its_consecration_column(): void
    {
        $this->assertSame('auxiliary_consecrated_at', EcclesiasticalRank::Auxiliary->consecrationDateColumn());
        $this->assertSame('pastor_consecrated_at', EcclesiasticalRank::Pastor->consecrationDateColumn());
    }
}
