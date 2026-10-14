<?php

namespace Database\Factories;

use App\Enums\LetterType;
use App\Models\Letter;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Letter>
 */
class LetterFactory extends Factory
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
            'type' => LetterType::Recommendation,
            'issued_at' => fake()->date(),
            'destination' => fake()->city(),
            'directed_by_member_id' => Member::factory(),
            'member_spiritual_situation_id' => null,
        ];
    }

    public function change(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => LetterType::Change,
        ]);
    }
}
