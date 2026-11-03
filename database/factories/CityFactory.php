<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<City>
 */
class CityFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $state = State::factory()->create();

        return [
            'name' => fake()->unique()->city(),
            'state_id' => $state->id,
            'country_id' => $state->country_id ?? Country::query()->create([
                'name' => fake()->unique()->country().' '.fake()->unique()->numerify('###'),
                'iso_code' => 'Z'.strtoupper(fake()->unique()->lexify('?')),
            ])->id,
            'capital_city' => false,
        ];
    }

    public function forState(State $state): static
    {
        return $this->state(fn (): array => [
            'state_id' => $state->id,
            'country_id' => $state->country_id,
        ]);
    }
}
