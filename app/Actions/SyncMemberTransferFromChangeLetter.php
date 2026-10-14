<?php

namespace App\Actions;

use App\Enums\LetterType;
use App\Enums\SpiritualSituation;
use App\Models\Letter;
use App\Models\MemberSpiritualSituation;

class SyncMemberTransferFromChangeLetter
{
    public function handle(Letter $letter): void
    {
        if ($letter->type !== LetterType::Change) {
            $this->discard($letter);

            return;
        }

        $this->sync($letter);
    }

    public function discardOnDelete(Letter $letter): void
    {
        $letter->transferSituation()->first()?->delete();
    }

    private function sync(Letter $letter): void
    {
        $attributes = [
            'situation' => SpiritualSituation::Transferred,
            'directed_by_member_id' => $letter->directed_by_member_id,
            'recorded_at' => $letter->issued_at,
        ];

        $situation = $letter->transferSituation()->first();

        if ($situation instanceof MemberSpiritualSituation) {
            $situation->fill($attributes)->save();

            return;
        }

        $situation = $letter->member->spiritualSituations()->create($attributes);

        $letter->member_spiritual_situation_id = $situation->getKey();
        $letter->unsetRelation('transferSituation');
        $letter->saveQuietly();
    }

    private function discard(Letter $letter): void
    {
        $situation = $letter->transferSituation()->first();

        if (! $situation instanceof MemberSpiritualSituation && $letter->member_spiritual_situation_id === null) {
            return;
        }

        $letter->member_spiritual_situation_id = null;
        $letter->unsetRelation('transferSituation');
        $letter->saveQuietly();
        $situation?->delete();
    }
}
