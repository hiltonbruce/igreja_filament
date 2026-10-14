<?php

namespace Tests\Feature;

use App\Enums\SpiritualSituation;
use App\Filament\Secretary\Resources\Members\Pages\EditMember;
use App\Filament\Secretary\Resources\Members\Pages\ListMembers;
use App\Models\Country;
use App\Models\Member;
use App\Models\MemberSpiritualSituation;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class MemberSpiritualSituationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('pt_BR');
    }

    public function test_a_member_without_records_is_in_communion(): void
    {
        $member = Member::factory()->create();

        $this->assertSame(SpiritualSituation::InCommunion, $member->currentSpiritualSituation());
        $this->assertSame(SpiritualSituation::InCommunion, MemberSpiritualSituation::latestFromStates([]));
    }

    public function test_the_latest_record_defines_the_current_situation(): void
    {
        $member = Member::factory()->create();
        $director = Member::factory()->create();

        $member->spiritualSituations()->create([
            'situation' => SpiritualSituation::InCommunion,
            'directed_by_member_id' => $director->id,
            'recorded_at' => '2026-01-10',
        ]);

        $member->spiritualSituations()->create([
            'situation' => SpiritualSituation::Transferred,
            'directed_by_member_id' => $director->id,
            'recorded_at' => '2026-08-02',
        ]);

        $this->assertSame(SpiritualSituation::Transferred, $member->fresh()->currentSpiritualSituation());
    }

    public function test_the_highest_id_wins_when_records_share_the_same_date(): void
    {
        $member = Member::factory()->create();
        $director = Member::factory()->create();

        $older = $member->spiritualSituations()->create([
            'situation' => SpiritualSituation::InCommunion,
            'directed_by_member_id' => $director->id,
            'recorded_at' => '2026-03-01',
        ]);

        $newer = $member->spiritualSituations()->create([
            'situation' => SpiritualSituation::Deceased,
            'directed_by_member_id' => $director->id,
            'recorded_at' => '2026-03-01',
        ]);

        $this->assertGreaterThan($older->id, $newer->id);
        $this->assertSame(SpiritualSituation::Deceased, $member->fresh()->currentSpiritualSituation());
    }

    public function test_discipline_days_are_kept_only_for_disciplined_members(): void
    {
        $member = Member::factory()->create();
        $director = Member::factory()->create();

        $indefinite = $member->spiritualSituations()->create([
            'situation' => SpiritualSituation::Disciplined,
            'directed_by_member_id' => $director->id,
            'recorded_at' => '2026-04-01',
            'discipline_days' => null,
            'reason' => '  Ausência reiterada aos cultos  ',
        ]);

        $timed = $member->spiritualSituations()->create([
            'situation' => SpiritualSituation::Disciplined,
            'directed_by_member_id' => $director->id,
            'recorded_at' => '2026-05-01',
            'discipline_days' => 40,
            'reason' => 'Conduta incompatível',
        ]);

        $cleared = $member->spiritualSituations()->create([
            'situation' => SpiritualSituation::Withdrew,
            'directed_by_member_id' => $director->id,
            'recorded_at' => '2026-02-01',
            'discipline_days' => 15,
            'reason' => 'Pediu afastamento',
        ]);

        $this->assertNull($indefinite->fresh()->discipline_days);
        $this->assertSame('Ausência reiterada aos cultos', $indefinite->fresh()->reason);
        $this->assertSame(40, $timed->fresh()->discipline_days);
        $this->assertNull($cleared->fresh()->discipline_days);
        $this->assertSame('Pediu afastamento', $cleared->fresh()->reason);
        $this->assertSame(SpiritualSituation::Disciplined, $member->fresh()->currentSpiritualSituation());
    }

    public function test_unsaved_form_items_use_the_latest_situation_and_default_to_communion(): void
    {
        $situation = MemberSpiritualSituation::latestFromStates([
            ['situation' => 'in_communion', 'recorded_at' => '2026-01-01', 'id' => 1],
            ['situation' => SpiritualSituation::Withdrew, 'recorded_at' => '2026-02-01', 'id' => 2],
            ['situation' => 'disciplined', 'recorded_at' => '2026-02-01'],
        ]);

        $this->assertSame(SpiritualSituation::Disciplined, $situation);
        $this->assertSame([
            'disciplined',
            'in_communion',
            'deceased',
            'changed_church',
            'withdrew',
        ], array_keys(SpiritualSituation::manualOptions()));
        $this->assertArrayHasKey(
            SpiritualSituation::Transferred->value,
            SpiritualSituation::manualOptions(SpiritualSituation::Transferred),
        );
    }

    public function test_the_member_form_shows_communion_when_there_is_no_record(): void
    {
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('secretary'));

        $member = Member::factory()->create();

        Livewire::test(EditMember::class, ['record' => $member->getRouteKey()])
            ->assertSee('Em comunhão')
            ->assertSee('Registros da situação espiritual')
            ->assertSee('Sem registros, o membro permanece em comunhão.')
            ->assertFormFieldExists('spiritualSituations');

        Livewire::test(ListMembers::class)
            ->assertSee($member->name)
            ->assertSee('Em comunhão');
    }

    public function test_the_form_stores_a_discipline_record_with_an_indefinite_period(): void
    {
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('secretary'));

        $brazil = Country::query()->create([
            'name' => 'Brasil',
            'iso_code' => 'BR',
        ]);
        $member = Member::factory()->create([
            'phone' => '11999999999',
            'country_id' => $brazil->id,
        ]);
        $director = Member::factory()->create();

        Livewire::test(EditMember::class, ['record' => $member->getRouteKey()])
            ->fillForm([
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
                'spiritualSituations' => [
                    [
                        'situation' => SpiritualSituation::Disciplined->value,
                        'directed_by_member_id' => $director->id,
                        'recorded_at' => '2026-10-01',
                        'discipline_days' => null,
                        'reason' => 'Ausência reiterada aos cultos',
                    ],
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertNotNull($stored = $member->spiritualSituations()->first());
        $this->assertSame(SpiritualSituation::Disciplined, $stored->situation);
        $this->assertSame($director->id, $stored->directed_by_member_id);
        $this->assertSame('2026-10-01', $stored->recorded_at->toDateString());
        $this->assertNull($stored->discipline_days);
        $this->assertSame('Ausência reiterada aos cultos', $stored->reason);
        $this->assertSame(SpiritualSituation::Disciplined, $member->fresh()->currentSpiritualSituation());
        $this->assertTrue((bool) $member->fresh()->sex);
    }

    public function test_the_form_requires_a_reason_for_discipline_withdrawal_and_church_change(): void
    {
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('secretary'));

        $brazil = Country::query()->create([
            'name' => 'Brasil',
            'iso_code' => 'BR',
        ]);
        $member = Member::factory()->create([
            'phone' => '11999999999',
            'country_id' => $brazil->id,
        ]);
        $director = Member::factory()->create();

        Livewire::test(EditMember::class, ['record' => $member->getRouteKey()])
            ->fillForm($this->memberFormState($member, [
                'situation' => SpiritualSituation::ChangedChurch->value,
                'directed_by_member_id' => $director->id,
                'recorded_at' => '2026-09-01',
                'reason' => '   ',
            ]))
            ->call('save')
            ->assertHasFormErrors(['spiritualSituations.0.reason']);

        $this->assertSame(0, $member->spiritualSituations()->count());

        Livewire::test(EditMember::class, ['record' => $member->getRouteKey()])
            ->fillForm($this->memberFormState($member, [
                'situation' => SpiritualSituation::ChangedChurch->value,
                'directed_by_member_id' => $director->id,
                'recorded_at' => '2026-09-01',
                'reason' => 'Passou a congregar em outra igreja',
            ]))
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Passou a congregar em outra igreja', $member->spiritualSituations()->first()?->reason);
    }

    public function test_the_situation_field_rejects_a_manual_transfer(): void
    {
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('secretary'));

        $brazil = Country::query()->create([
            'name' => 'Brasil',
            'iso_code' => 'BR',
        ]);
        $member = Member::factory()->create([
            'phone' => '11999999999',
            'country_id' => $brazil->id,
        ]);
        $director = Member::factory()->create();

        Livewire::test(EditMember::class, ['record' => $member->getRouteKey()])
            ->fillForm($this->memberFormState($member, [
                'situation' => SpiritualSituation::Transferred->value,
                'directed_by_member_id' => $director->id,
                'recorded_at' => '2026-10-01',
            ]))
            ->call('save')
            ->assertHasFormErrors(['spiritualSituations.0.situation']);

        $this->assertSame(0, $member->spiritualSituations()->count());
        $this->assertSame(SpiritualSituation::InCommunion, $member->fresh()->currentSpiritualSituation());
    }

    public function test_an_existing_transfer_remains_valid_when_the_member_form_is_saved(): void
    {
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('secretary'));

        $brazil = Country::query()->create([
            'name' => 'Brasil',
            'iso_code' => 'BR',
        ]);
        $member = Member::factory()->create([
            'phone' => '11999999999',
            'country_id' => $brazil->id,
        ]);
        $director = Member::factory()->create();
        $situation = $member->spiritualSituations()->create([
            'situation' => SpiritualSituation::Transferred,
            'directed_by_member_id' => $director->id,
            'recorded_at' => '2026-10-01',
        ]);

        Livewire::test(EditMember::class, ['record' => $member->getRouteKey()])
            ->set('data.brazilian_born', 'true')
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(SpiritualSituation::Transferred, $situation->fresh()->situation);
        $this->assertSame(SpiritualSituation::Transferred, $member->fresh()->currentSpiritualSituation());
    }

    public function test_a_reason_is_discarded_when_the_situation_does_not_require_one(): void
    {
        $member = Member::factory()->create();
        $director = Member::factory()->create();

        $record = $member->spiritualSituations()->create([
            'situation' => SpiritualSituation::Transferred,
            'directed_by_member_id' => $director->id,
            'recorded_at' => '2026-07-01',
            'reason' => 'Este motivo não se aplica',
        ]);

        $this->assertNull($record->fresh()->reason);

        $this->expectException(ValidationException::class);

        $member->spiritualSituations()->create([
            'situation' => SpiritualSituation::Withdrew,
            'directed_by_member_id' => $director->id,
            'recorded_at' => '2026-07-02',
            'reason' => '   ',
        ]);
    }

    /**
     * @param  array<string, mixed>  $occurrence
     * @return array<string, mixed>
     */
    private function memberFormState(Member $member, array $occurrence): array
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
            'spiritualSituations' => [$occurrence],
        ];
    }
}
