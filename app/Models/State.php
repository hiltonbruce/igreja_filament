<?php

namespace App\Models;

use App\Enums\BrazilRegion;
use Database\Factories\StateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class State extends Model
{
    /** @use HasFactory<StateFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'abbreviation',
        'region',
        'country_id',
        'city_id',
        'user_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'region' => BrazilRegion::class,
        ];
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
