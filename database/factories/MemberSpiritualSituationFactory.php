<?php

namespace Database\Factories;

use App\Enums\SpiritualSituation;
use App\Models\Member;
use App\Models\MemberSpiritualSituation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MemberSpiritualSituation>
 */
class MemberSpiritualSituationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'member_id' => Member::factory(),
            'situation' => SpiritualSituation::InCommunion,
            'directed_by_member_id' => Member::factory(),
            'recorded_at' => fake()->date(),
            'discipline_days' => null,
            'reason' => null,
        ];
    }

    public function disciplined(?int $days = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'situation' => SpiritualSituation::Disciplined,
            'discipline_days' => $days,
            'reason' => 'Disciplina aplicada em culto',
        ]);
    }
}
