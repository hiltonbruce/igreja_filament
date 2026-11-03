<?php

namespace App\Models;

use Database\Factories\CityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model
{
    /** @use HasFactory<CityFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'state_id',
        'country_id',
        'capital_city',
        'ibge_code',
        'latitude',
        'longitude',
        'timezone',
        'area_code',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'capital_city' => 'boolean',
        ];
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function neighborhoods(): HasMany
    {
        return $this->hasMany(Neighborhood::class);
    }
}
