<?php

namespace Tests\Feature;

use App\Actions\ResolveForeignLocation;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResolveForeignLocationTest extends TestCase
{
    use RefreshDatabase;

    private Country $country;

    protected function setUp(): void
    {
        parent::setUp();

        $this->country = Country::query()->create([
            'name' => 'Portugal',
            'iso_code' => 'PT',
        ]);
    }

    public function test_it_creates_a_foreign_state_without_an_abbreviation(): void
    {
        $stateId = app(ResolveForeignLocation::class)->resolveState($this->country->id, '  Lisboa  ');

        $this->assertNotNull($stateId);
        $this->assertDatabaseHas('states', [
            'id' => $stateId,
            'name' => 'Lisboa',
            'country_id' => $this->country->id,
            'abbreviation' => null,
        ]);
    }

    public function test_it_reuses_an_existing_foreign_state(): void
    {
        $existing = State::query()->create([
            'name' => 'Lisboa',
            'country_id' => $this->country->id,
        ]);

        $stateId = app(ResolveForeignLocation::class)->resolveState($this->country->id, 'Lisboa');

        $this->assertSame($existing->id, $stateId);
        $this->assertSame(1, State::query()->where('name', 'Lisboa')->count());
    }

    public function test_it_creates_a_foreign_city_under_the_state(): void
    {
        $resolver = app(ResolveForeignLocation::class);
        $stateId = $resolver->resolveState($this->country->id, 'Lisboa');

        $cityId = $resolver->resolveCity($this->country->id, $stateId, 'Sintra');

        $this->assertNotNull($cityId);
        $this->assertDatabaseHas('cities', [
            'id' => $cityId,
            'name' => 'Sintra',
            'state_id' => $stateId,
            'country_id' => $this->country->id,
            'ibge_code' => null,
        ]);
    }

    public function test_it_returns_null_for_blank_or_incomplete_input(): void
    {
        $resolver = app(ResolveForeignLocation::class);

        $this->assertNull($resolver->resolveState($this->country->id, '   '));
        $this->assertNull($resolver->resolveState(null, 'Lisboa'));
        $this->assertNull($resolver->resolveCity($this->country->id, null, 'Sintra'));
        $this->assertSame(0, State::query()->count());
        $this->assertSame(0, City::query()->count());
    }
}
