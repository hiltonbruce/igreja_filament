<?php

namespace App\Observers;

use App\Models\Member;
use App\Models\MemberPhoto;
use Illuminate\Support\Facades\Auth;

class MemberObserver
{
    public function updating(Member $member): void
    {
        if (! $member->isDirty('photo')) {
            return;
        }

        $previous = $member->getOriginal('photo');

        if (! filled($previous)) {
            return;
        }

        MemberPhoto::query()->create([
            'member_id' => $member->getKey(),
            'path' => $previous,
            'disk' => 'local',
            'superseded_by_user_id' => Auth::id(),
        ]);
    }
}
