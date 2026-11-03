<?php

namespace Tests\Feature;

use App\Enums\EcclesiasticalRank;
use App\Models\Appointment;
use App\Models\Church;
use App\Models\Member;
use App\Models\Position;
use App\Models\Unit;
use App\Models\UnitType;
use App\Support\AppointmentRules;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AppointmentRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_current_rank_uses_highest_consecration(): void
    {
        $member = Member::factory()->create([
            'deacon_consecrated_at' => '2018-01-01',
            'pastor_consecrated_at' => '2024-01-01',
        ]);

        $this->assertSame(EcclesiasticalRank::Pastor, $member->currentEcclesiasticalRank());
    }

    public function test_member_without_consecration_has_no_rank(): void
    {
        $member = Member::factory()->create();

        $this->assertNull($member->currentEcclesiasticalRank());
    }

    public function test_unit_hierarchy_stays_within_the_same_church(): void
    {
        $church = Church::factory()->create();
        $type = UnitType::factory()->create();

        $parent = Unit::factory()
            ->for($church)
            ->for($type, 'unitType')
            ->create(['name' => null]);

        $child = Unit::factory()->childOf($parent)->for($type, 'unitType')->create();

        $this->assertSame($church->id, $child->church_id);
        $this->assertSame($parent->id, $child->parent->id);
        $this->assertTrue($parent->children->contains($child));
        $this->assertSame($type->name, $parent->displayName());
    }

    public function test_unit_id_defines_church_id(): void
    {
        $church = Church::factory()->create();
        $unit = Unit::factory()->for($church)->create();
        $member = Member::factory()->create();
        $position = Position::factory()->unitScope()->create();

        $data = AppointmentRules::validate([
            'member_id' => $member->id,
            'position_id' => $position->id,
            'church_id' => Church::factory()->create()->id,
            'unit_id' => $unit->id,
        ]);

        $this->assertSame($church->id, $data['church_id']);
    }

    public function test_ordinal_is_required_for_positions_that_support_it(): void
    {
        $church = Church::factory()->create();
        $unit = Unit::factory()->for($church)->create();
        $member = Member::factory()->create();
        $position = Position::factory()->unitScope()->withOrdinal()->create();

        $this->expectException(ValidationException::class);

        AppointmentRules::validate([
            'member_id' => $member->id,
            'position_id' => $position->id,
            'unit_id' => $unit->id,
        ]);
    }

    public function test_ordinal_must_be_unique_per_unit_among_active_appointments(): void
    {
        $church = Church::factory()->create();
        $unit = Unit::factory()->for($church)->create();
        $position = Position::factory()->unitScope()->withOrdinal()->create();

        Appointment::factory()->create([
            'church_id' => $church->id,
            'unit_id' => $unit->id,
            'position_id' => $position->id,
            'ordinal' => 1,
            'is_active' => true,
        ]);

        $this->expectException(ValidationException::class);

        AppointmentRules::validate([
            'member_id' => Member::factory()->create()->id,
            'position_id' => $position->id,
            'unit_id' => $unit->id,
            'ordinal' => 1,
        ]);
    }

    public function test_same_ordinal_is_allowed_in_a_different_unit(): void
    {
        $church = Church::factory()->create();
        $unitA = Unit::factory()->for($church)->create();
        $unitB = Unit::factory()->for($church)->create();
        $position = Position::factory()->unitScope()->withOrdinal()->create();

        Appointment::factory()->create([
            'church_id' => $church->id,
            'unit_id' => $unitA->id,
            'position_id' => $position->id,
            'ordinal' => 1,
            'is_active' => true,
        ]);

        $data = AppointmentRules::validate([
            'member_id' => Member::factory()->create()->id,
            'position_id' => $position->id,
            'unit_id' => $unitB->id,
            'ordinal' => 1,
        ]);

        $this->assertSame(1, $data['ordinal']);
        $this->assertSame($unitB->id, $data['unit_id']);
    }

    public function test_unit_scoped_position_requires_a_unit(): void
    {
        $church = Church::factory()->create();
        $member = Member::factory()->create();
        $position = Position::factory()->unitScope()->create();

        $this->expectException(ValidationException::class);

        AppointmentRules::validate([
            'member_id' => $member->id,
            'position_id' => $position->id,
            'church_id' => $church->id,
        ]);
    }

    public function test_church_scoped_position_rejects_a_unit(): void
    {
        $church = Church::factory()->create();
        $unit = Unit::factory()->for($church)->create();
        $member = Member::factory()->create();
        $position = Position::factory()->churchScope()->create();

        $this->expectException(ValidationException::class);

        AppointmentRules::validate([
            'member_id' => $member->id,
            'position_id' => $position->id,
            'unit_id' => $unit->id,
        ]);
    }
}
