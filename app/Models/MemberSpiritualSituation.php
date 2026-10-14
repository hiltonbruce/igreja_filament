<?php

namespace App\Models;

use App\Enums\SpiritualSituation;
use Database\Factories\MemberSpiritualSituationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class MemberSpiritualSituation extends Model
{
    /** @use HasFactory<MemberSpiritualSituationFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'member_id',
        'situation',
        'directed_by_member_id',
        'recorded_at',
        'discipline_days',
        'reason',
    ];

    protected static function booted(): void
    {
        static::saving(function (MemberSpiritualSituation $record): void {
            if ($record->situation !== SpiritualSituation::Disciplined) {
                $record->discipline_days = null;
            }

            $record->reason = filled($record->reason) ? trim((string) $record->reason) : null;

            if (! $record->situation?->requiresReason()) {
                $record->reason = null;

                return;
            }

            if ($record->reason === null) {
                throw ValidationException::withMessages([
                    'reason' => __('custom.The reason is required for this spiritual situation.'),
                ]);
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'situation' => SpiritualSituation::class,
            'recorded_at' => 'date',
            'discipline_days' => 'integer',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function directedBy(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'directed_by_member_id');
    }

    /**
     * Situação vigente entre os itens ainda não persistidos do formulário.
     * Sem itens válidos, o membro permanece em comunhão.
     *
     * @param  array<int|string, mixed>  $items
     */
    public static function latestFromStates(array $items): SpiritualSituation
    {
        $latest = null;
        $latestKey = '';

        foreach ($items as $index => $item) {
            if (! is_array($item)) {
                continue;
            }

            $situation = SpiritualSituation::fromMixed($item['situation'] ?? null);

            if ($situation === null) {
                continue;
            }

            $key = self::stateSortKey($item, (string) $index);

            if ($latest === null || $key > $latestKey) {
                $latest = $situation;
                $latestKey = $key;
            }
        }

        return $latest ?? SpiritualSituation::InCommunion;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private static function stateSortKey(array $item, string $index): string
    {
        $recordedAt = $item['recorded_at'] ?? '';

        if ($recordedAt instanceof \DateTimeInterface) {
            $recordedAt = Carbon::parse($recordedAt)->toDateString();
        }

        $idPart = filled($item['id'] ?? null)
            ? str_pad((string) $item['id'], 20, '0', STR_PAD_LEFT)
            : 'z'.str_pad($index, 8, '0', STR_PAD_LEFT);

        return ((string) $recordedAt).'|'.$idPart;
    }
}
