<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $response = Http::timeout(30)
            ->acceptJson()
            ->get('https://restcountries.com/v3.1/all?fields=name,translations,cca2,idd,currencies');

        if (! $response->ok()) {
            throw new RuntimeException('Falha ao buscar países na API pública.');
        }

        $countries = collect($response->json())
            ->map(function (array $country): ?array {
                $isoCode = strtoupper((string) ($country['cca2'] ?? ''));
                $name = trim((string) ($country['translations']['por']['common'] ?? $country['name']['common'] ?? ''));

                if ($isoCode === '' || $name === '') {
                    return null;
                }

                $rootPhoneCode = trim((string) ($country['idd']['root'] ?? ''));
                $suffixPhoneCode = (string) collect($country['idd']['suffixes'] ?? [])->first();
                $composedPhoneCode = trim($rootPhoneCode.$suffixPhoneCode);
                $phoneCode = $composedPhoneCode !== '' ? $composedPhoneCode : null;
                $currency = (string) collect(array_keys($country['currencies'] ?? []))->first();

                return [
                    'name' => $name,
                    'iso_code' => $isoCode,
                    'phone_code' => $phoneCode,
                    'currency' => $currency !== '' ? strtoupper($currency) : null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ];
            })
            ->filter()
            ->values()
            ->all();

        Country::query()->upsert(
            $countries,
            ['iso_code'],
            ['name', 'phone_code', 'currency', 'updated_at']
        );
    }
}
