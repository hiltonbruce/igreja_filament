<?php

namespace Database\Factories;

use App\Models\UnitType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UnitType>
 */
class UnitTypeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => ucfirst(fake()->unique()->words(2, true)),
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
            'user_id' => User::factory(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
