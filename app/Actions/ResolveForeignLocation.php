<?php

namespace App\Actions;

use App\Models\City;
use App\Models\State;
use Illuminate\Support\Facades\Auth;

class ResolveForeignLocation
{
    /**
     * Resolve a free-text state/province name into a State record for the
     * given country, creating it when it does not exist yet. Foreign states
     * have no UF, so the abbreviation stays null.
     */
    public function resolveState(?int $countryId, ?string $name): ?int
    {
        $name = trim((string) $name);

        if ($countryId === null || $name === '') {
            return null;
        }

        $state = State::query()->firstOrCreate(
            [
                'name' => $name,
                'country_id' => $countryId,
            ],
            [
                'user_id' => Auth::id(),
            ],
        );

        return $state->id;
    }

    /**
     * Resolve a free-text city name into a City record for the given state and
     * country, creating it when it does not exist yet.
     */
    public function resolveCity(?int $countryId, ?int $stateId, ?string $name): ?int
    {
        $name = trim((string) $name);

        if ($countryId === null || $stateId === null || $name === '') {
            return null;
        }

        $city = City::query()->firstOrCreate(
            [
                'name' => $name,
                'state_id' => $stateId,
            ],
            [
                'country_id' => $countryId,
                'user_id' => Auth::id(),
            ],
        );

        return $city->id;
    }
}
