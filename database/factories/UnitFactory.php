<?php

namespace Database\Factories;

use App\Models\Church;
use App\Models\Unit;
use App\Models\UnitType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Unit>
 */
class UnitFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'church_id' => Church::factory(),
            'unit_type_id' => UnitType::factory(),
            'parent_id' => null,
            'name' => fake()->optional()->words(2, true),
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
            'user_id' => User::factory(),
        ];
    }

    public function childOf(Unit $parent): static
    {
        return $this->state(fn (): array => [
            'church_id' => $parent->church_id,
            'parent_id' => $parent->id,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
