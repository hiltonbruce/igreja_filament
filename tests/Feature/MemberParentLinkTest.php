<?php

namespace Tests\Feature;

use App\Filament\Secretary\Resources\Members\Pages\EditMember;
use App\Models\Country;
use App\Models\Member;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MemberParentLinkTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('pt_BR');
    }

    public function test_parent_searches_only_offer_the_matching_sex(): void
    {
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('secretary'));

        $member = $this->member();
        $father = Member::factory()->male()->create(['name' => 'Pai Compartilhado Silva']);
        $mother = Member::factory()->female()->create(['name' => 'Mae Compartilhado Souza']);

        $maleIds = Member::searchBySexAndName('Compartilhado', true, $member->id)->modelKeys();
        $femaleIds = Member::searchBySexAndName('Compartilhado', false, $member->id)->modelKeys();

        $this->assertContains($father->id, $maleIds);
        $this->assertNotContains($mother->id, $maleIds);
        $this->assertContains($mother->id, $femaleIds);
        $this->assertNotContains($father->id, $femaleIds);

        Livewire::test(EditMember::class, ['record' => $member->getRouteKey()])
            ->assertDontSee('Membro Pai')
            ->assertDontSee('Membro Mãe')
            ->set('data.father_name', 'Compartilhado')
            ->assertSee('Pai Compartilhado Silva')
            ->assertDontSee('Mae Compartilhado Souza')
            ->set('data.mother_name', 'Compartilhado')
            ->assertSee('Mae Compartilhado Souza');
    }

    public function test_a_typed_parent_name_does_not_store_the_member_id(): void
    {
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('secretary'));

        $member = $this->member();
        $father = Member::factory()->male()->create(['name' => 'Pai Unico Silva']);
        $mother = Member::factory()->female()->create(['name' => 'Mae Unica Souza']);

        Livewire::test(EditMember::class, ['record' => $member->getRouteKey()])
            ->fillForm($this->formState($member, [
                'father_name' => $father->name,
                'mother_name' => $mother->name,
                'father_member_id' => null,
                'mother_member_id' => null,
            ]))
            ->call('save')
            ->assertHasNoFormErrors();

        $member->refresh();

        $this->assertSame($father->name, $member->father_name);
        $this->assertSame($mother->name, $member->mother_name);
        $this->assertNull($member->father_member_id);
        $this->assertNull($member->mother_member_id);
    }

    public function test_selecting_a_parent_member_stores_that_id_and_name(): void
    {
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('secretary'));

        $member = $this->member();
        $father = Member::factory()->male()->create(['name' => 'Pai Unico Silva']);
        $mother = Member::factory()->female()->create(['name' => 'Mae Unica Souza']);

        Livewire::test(EditMember::class, ['record' => $member->getRouteKey()])
            ->fillForm($this->formState($member, [
                'father_name' => 'Pai Unico',
                'mother_name' => 'Mae Unica',
            ]))
            ->call('linkParentMember', 'father_name', 'father_member_id', $father->id)
            ->call('linkParentMember', 'mother_name', 'mother_member_id', $mother->id)
            ->assertSet('data.father_name', $father->name)
            ->assertSet('data.mother_name', $mother->name)
            ->assertSet('data.father_member_id', $father->id)
            ->assertSet('data.mother_member_id', $mother->id)
            ->call('save')
            ->assertHasNoFormErrors();

        $member->refresh();

        $this->assertSame($father->id, $member->father_member_id);
        $this->assertSame($mother->id, $member->mother_member_id);
        $this->assertSame($father->name, $member->father_name);
        $this->assertSame($mother->name, $member->mother_name);
    }

    public function test_a_parent_of_the_wrong_sex_is_rejected(): void
    {
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('secretary'));

        $member = $this->member();
        $woman = Member::factory()->female()->create();
        $man = Member::factory()->male()->create();

        Livewire::test(EditMember::class, ['record' => $member->getRouteKey()])
            ->fillForm($this->formState($member, [
                'father_member_id' => $woman->id,
            ]))
            ->call('save')
            ->assertHasFormErrors(['father_member_id']);

        Livewire::test(EditMember::class, ['record' => $member->getRouteKey()])
            ->fillForm($this->formState($member, [
                'mother_member_id' => $man->id,
            ]))
            ->call('save')
            ->assertHasFormErrors(['mother_member_id']);

        $member->refresh();

        $this->assertNull($member->father_member_id);
        $this->assertNull($member->mother_member_id);
    }

    public function test_changing_the_parent_name_after_a_link_clears_the_id(): void
    {
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('secretary'));

        $member = $this->member();
        $father = Member::factory()->male()->create(['name' => 'Pai Unico Silva']);

        Livewire::test(EditMember::class, ['record' => $member->getRouteKey()])
            ->fillForm($this->formState($member))
            ->set('data.father_member_id', $father->id)
            ->set('data.father_name', 'Nome digitado sem vinculo')
            ->call('save')
            ->assertHasNoFormErrors();

        $member->refresh();

        $this->assertSame('Nome digitado sem vinculo', $member->father_name);
        $this->assertNull($member->father_member_id);
    }

    private function member(): Member
    {
        $brazil = Country::query()->create([
            'name' => 'Brasil',
            'iso_code' => 'BR',
        ]);

        return Member::factory()->create([
            'phone' => '11999999999',
            'country_id' => $brazil->id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function formState(Member $member, array $overrides = []): array
    {
        return [
            'name' => $member->name,
            'father_name' => $member->father_name,
            'mother_name' => $member->mother_name,
            'birth_date' => $member->birth_date?->toDateString(),
            'sex' => '1',
            'brazilian_born' => 'true',
            'document_type' => 'CPF',
            'document' => $member->document,
            'email' => $member->email,
            'phone' => '11999999999',
            'country_id' => $member->country_id,
            ...$overrides,
        ];
    }
}
