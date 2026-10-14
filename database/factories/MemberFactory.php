<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'father_name' => fake()->name(),
            'mother_name' => fake()->name(),
            'birth_date' => fake()->date(),
            'sex' => fake()->boolean(),
            'document' => fake()->unique()->numerify('###########'),
            'email' => fake()->unique()->safeEmail(),
            'country_id' => Country::query()->create([
                'name' => fake()->unique()->country().' '.fake()->unique()->numerify('###'),
                'iso_code' => strtoupper(fake()->unique()->lexify('??')),
            ])->id,
            'user_id' => User::factory(),
        ];
    }
}
