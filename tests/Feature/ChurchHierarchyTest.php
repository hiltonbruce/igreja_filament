<?php

namespace Tests\Feature;

use App\Actions\ResolveForeignLocation;
use App\Enums\BrazilRegion;
use App\Enums\ChurchLevel;
use App\Filament\Secretary\Resources\Churches\Pages\CreateChurch;
use App\Models\Church;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use App\Models\User;
use App\Support\ChurchHierarchy;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class ChurchHierarchyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('pt_BR');
    }

    public function test_a_municipal_church_can_have_congregations_without_higher_parents(): void
    {
        $municipal = Church::factory()->municipal()->create([
            'name' => 'Sede Municipal Centro',
        ]);

        $congregation = Church::factory()->congregation($municipal)->create([
            'name' => 'Congregação Jardim',
        ]);

        $this->assertSame(ChurchLevel::Municipal, $municipal->level);
        $this->assertNull($municipal->parent_id);
        $this->assertSame(ChurchLevel::Congregation, $congregation->level);
        $this->assertSame($municipal->id, $congregation->parent_id);
        $this->assertTrue($municipal->congregations->contains($congregation));
    }

    public function test_a_state_headquarters_can_have_multiple_municipal_churches_in_the_same_city(): void
    {
        $state = State::factory()->inRegion(BrazilRegion::Southeast)->create();
        $city = City::factory()->forState($state)->create();

        $stateChurch = Church::factory()->stateLevel($state)->create([
            'name' => 'Sede Estadual',
        ]);

        $firstMunicipal = Church::factory()->municipal($city)->childOf($stateChurch)->create([
            'name' => 'Sede Municipal Norte',
        ]);
        $secondMunicipal = Church::factory()->municipal($city)->childOf($stateChurch)->create([
            'name' => 'Sede Municipal Sul',
        ]);

        Church::factory()->congregation($firstMunicipal)->create(['name' => 'Congregação A']);
        Church::factory()->congregation($secondMunicipal)->create(['name' => 'Congregação B']);

        $this->assertSame(2, $stateChurch->children()->count());
        $this->assertSame($city->id, $firstMunicipal->city_id);
        $this->assertSame($city->id, $secondMunicipal->city_id);
        $this->assertSame(1, $firstMunicipal->congregations()->count());
        $this->assertSame(1, $secondMunicipal->congregations()->count());
    }

    public function test_a_congregation_without_municipal_parent_is_rejected(): void
    {
        $city = City::factory()->create();
        $user = User::factory()->create();

        $this->expectException(ValidationException::class);

        ChurchHierarchy::validate([
            'name' => 'Congregação Sem Pai',
            'level' => ChurchLevel::Congregation->value,
            'city_id' => $city->id,
            'state_id' => $city->state_id,
            'country_id' => $city->country_id,
            'user_id' => $user->id,
        ]);
    }

    public function test_an_incompatible_parent_level_is_rejected(): void
    {
        $brazil = Country::query()->create(['name' => 'Brasil', 'iso_code' => 'BR']);
        $municipal = Church::factory()->municipal()->create();
        $user = User::factory()->create();

        try {
            ChurchHierarchy::validate([
                'name' => 'Sede Regional Inválida',
                'level' => ChurchLevel::Regional->value,
                'region' => BrazilRegion::Southeast->value,
                'country_id' => $brazil->id,
                'parent_id' => $municipal->id,
                'user_id' => $user->id,
            ]);
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('parent_id', $exception->errors());
        }
    }

    public function test_regional_requires_region_state_requires_state_and_municipal_requires_city(): void
    {
        $brazil = Country::query()->create(['name' => 'Brasil', 'iso_code' => 'BR']);
        $user = User::factory()->create();

        try {
            ChurchHierarchy::validate([
                'name' => 'Regional Sem Região',
                'level' => ChurchLevel::Regional->value,
                'country_id' => $brazil->id,
                'user_id' => $user->id,
            ]);
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('region', $exception->errors());
        }

        try {
            ChurchHierarchy::validate([
                'name' => 'Estadual Sem Estado',
                'level' => ChurchLevel::State->value,
                'country_id' => $brazil->id,
                'user_id' => $user->id,
            ]);
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('state_id', $exception->errors());
        }

        try {
            ChurchHierarchy::validate([
                'name' => 'Municipal Sem Cidade',
                'level' => ChurchLevel::Municipal->value,
                'country_id' => $brazil->id,
                'user_id' => $user->id,
            ]);
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('city_id', $exception->errors());
        }
    }

    public function test_a_foreign_municipal_church_and_congregation_can_be_created(): void
    {
        $portugal = Country::query()->create(['name' => 'Portugal', 'iso_code' => 'PT']);
        $user = User::factory()->create();
        $resolver = app(ResolveForeignLocation::class);

        $stateId = $resolver->resolveState($portugal->id, 'Lisboa');
        $cityId = $resolver->resolveCity($portugal->id, $stateId, 'Sintra');

        $validated = ChurchHierarchy::validate([
            'name' => 'Sede Sintra',
            'level' => ChurchLevel::Municipal->value,
            'country_id' => $portugal->id,
            'state_id' => $stateId,
            'city_id' => $cityId,
            'region' => BrazilRegion::Southeast->value,
            'user_id' => $user->id,
        ]);

        $this->assertNull($validated['region']);

        $municipal = Church::query()->create($validated);

        $congregation = Church::factory()->congregation($municipal)->create([
            'name' => 'Congregação Cascais',
        ]);

        $this->assertSame(ChurchLevel::Municipal, $municipal->level);
        $this->assertSame($portugal->id, $municipal->country_id);
        $this->assertNull($municipal->region);
        $this->assertSame($municipal->id, $congregation->parent_id);
    }

    public function test_a_foreign_regional_church_does_not_require_brazil_region(): void
    {
        $angola = Country::query()->create(['name' => 'Angola', 'iso_code' => 'AO']);
        $user = User::factory()->create();

        $validated = ChurchHierarchy::validate([
            'name' => 'Campo Missionário Angola',
            'level' => ChurchLevel::Regional->value,
            'country_id' => $angola->id,
            'user_id' => $user->id,
        ]);

        $this->assertNull($validated['region'] ?? null);
        $this->assertSame(ChurchLevel::Regional->value, $validated['level']);
    }

    public function test_the_secretary_form_creates_a_foreign_municipal_church(): void
    {
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('secretary'));

        $portugal = Country::query()->create(['name' => 'Portugal', 'iso_code' => 'PT']);
        $resolver = app(ResolveForeignLocation::class);
        $stateId = $resolver->resolveState($portugal->id, 'Lisboa');
        $cityId = $resolver->resolveCity($portugal->id, $stateId, 'Lisboa');

        Livewire::test(CreateChurch::class)
            ->fillForm([
                'name' => 'Igreja de Lisboa',
                'level' => ChurchLevel::Municipal->value,
                'country_id' => $portugal->id,
                'timezone' => 'Europe/Lisbon',
            ])
            ->fillForm([
                'state_id' => $stateId,
                'city_id' => $cityId,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $church = Church::query()->where('name', 'Igreja de Lisboa')->first();

        $this->assertNotNull($church);
        $this->assertSame($portugal->id, $church->country_id);
        $this->assertNull($church->region);
        $this->assertSame($stateId, $church->state_id);
        $this->assertSame($cityId, $church->city_id);
        $this->assertSame('Lisboa', $church->state?->name);
        $this->assertNull($church->state?->abbreviation);
    }

    public function test_the_secretary_form_creates_a_municipal_church(): void
    {
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('secretary'));

        $brazil = Country::query()->create(['name' => 'Brasil', 'iso_code' => 'BR']);
        $state = State::factory()->inRegion(BrazilRegion::Southeast)->create([
            'country_id' => $brazil->id,
        ]);
        $city = City::factory()->forState($state)->create();

        Livewire::test(CreateChurch::class)
            ->fillForm([
                'name' => 'Sede Municipal Teste',
                'level' => ChurchLevel::Municipal->value,
                'country_id' => $brazil->id,
                'timezone' => 'America/Sao_Paulo',
            ])
            ->fillForm([
                'brazil_state_id' => $state->id,
                'brazil_city_id' => $city->id,
                'state_id' => $state->id,
                'city_id' => $city->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('churches', [
            'name' => 'Sede Municipal Teste',
            'level' => ChurchLevel::Municipal->value,
            'city_id' => $city->id,
            'state_id' => $state->id,
            'country_id' => $brazil->id,
        ]);
    }

    public function test_states_receive_brazil_region_from_abbreviation_mapping(): void
    {
        $state = State::factory()->create([
            'abbreviation' => 'SP',
            'region' => BrazilRegion::Southeast,
        ]);

        $this->assertSame(BrazilRegion::Southeast, $state->fresh()->region);
        $this->assertSame(BrazilRegion::Southeast, BrazilRegion::fromStateAbbreviation('SP'));
        $this->assertSame(BrazilRegion::South, BrazilRegion::fromStateAbbreviation('RS'));
    }
}
