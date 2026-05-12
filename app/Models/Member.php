<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use SoftDeletes;

    public function memberPhotos(): HasMany
    {
        return $this->hasMany(MemberPhoto::class)->orderByDesc('created_at');
    }
}
