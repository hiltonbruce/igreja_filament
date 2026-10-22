<?php

namespace App\Models;

use App\Enums\SpiritualSituation;
use Database\Factories\MemberFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    /** @use HasFactory<MemberFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'birth_date',
        'city_of_birth_id',
        'state_of_birth_id',
        'country_of_birth_id',
        'sex',
        'donor',
        'blood_type',
        'photo',
        'mother_name',
        'mother_member_id',
        'father_name',
        'father_member_id',
        'matrimonial_status',
        'baptized_in_spirit',
        'baptized_in_spirit_date',
        'baptized',
        'city_id',
        'state_id',
        'country_id',
        'neighborhood_id',
        'number',
        'complement',
        'document',
        'document_type',
        'email',
        'phone',
        'address',
        'zip_code',
        'user_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'sex' => 'boolean',
            'donor' => 'boolean',
            'matrimonial_status' => 'boolean',
            'baptized_in_spirit' => 'boolean',
            'baptized_in_spirit_date' => 'date',
            'baptized' => 'date',
        ];
    }

    /**
     * @return Collection<int, static>
     */
    public static function searchBySexAndName(string $search, bool $male, ?int $exceptId = null): Collection
    {
        $search = trim($search);

        if (mb_strlen($search) < 2) {
            return new Collection;
        }

        $term = '%'.addcslashes(mb_strtolower($search), '%_\\').'%';

        return static::query()
            ->where('sex', $male)
            ->when($exceptId, fn (Builder $query): Builder => $query->whereKeyNot($exceptId))
            ->whereRaw('LOWER(name) LIKE ?', [$term])
            ->orderBy('name')
            ->limit(8)
            ->get();
    }

    public function father(): BelongsTo
    {
        return $this->belongsTo(self::class, 'father_member_id');
    }

    public function mother(): BelongsTo
    {
        return $this->belongsTo(self::class, 'mother_member_id');
    }

    public function memberPhotos(): HasMany
    {
        return $this->hasMany(MemberPhoto::class)->orderByDesc('created_at');
    }

    public function spiritualSituations(): HasMany
    {
        return $this->hasMany(MemberSpiritualSituation::class)
            ->orderByDesc('recorded_at')
            ->orderByDesc('id');
    }

    public function latestSpiritualSituation(): HasOne
    {
        return $this->hasOne(MemberSpiritualSituation::class)->latestOfMany([
            'recorded_at',
            'id',
        ]);
    }

    public function currentSpiritualSituation(): SpiritualSituation
    {
        return $this->latestSpiritualSituation?->situation ?? SpiritualSituation::InCommunion;
    }

    public function letters(): HasMany
    {
        return $this->hasMany(Letter::class)
            ->orderByDesc('issued_at')
            ->orderByDesc('id');
    }
}
