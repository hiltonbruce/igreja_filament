<?php

namespace App\Models;

use App\Enums\BrazilRegion;
use App\Enums\ChurchLevel;
use Database\Factories\ChurchFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Church extends Model
{
    /** @use HasFactory<ChurchFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'level',
        'region',
        'denomination',
        'address',
        'phone',
        'email',
        'website',
        'country_id',
        'state_id',
        'city_id',
        'neighborhood_id',
        'parent_id',
        'sector',
        'timezone',
        'latitude',
        'longitude',
        'area_code',
        'postal_code',
        'user_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'level' => ChurchLevel::class,
            'region' => BrazilRegion::class,
            'sector' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function congregations(): HasMany
    {
        return $this->children()->where('level', ChurchLevel::Congregation);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function neighborhood(): BelongsTo
    {
        return $this->belongsTo(Neighborhood::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    #[Scope]
    protected function ofLevel(Builder $query, ChurchLevel $level): void
    {
        $query->where('level', $level);
    }

    #[Scope]
    protected function municipal(Builder $query): void
    {
        $query->where('level', ChurchLevel::Municipal);
    }
}
