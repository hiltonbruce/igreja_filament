<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberPhoto extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'member_id',
        'path',
        'disk',
        'superseded_by_user_id',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function supersededBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'superseded_by_user_id');
    }
}
