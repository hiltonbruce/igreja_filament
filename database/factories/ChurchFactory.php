<?php

namespace Database\Factories;

use App\Enums\BrazilRegion;
use App\Enums\ChurchLevel;
use App\Models\Church;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Church>
 */
class ChurchFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $city = City::factory()->create();

        return [
            'name' => 'Igreja '.fake()->unique()->company(),
            'level' => ChurchLevel::Municipal,
            'city_id' => $city->id,
            'state_id' => $city->state_id,
            'country_id' => $city->country_id,
            'timezone' => 'America/Sao_Paulo',
            'user_id' => User::factory(),
        ];
    }

    public function national(): static
    {
        return $this->state(fn (): array => [
            'level' => ChurchLevel::National,
            'region' => null,
            'state_id' => null,
            'city_id' => null,
            'parent_id' => null,
        ]);
    }

    public function regional(BrazilRegion $region = BrazilRegion::Southeast): static
    {
        return $this->state(function () use ($region): array {
            $brazil = Country::query()->firstOrCreate(
                ['iso_code' => 'BR'],
                ['name' => 'Brasil'],
            );

            return [
                'level' => ChurchLevel::Regional,
                'region' => $region,
                'country_id' => $brazil->id,
                'state_id' => null,
                'city_id' => null,
            ];
        });
    }

    public function foreign(?Country $country = null): static
    {
        $country ??= Country::query()->create([
            'name' => 'Portugal '.fake()->unique()->numerify('###'),
            'iso_code' => 'P'.strtoupper(fake()->unique()->lexify('?')),
        ]);

        $state = State::factory()->create([
            'name' => 'Lisboa',
            'abbreviation' => null,
            'region' => null,
            'country_id' => $country->id,
        ]);

        $city = City::factory()->forState($state)->create([
            'name' => 'Sintra',
        ]);

        return $this->state(fn (): array => [
            'level' => ChurchLevel::Municipal,
            'region' => null,
            'country_id' => $country->id,
            'state_id' => $state->id,
            'city_id' => $city->id,
        ]);
    }

    public function stateLevel(?State $state = null): static
    {
        $state ??= State::factory()->create();

        return $this->state(fn (): array => [
            'level' => ChurchLevel::State,
            'region' => null,
            'state_id' => $state->id,
            'city_id' => null,
            'country_id' => $state->country_id,
        ]);
    }

    public function municipal(?City $city = null): static
    {
        $city ??= City::factory()->create();

        return $this->state(fn (): array => [
            'level' => ChurchLevel::Municipal,
            'region' => null,
            'city_id' => $city->id,
            'state_id' => $city->state_id,
            'country_id' => $city->country_id,
        ]);
    }

    public function congregation(?Church $parent = null): static
    {
        $parent ??= Church::factory()->municipal()->create();

        return $this->state(fn (): array => [
            'level' => ChurchLevel::Congregation,
            'region' => null,
            'parent_id' => $parent->id,
            'city_id' => $parent->city_id,
            'state_id' => $parent->state_id,
            'country_id' => $parent->country_id,
        ]);
    }

    public function childOf(Church $parent): static
    {
        return $this->state(fn (): array => [
            'parent_id' => $parent->id,
        ]);
    }
}
