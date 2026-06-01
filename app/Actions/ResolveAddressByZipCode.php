<?php

namespace App\Actions;

use App\Models\City;
use App\Models\Country;
use App\Models\Neighborhood;
use App\Models\State;
use App\Models\Street;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Throwable;

class ResolveAddressByZipCode
{
    /**
     * Resolve a CEP into a normalized address payload.
     *
     * The lookup is performed against the local database first and, when not
     * found, falls back to BrasilAPI (v1 then v2). Any missing related records
     * (country, state, city, neighborhood and street) are created or updated.
     *
     * @return array{
     *     zip_code: string,
     *     country_id: int,
     *     state_id: int,
     *     city_id: int,
     *     neighborhood_id: int,
     *     street_id: int,
     *     address: string,
     *     latitude: ?string,
     *     longitude: ?string,
     * }|null
     */
    public function handle(?string $zipCode): ?array
    {
        $sanitizedZipCode = $this->sanitize($zipCode);

        if ($sanitizedZipCode === null) {
            return null;
        }

        $localStreet = $this->findLocalStreet($sanitizedZipCode);

        if ($localStreet instanceof Street) {
            return $this->present($localStreet);
        }

        $payload = $this->fetchFromBrasilApi($sanitizedZipCode);

        if ($payload === null) {
            return null;
        }

        return $this->present($this->persist($sanitizedZipCode, $payload));
    }

    /**
     * Normalize a CEP to 8 digits. A single leading zero is restored when the
     * value came from an integer column (e.g. "1001000" -> "01001000"). Since
     * the lowest Brazilian CEP is 01000-000, valid values always have 7 or 8
     * digits once non-numeric characters are stripped.
     */
    private function sanitize(?string $zipCode): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $zipCode);
        $length = strlen((string) $digits);

        if ($length !== 7 && $length !== 8) {
            return null;
        }

        return str_pad((string) $digits, 8, '0', STR_PAD_LEFT);
    }

    private function findLocalStreet(string $zipCode): ?Street
    {
        return Street::query()
            ->with(['neighborhood', 'city', 'state', 'country'])
            ->where('zip_code', $zipCode)
            ->first();
    }

    /**
     * Query BrasilAPI, preferring v2 (which also provides geolocation data)
     * and falling back to v1.
     *
     * @return array<string, mixed>|null
     */
    private function fetchFromBrasilApi(string $zipCode): ?array
    {
        foreach (['v2', 'v1'] as $version) {
            try {
                $response = Http::timeout(15)
                    ->acceptJson()
                    ->get("https://brasilapi.com.br/api/cep/{$version}/{$zipCode}");
            } catch (Throwable) {
                continue;
            }

            if (! $response->successful()) {
                continue;
            }

            $data = $response->json();

            if (is_array($data) && ! empty($data['street'] ?? null)) {
                return $data;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function persist(string $zipCode, array $payload): Street
    {
        $userId = Auth::id();

        $country = $this->resolveCountry();
        $state = $this->resolveState($payload, $country, $userId);
        $city = $this->resolveCity($payload, $state, $country, $userId);
        $neighborhood = $this->resolveNeighborhood($payload, $city, $state, $country, $userId);

        [$latitude, $longitude] = $this->extractCoordinates($payload);

        $street = Street::query()->firstOrNew([
            'zip_code' => $zipCode,
        ]);

        $street->fill([
            'name' => (string) ($payload['street'] ?? $street->name),
            'neighborhood_id' => $neighborhood->id,
            'city_id' => $city->id,
            'state_id' => $state->id,
            'country_id' => $country->id,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);

        if ($street->user_id === null) {
            $street->user_id = $userId;
        }

        $street->save();
        $street->setRelations([
            'neighborhood' => $neighborhood,
            'city' => $city,
            'state' => $state,
            'country' => $country,
        ]);

        return $street;
    }

    private function resolveCountry(): Country
    {
        return Country::query()->where('iso_code', 'BR')->firstOrFail();
    }

    /**
     * Resolve the state from the local database. When it does not exist yet,
     * its full name is fetched from the BrasilAPI IBGE endpoint and persisted.
     *
     * @param  array<string, mixed>  $payload
     */
    private function resolveState(array $payload, Country $country, ?int $userId): State
    {
        $abbreviation = strtoupper((string) ($payload['state'] ?? ''));

        $state = State::query()->where('abbreviation', $abbreviation)->first();

        if ($state instanceof State) {
            return $state;
        }

        return State::query()->create([
            'name' => $this->fetchStateName($abbreviation),
            'abbreviation' => $abbreviation,
            'country_id' => $country->id,
            'user_id' => $userId,
        ]);
    }

    /**
     * Fetch the full state name from the BrasilAPI IBGE endpoint, falling back
     * to the abbreviation when the request cannot be completed.
     */
    private function fetchStateName(string $abbreviation): string
    {
        try {
            $response = Http::timeout(15)
                ->acceptJson()
                ->get("https://brasilapi.com.br/api/ibge/uf/v1/{$abbreviation}");
        } catch (Throwable) {
            return $abbreviation;
        }

        if (! $response->successful()) {
            return $abbreviation;
        }

        $name = (string) $response->json('nome');

        return $name !== '' ? $name : $abbreviation;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function resolveCity(array $payload, State $state, Country $country, ?int $userId): City
    {
        return City::query()->firstOrCreate(
            [
                'name' => (string) ($payload['city'] ?? ''),
                'state_id' => $state->id,
            ],
            [
                'country_id' => $country->id,
                'user_id' => $userId,
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function resolveNeighborhood(array $payload, City $city, State $state, Country $country, ?int $userId): Neighborhood
    {
        return Neighborhood::query()->firstOrCreate(
            [
                'name' => (string) ($payload['neighborhood'] ?? ''),
                'city_id' => $city->id,
            ],
            [
                'state_id' => $state->id,
                'country_id' => $country->id,
                'user_id' => $userId,
            ],
        );
    }

    /**
     * Extract latitude/longitude from a BrasilAPI v2 payload. Returns nulls
     * when the data is unavailable (e.g. a v1 response).
     *
     * @param  array<string, mixed>  $payload
     * @return array{0: ?string, 1: ?string}
     */
    private function extractCoordinates(array $payload): array
    {
        $coordinates = $payload['location']['coordinates'] ?? [];

        $latitude = $coordinates['latitude'] ?? null;
        $longitude = $coordinates['longitude'] ?? null;

        return [
            ($latitude === null || $latitude === '') ? null : (string) $latitude,
            ($longitude === null || $longitude === '') ? null : (string) $longitude,
        ];
    }

    /**
     * @return array{
     *     zip_code: string,
     *     country_id: int,
     *     state_id: int,
     *     city_id: int,
     *     neighborhood_id: int,
     *     street_id: int,
     *     address: string,
     *     latitude: ?string,
     *     longitude: ?string,
     * }
     */
    private function present(Street $street): array
    {
        return [
            'zip_code' => (string) $street->zip_code,
            'country_id' => (int) $street->country_id,
            'state_id' => (int) $street->state_id,
            'city_id' => (int) $street->city_id,
            'neighborhood_id' => (int) $street->neighborhood_id,
            'street_id' => (int) $street->id,
            'address' => (string) $street->name,
            'latitude' => $street->latitude !== null ? (string) $street->latitude : null,
            'longitude' => $street->longitude !== null ? (string) $street->longitude : null,
        ];
    }
}
