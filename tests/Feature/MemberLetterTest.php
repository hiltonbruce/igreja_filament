<?php

namespace Tests\Feature;

use App\Enums\LetterType;
use App\Enums\SpiritualSituation;
use App\Filament\Secretary\Resources\Letters\Pages\ManageLetters;
use App\Filament\Secretary\Resources\Members\Pages\EditMember;
use App\Models\Country;
use App\Models\Letter;
use App\Models\Member;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MemberLetterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('pt_BR');
    }

    public function test_recommendation_and_in_transit_letters_do_not_change_the_spiritual_situation(): void
    {
        $member = Member::factory()->create();
        $director = Member::factory()->create();

        Letter::factory()->create([
            'member_id' => $member->id,
            'type' => LetterType::Recommendation,
            'directed_by_member_id' => $director->id,
            'destination' => 'Congregação Central',
        ]);

        Letter::factory()->create([
            'member_id' => $member->id,
            'type' => LetterType::InTransit,
            'directed_by_member_id' => $director->id,
            'destination' => 'Congregação do Jardim',
        ]);

        $this->assertSame(0, $member->spiritualSituations()->count());
        $this->assertSame(SpiritualSituation::InCommunion, $member->fresh()->currentSpiritualSituation());
    }

    public function test_a_change_letter_transfers_the_member_and_keeps_a_single_record(): void
    {
        $member = Member::factory()->create();
        $director = Member::factory()->create();

        $member->spiritualSituations()->create([
            'situation' => SpiritualSituation::InCommunion,
            'directed_by_member_id' => $director->id,
            'recorded_at' => '2026-01-01',
        ]);

        $letter = Letter::factory()->change()->create([
            'member_id' => $member->id,
            'directed_by_member_id' => $director->id,
            'issued_at' => '2026-10-01',
            'destination' => 'Congregação Nova',
        ]);

        $letter->refresh();

        $this->assertNotNull($letter->transferSituation);
        $this->assertSame(SpiritualSituation::Transferred, $letter->transferSituation->situation);
        $this->assertSame($director->id, $letter->transferSituation->directed_by_member_id);
        $this->assertSame('2026-10-01', $letter->transferSituation->recorded_at->toDateString());
        $this->assertSame(SpiritualSituation::Transferred, $member->fresh()->currentSpiritualSituation());
        $this->assertSame(2, $member->spiritualSituations()->count());

        $letter->update(['issued_at' => '2026-10-20']);

        $this->assertSame(2, $member->spiritualSituations()->count());
        $this->assertSame('2026-10-20', $letter->fresh()->transferSituation->recorded_at->toDateString());
    }

    public function test_changing_or_deleting_a_change_letter_removes_only_its_transfer(): void
    {
        $member = Member::factory()->create();
        $director = Member::factory()->create();

        $member->spiritualSituations()->create([
            'situation' => SpiritualSituation::InCommunion,
            'directed_by_member_id' => $director->id,
            'recorded_at' => '2026-01-01',
        ]);

        $corrected = Letter::factory()->change()->create([
            'member_id' => $member->id,
            'directed_by_member_id' => $director->id,
            'issued_at' => '2026-08-01',
        ]);

        $corrected->update(['type' => LetterType::Recommendation]);

        $this->assertNull($corrected->fresh()->member_spiritual_situation_id);
        $this->assertSame(SpiritualSituation::InCommunion, $member->fresh()->currentSpiritualSituation());

        $removed = Letter::factory()->change()->create([
            'member_id' => $member->id,
            'directed_by_member_id' => $director->id,
            'issued_at' => '2026-09-01',
        ]);

        $removed->delete();

        $this->assertSame(1, $member->spiritualSituations()->count());
        $this->assertSame(SpiritualSituation::InCommunion, $member->fresh()->currentSpiritualSituation());
    }

    public function test_the_member_form_stores_a_change_letter_and_transfers_the_member(): void
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
                'letters' => [
                    [
                        'type' => LetterType::Change->value,
                        'issued_at' => '2026-10-13',
                        'directed_by_member_id' => $director->id,
                        'destination' => 'Congregação do Centro',
                    ],
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $letter = $member->letters()->first();

        $this->assertNotNull($letter);
        $this->assertSame(LetterType::Change, $letter->type);
        $this->assertSame('Congregação do Centro', $letter->destination);
        $this->assertSame(SpiritualSituation::Transferred, $letter->transferSituation?->situation);
        $this->assertSame(SpiritualSituation::Transferred, $member->fresh()->currentSpiritualSituation());
    }

    public function test_the_letters_resource_creates_a_change_letter_and_transfers_the_member(): void
    {
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('secretary'));

        $member = Member::factory()->create();
        $director = Member::factory()->create();

        Livewire::test(ManageLetters::class)
            ->callAction('create', data: [
                'member_id' => $member->id,
                'type' => LetterType::Change->value,
                'issued_at' => '2026-10-13',
                'directed_by_member_id' => $director->id,
                'destination' => 'Congregação do Centro',
            ])
            ->assertHasNoActionErrors()
            ->assertSee('Congregação do Centro')
            ->assertSee('Mudança');

        $this->assertSame(SpiritualSituation::Transferred, $member->fresh()->currentSpiritualSituation());
    }

    public function test_the_letters_resource_recommendation_does_not_transfer_the_member(): void
    {
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('secretary'));

        $member = Member::factory()->create();
        $director = Member::factory()->create();

        Livewire::test(ManageLetters::class)
            ->callAction('create', data: [
                'member_id' => $member->id,
                'type' => LetterType::Recommendation->value,
                'issued_at' => '2026-10-13',
                'directed_by_member_id' => $director->id,
                'destination' => 'Congregação da Vila',
            ])
            ->assertHasNoActionErrors();

        $this->assertSame(SpiritualSituation::InCommunion, $member->fresh()->currentSpiritualSituation());
    }
}
