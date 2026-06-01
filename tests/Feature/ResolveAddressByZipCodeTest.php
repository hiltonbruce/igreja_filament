<?php

namespace Tests\Feature;

use App\Actions\ResolveAddressByZipCode;
use App\Models\City;
use App\Models\Country;
use App\Models\Neighborhood;
use App\Models\State;
use App\Models\Street;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ResolveAddressByZipCodeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Country::query()->create([
            'name' => 'Brasil',
            'iso_code' => 'BR',
        ]);
    }

    public function test_it_returns_null_for_invalid_zip_codes(): void
    {
        Http::fake();

        $this->assertNull(app(ResolveAddressByZipCode::class)->handle(null));
        $this->assertNull(app(ResolveAddressByZipCode::class)->handle('123'));

        Http::assertNothingSent();
    }

    public function test_it_resolves_from_the_local_database_without_calling_the_api(): void
    {
        Http::fake();

        $country = Country::query()->where('iso_code', 'BR')->firstOrFail();
        $state = State::query()->create(['name' => 'Santa Catarina', 'abbreviation' => 'SC', 'country_id' => $country->id]);
        $city = City::query()->create(['name' => 'Blumenau', 'state_id' => $state->id, 'country_id' => $country->id]);
        $neighborhood = Neighborhood::query()->create(['name' => 'Centro', 'city_id' => $city->id, 'state_id' => $state->id, 'country_id' => $country->id]);
        $street = Street::query()->create([
            'name' => 'Rua Local',
            'zip_code' => '89010025',
            'neighborhood_id' => $neighborhood->id,
            'city_id' => $city->id,
            'state_id' => $state->id,
            'country_id' => $country->id,
        ]);

        $resolved = app(ResolveAddressByZipCode::class)->handle('89010-025');

        Http::assertNothingSent();
        $this->assertSame($street->id, $resolved['street_id']);
        $this->assertSame('Rua Local', $resolved['address']);
        $this->assertSame($neighborhood->id, $resolved['neighborhood_id']);
    }

    public function test_it_prefers_brasil_api_v2_over_v1(): void
    {
        Http::fake([
            'https://brasilapi.com.br/api/cep/v2/*' => Http::response([
                'cep' => '89010025',
                'state' => 'SC',
                'city' => 'Blumenau',
                'neighborhood' => 'Centro',
                'street' => 'Rua Doutor Luiz de Freitas Melro',
                'location' => [
                    'type' => 'Point',
                    'coordinates' => [
                        'longitude' => '-49.0629788',
                        'latitude' => '-26.9244749',
                    ],
                ],
            ]),
            'https://brasilapi.com.br/api/cep/v1/*' => Http::response([
                'cep' => '89010025',
                'state' => 'SC',
                'city' => 'Blumenau',
                'neighborhood' => 'Centro',
                'street' => 'Rua que NÃO deveria ser usada',
                'service' => 'viacep',
            ]),
            'https://brasilapi.com.br/api/ibge/uf/v1/*' => Http::response([
                'id' => 42,
                'sigla' => 'SC',
                'nome' => 'Santa Catarina',
            ]),
        ]);

        $resolved = app(ResolveAddressByZipCode::class)->handle('89010-025');

        Http::assertNotSent(fn ($request): bool => str_contains($request->url(), '/api/cep/v1/'));
        Http::assertSent(fn ($request): bool => str_contains($request->url(), '/api/cep/v2/'));
        $this->assertSame('Rua Doutor Luiz de Freitas Melro', $resolved['address']);
        $this->assertSame('-26.9244749', $resolved['latitude']);
        $this->assertSame('-49.0629788', $resolved['longitude']);

        $this->assertDatabaseHas('states', ['abbreviation' => 'SC', 'name' => 'Santa Catarina']);
        $this->assertDatabaseHas('cities', ['name' => 'Blumenau']);
        $this->assertDatabaseHas('neighborhoods', ['name' => 'Centro']);
        $this->assertDatabaseHas('streets', [
            'name' => 'Rua Doutor Luiz de Freitas Melro',
            'zip_code' => '89010025',
            'latitude' => '-26.9244749',
            'longitude' => '-49.0629788',
        ]);
    }

    public function test_it_falls_back_to_v1_when_v2_has_no_street(): void
    {
        Http::fake([
            'https://brasilapi.com.br/api/cep/v2/*' => Http::response([], 404),
            'https://brasilapi.com.br/api/cep/v1/*' => Http::response([
                'cep' => '89010025',
                'state' => 'SC',
                'city' => 'Blumenau',
                'neighborhood' => 'Centro',
                'street' => 'Rua Doutor Luiz de Freitas Melro',
                'service' => 'viacep',
            ]),
            'https://brasilapi.com.br/api/ibge/uf/v1/*' => Http::response([
                'id' => 42,
                'sigla' => 'SC',
                'nome' => 'Santa Catarina',
            ]),
        ]);

        $resolved = app(ResolveAddressByZipCode::class)->handle('89010025');

        $this->assertSame('Rua Doutor Luiz de Freitas Melro', $resolved['address']);
        $this->assertNull($resolved['latitude']);
        $this->assertNull($resolved['longitude']);
        $this->assertDatabaseHas('states', ['abbreviation' => 'SC', 'name' => 'Santa Catarina']);
    }

    public function test_it_fetches_and_saves_the_state_name_from_ibge_when_missing(): void
    {
        Http::fake([
            'https://brasilapi.com.br/api/cep/v2/*' => Http::response([
                'cep' => '01001000',
                'state' => 'SP',
                'city' => 'São Paulo',
                'neighborhood' => 'Sé',
                'street' => 'Praça da Sé',
            ]),
            'https://brasilapi.com.br/api/ibge/uf/v1/SP' => Http::response([
                'id' => 35,
                'sigla' => 'SP',
                'nome' => 'São Paulo',
            ]),
        ]);

        app(ResolveAddressByZipCode::class)->handle('01001-000');

        Http::assertSent(fn ($request): bool => str_contains($request->url(), '/api/ibge/uf/v1/SP'));
        $this->assertDatabaseHas('states', ['abbreviation' => 'SP', 'name' => 'São Paulo']);
    }

    public function test_it_falls_back_to_the_abbreviation_when_ibge_lookup_fails(): void
    {
        Http::fake([
            'https://brasilapi.com.br/api/cep/v2/*' => Http::response([
                'cep' => '01001000',
                'state' => 'SP',
                'city' => 'São Paulo',
                'neighborhood' => 'Sé',
                'street' => 'Praça da Sé',
            ]),
            'https://brasilapi.com.br/api/ibge/uf/v1/*' => Http::response([], 500),
        ]);

        app(ResolveAddressByZipCode::class)->handle('01001-000');

        $this->assertDatabaseHas('states', ['abbreviation' => 'SP', 'name' => 'SP']);
    }

    public function test_it_restores_a_missing_leading_zero_when_searching_locally(): void
    {
        Http::fake();

        $country = Country::query()->where('iso_code', 'BR')->firstOrFail();
        $state = State::query()->create(['name' => 'São Paulo', 'abbreviation' => 'SP', 'country_id' => $country->id]);
        $city = City::query()->create(['name' => 'São Paulo', 'state_id' => $state->id, 'country_id' => $country->id]);
        $neighborhood = Neighborhood::query()->create(['name' => 'Sé', 'city_id' => $city->id, 'state_id' => $state->id, 'country_id' => $country->id]);
        $street = Street::query()->create([
            'name' => 'Praça da Sé',
            'zip_code' => '01001000',
            'neighborhood_id' => $neighborhood->id,
            'city_id' => $city->id,
            'state_id' => $state->id,
            'country_id' => $country->id,
        ]);

        // Value coming back from an integer column lost its leading zero.
        $resolved = app(ResolveAddressByZipCode::class)->handle('1001000');

        Http::assertNothingSent();
        $this->assertSame('01001000', $resolved['zip_code']);
        $this->assertSame($street->id, $resolved['street_id']);
    }

    public function test_it_updates_an_existing_street_with_geolocation(): void
    {
        Http::fake([
            'https://brasilapi.com.br/api/cep/v1/*' => Http::response([], 404),
            'https://brasilapi.com.br/api/cep/v2/*' => Http::response([
                'cep' => '89010025',
                'state' => 'SC',
                'city' => 'Blumenau',
                'neighborhood' => 'Centro',
                'street' => 'Rua Doutor Luiz de Freitas Melro',
                'location' => [
                    'type' => 'Point',
                    'coordinates' => [
                        'longitude' => '-49.0629788',
                        'latitude' => '-26.9244749',
                    ],
                ],
            ]),
        ]);

        app(ResolveAddressByZipCode::class)->handle('89010025');
        app(ResolveAddressByZipCode::class)->handle('89010025');

        $this->assertSame(1, Street::query()->where('zip_code', '89010025')->count());
    }
}
