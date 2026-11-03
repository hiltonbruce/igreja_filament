<?php

namespace Database\Factories;

use App\Enums\PositionScope;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Position>
 */
class PositionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => ucfirst(fake()->unique()->words(2, true)),
            'description' => fake()->optional()->sentence(),
            'supports_ordinal' => false,
            'scope' => PositionScope::Both,
            'is_active' => true,
            'user_id' => User::factory(),
        ];
    }

    public function withOrdinal(): static
    {
        return $this->state(fn (): array => ['supports_ordinal' => true]);
    }

    public function unitScope(): static
    {
        return $this->state(fn (): array => ['scope' => PositionScope::Unit]);
    }

    public function churchScope(): static
    {
        return $this->state(fn (): array => ['scope' => PositionScope::Church]);
    }
}
