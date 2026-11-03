<?php

namespace Database\Factories;

use App\Enums\BrazilRegion;
use App\Models\Country;
use App\Models\State;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<State>
 */
class StateFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $abbreviation = strtoupper(fake()->unique()->lexify('??'));

        return [
            'name' => fake()->unique()->state(),
            'abbreviation' => $abbreviation,
            'region' => BrazilRegion::fromStateAbbreviation($abbreviation) ?? BrazilRegion::Southeast,
            'country_id' => Country::query()->create([
                'name' => fake()->unique()->country().' '.fake()->unique()->numerify('###'),
                'iso_code' => 'Z'.strtoupper(fake()->unique()->lexify('?')),
            ])->id,
        ];
    }

    public function inRegion(BrazilRegion $region): static
    {
        $abbreviation = $region->stateAbbreviations()[0];

        return $this->state(fn (): array => [
            'abbreviation' => $abbreviation,
            'region' => $region,
            'name' => 'Estado '.$abbreviation,
        ]);
    }
}
