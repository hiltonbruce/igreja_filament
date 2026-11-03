<?php

namespace App\Support;

use App\Enums\BrazilRegion;
use App\Enums\ChurchLevel;
use App\Models\Church;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ChurchHierarchy
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function validate(array $data, ?Church $current = null): array
    {
        $data = self::normalize($data);
        $level = ChurchLevel::tryFrom((string) ($data['level'] ?? ''));
        $isBrazil = self::isBrazil($data['country_id'] ?? null);

        $validator = Validator::make($data, self::rules($data, $current));

        $validator->after(function ($validator) use ($data, $level, $current, $isBrazil): void {
            if ($level === null) {
                return;
            }

            if ($level === ChurchLevel::Regional && ! $isBrazil && filled($data['region'] ?? null)) {
                $validator->errors()->add('region', __('custom.Brazil region applies only to churches in Brazil.'));
            }

            self::assertParentCompatibility($validator, $data, $level, $current);
            self::assertGeographicCoherence($validator, $data, $level, $isBrazil);
        });

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validated = $validator->validated();

        if (! $isBrazil) {
            $validated['region'] = null;
        }

        return $validated;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function normalize(array $data): array
    {
        if (($data['level'] ?? null) instanceof ChurchLevel) {
            $data['level'] = $data['level']->value;
        }

        if (($data['region'] ?? null) instanceof BrazilRegion) {
            $data['region'] = $data['region']->value;
        }

        if (($data['region'] ?? null) === '') {
            $data['region'] = null;
        }

        return $data;
    }

    public static function brazilCountryId(): ?int
    {
        $id = Country::query()->where('iso_code', 'BR')->value('id');

        return $id !== null ? (int) $id : null;
    }

    public static function isBrazil(mixed $countryId): bool
    {
        if (! filled($countryId)) {
            return false;
        }

        $brazilId = self::brazilCountryId();

        return $brazilId !== null && (int) $countryId === $brazilId;
    }

    public static function isForeign(mixed $countryId): bool
    {
        return filled($countryId) && ! self::isBrazil($countryId);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, list<mixed>>
     */
    public static function rules(array $data = [], ?Church $current = null): array
    {
        $data = self::normalize($data);
        $level = ChurchLevel::tryFrom((string) ($data['level'] ?? ''));
        $isBrazil = self::isBrazil($data['country_id'] ?? null);

        return [
            'name' => ['required', 'string', 'max:255'],
            'level' => ['required', Rule::enum(ChurchLevel::class)],
            'denomination' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'area_code' => ['nullable', 'string', 'max:5'],
            'timezone' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'string', 'max:10'],
            'longitude' => ['nullable', 'string', 'max:10'],
            'sector' => ['nullable', 'integer'],
            'country_id' => [
                Rule::requiredIf(fn (): bool => $level?->requiresCountry() ?? false),
                'nullable',
                'integer',
                'exists:countries,id',
            ],
            'state_id' => [
                Rule::requiredIf(fn (): bool => $level?->requiresState() ?? false),
                'nullable',
                'integer',
                'exists:states,id',
            ],
            'city_id' => [
                Rule::requiredIf(fn (): bool => $level?->requiresCity() ?? false),
                'nullable',
                'integer',
                'exists:cities,id',
            ],
            'neighborhood_id' => ['nullable', 'integer', 'exists:neighborhoods,id'],
            'region' => [
                Rule::requiredIf(fn (): bool => $level?->requiresBrazilRegion($isBrazil) ?? false),
                'nullable',
                Rule::enum(BrazilRegion::class),
            ],
            'parent_id' => array_values(array_filter([
                Rule::requiredIf(fn (): bool => $level === ChurchLevel::Congregation),
                'nullable',
                'integer',
                Rule::exists('churches', 'id')->whereNull('deleted_at'),
                $current !== null ? Rule::notIn([(string) $current->id]) : null,
            ])),
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    /**
     * @param  \Illuminate\Validation\Validator  $validator
     * @param  array<string, mixed>  $data
     */
    protected static function assertParentCompatibility($validator, array $data, ChurchLevel $level, ?Church $current): void
    {
        $parentId = $data['parent_id'] ?? null;

        if ($parentId === null || $parentId === '') {
            if (! $level->allowsEmptyParent()) {
                $validator->errors()->add('parent_id', __('custom.A congregation must belong to a municipal headquarters.'));
            }

            return;
        }

        $parent = Church::query()->find($parentId);

        if ($parent === null) {
            return;
        }

        if ($current !== null && self::wouldCreateCycle($current, $parent)) {
            $validator->errors()->add('parent_id', __('custom.A church cannot be its own ancestor.'));

            return;
        }

        $allowed = $level->allowedParentLevels();

        if ($allowed === [] || ! in_array($parent->level, $allowed, true)) {
            $validator->errors()->add('parent_id', __('custom.The selected parent is not valid for this church level.'));
        }
    }

    /**
     * @param  \Illuminate\Validation\Validator  $validator
     * @param  array<string, mixed>  $data
     */
    protected static function assertGeographicCoherence($validator, array $data, ChurchLevel $level, bool $isBrazil): void
    {
        $stateId = isset($data['state_id']) ? (int) $data['state_id'] : null;
        $cityId = isset($data['city_id']) ? (int) $data['city_id'] : null;
        $parentId = isset($data['parent_id']) ? (int) $data['parent_id'] : null;
        $countryId = isset($data['country_id']) ? (int) $data['country_id'] : null;
        $region = BrazilRegion::tryFrom((string) ($data['region'] ?? ''));

        $state = $stateId ? State::query()->find($stateId) : null;
        $city = $cityId ? City::query()->find($cityId) : null;
        $parent = $parentId ? Church::query()->find($parentId) : null;

        if ($state !== null && $countryId !== null && (int) $state->country_id !== $countryId) {
            $validator->errors()->add('state_id', __('custom.The selected state does not belong to the selected country.'));
        }

        if ($city !== null && $countryId !== null && (int) $city->country_id !== $countryId) {
            $validator->errors()->add('city_id', __('custom.The selected city does not belong to the selected country.'));
        }

        if ($city !== null && $state !== null && (int) $city->state_id !== (int) $state->id) {
            $validator->errors()->add('city_id', __('custom.The selected city does not belong to the selected state.'));
        }

        if ($isBrazil && $level === ChurchLevel::Regional && $region !== null && $state !== null && $state->region !== $region) {
            $validator->errors()->add('state_id', __('custom.The selected state does not belong to the selected region.'));
        }

        if ($parent === null) {
            return;
        }

        if ($isBrazil && $parent->level === ChurchLevel::Regional && $parent->region !== null) {
            if ($state !== null && $state->region !== $parent->region) {
                $validator->errors()->add('state_id', __('custom.The selected state does not belong to the parent region.'));
            }
        }

        if ($parent->level === ChurchLevel::State && $parent->state_id !== null && $state !== null) {
            if ((int) $parent->state_id !== (int) $state->id) {
                $validator->errors()->add('state_id', __('custom.The selected state must match the parent state headquarters.'));
            }
        }

        if ($level === ChurchLevel::Congregation && $parent->level === ChurchLevel::Municipal) {
            if ($parent->city_id !== null && $city !== null && (int) $parent->city_id !== (int) $city->id) {
                $validator->errors()->add('city_id', __('custom.A congregation must stay in the same city as its municipal headquarters.'));
            }

            if ($parent->state_id !== null && $state !== null && (int) $parent->state_id !== (int) $state->id) {
                $validator->errors()->add('state_id', __('custom.A congregation must stay in the same state as its municipal headquarters.'));
            }
        }
    }

    protected static function wouldCreateCycle(Church $current, Church $parent): bool
    {
        if ($parent->is($current)) {
            return true;
        }

        $ancestor = $parent;

        while ($ancestor->parent_id !== null) {
            if ((int) $ancestor->parent_id === (int) $current->id) {
                return true;
            }

            $ancestor = $ancestor->parent;

            if ($ancestor === null) {
                break;
            }
        }

        return false;
    }
}
