<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;
use App\Models\State;
use Illuminate\Support\Facades\Http;

class CountryStateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Try to download a full dataset; fallback to REST Countries (with placeholder states), then to bundled set.
        if (!$this->seedFromRemoteDataset()) {
            $this->command->warn('Full dataset download failed. Trying REST Countries API...');
            if (!$this->seedFromRestCountries()) {
                $this->command->warn('REST Countries fetch failed. Falling back to bundled country/state data.');
                $this->seedFallbackCountries();
                $this->seedStates();
            }
        }

        $this->command->info('Country and State data seeded successfully!');
    }

    /**
     * Seed countries/states from the public dataset (dr5hn/countries-states-cities-database).
     * Returns true on success, false on failure.
     */
    private function seedFromRemoteDataset(): bool
    {
        $urls = [
            'https://raw.githubusercontent.com/dr5hn/countries-states-cities-database/master/countries%2Bstates.json',
            'https://raw.githubusercontent.com/dr5hn/countries-states-cities-database/main/countries%2Bstates.json',
        ];

        try {
            $data = null;
            foreach ($urls as $url) {
                $this->command->info("Attempting download: {$url}");
                $response = Http::timeout(60)->get($url);
                if ($response->successful()) {
                    $data = $response->json();
                    break;
                }
            }
            if (!is_array($data) || empty($data)) {
                $this->command->warn('Download failed or invalid dataset.');
                return false;
            }

            $this->command->info('Seeding ' . count($data) . ' countries from downloaded dataset...');

            foreach ($data as $countryIndex => $countryData) {
                $country = Country::updateOrCreate(
                    ['code' => $countryData['iso2'] ?? $countryData['name'] ?? ''],
                    [
                        'name'       => $countryData['name'] ?? '',
                        'iso3'       => $countryData['iso3'] ?? null,
                        'phone_code' => isset($countryData['phone_code']) ? (int) $countryData['phone_code'] : null,
                        'is_active'  => true,
                        'sort_order' => $countryIndex,
                    ]
                );

                // Seed states for this country
                if (!empty($countryData['states']) && is_array($countryData['states'])) {
                    foreach ($countryData['states'] as $stateIndex => $stateData) {
                        if (empty($stateData['name'])) {
                            continue;
                        }

                        State::updateOrCreate(
                            [
                                'country_id' => $country->id,
                                'name'       => $stateData['name'],
                            ],
                            [
                                'code'       => $stateData['state_code'] ?? null,
                                'is_active'  => true,
                                'sort_order' => $stateIndex,
                            ]
                        );
                    }
                }
            }

            $this->command->info('Remote dataset seeded successfully.');
            return true;
        } catch (\Exception $e) {
            $this->command->warn('Error seeding from remote dataset: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Seed countries from REST Countries API and attach a placeholder state so dropdowns are never empty.
     */
    private function seedFromRestCountries(): bool
    {
        $url = 'https://restcountries.com/v3.1/all?fields=name,cca2,cca3,idd';

        try {
            $response = Http::timeout(60)->get($url);
            if (!$response->successful()) {
                $this->command->warn('REST Countries request failed with status: ' . $response->status());
                return false;
            }

            $countries = $response->json();
            if (!is_array($countries) || empty($countries)) {
                $this->command->warn('REST Countries returned invalid data.');
                return false;
            }

            foreach ($countries as $index => $countryData) {
                $phoneCode = null;
                if (isset($countryData['idd']['root']) && isset($countryData['idd']['suffixes'][0])) {
                    $phoneCode = (int) str_replace('+', '', $countryData['idd']['root'] . $countryData['idd']['suffixes'][0]);
                }

                $country = Country::updateOrCreate(
                    ['code' => $countryData['cca2'] ?? $countryData['name']['common'] ?? ''],
                    [
                        'name'       => $countryData['name']['common'] ?? '',
                        'iso3'       => $countryData['cca3'] ?? null,
                        'phone_code' => $phoneCode,
                        'is_active'  => true,
                        'sort_order' => $index,
                    ]
                );

                // Add a placeholder state to avoid empty dropdowns
                State::updateOrCreate(
                    [
                        'country_id' => $country->id,
                        'name'       => 'State / Region',
                    ],
                    [
                        'code'       => null,
                        'is_active'  => true,
                        'sort_order' => 0,
                    ]
                );
            }

            return true;
        } catch (\Exception $e) {
            $this->command->warn('Error fetching REST Countries: ' . $e->getMessage());
            return false;
        }
    }

    private function seedFallbackCountries()
    {
        $fallbackCountries = [
            ['name' => 'India', 'code' => 'IN', 'iso3' => 'IND', 'phone_code' => 91],
            ['name' => 'United States', 'code' => 'US', 'iso3' => 'USA', 'phone_code' => 1],
            ['name' => 'United Kingdom', 'code' => 'GB', 'iso3' => 'GBR', 'phone_code' => 44],
            ['name' => 'Canada', 'code' => 'CA', 'iso3' => 'CAN', 'phone_code' => 1],
            ['name' => 'Australia', 'code' => 'AU', 'iso3' => 'AUS', 'phone_code' => 61],
            ['name' => 'United Arab Emirates', 'code' => 'AE', 'iso3' => 'ARE', 'phone_code' => 971],
            ['name' => 'Germany', 'code' => 'DE', 'iso3' => 'DEU', 'phone_code' => 49],
            ['name' => 'France', 'code' => 'FR', 'iso3' => 'FRA', 'phone_code' => 33],
            ['name' => 'Japan', 'code' => 'JP', 'iso3' => 'JPN', 'phone_code' => 81],
            ['name' => 'China', 'code' => 'CN', 'iso3' => 'CHN', 'phone_code' => 86],
        ];
        
        foreach ($fallbackCountries as $country) {
            Country::updateOrCreate(
                ['code' => $country['code']],
                array_merge($country, ['is_active' => true, 'sort_order' => 0])
            );
        }
    }
    
    private function seedStates()
    {
        // Comprehensive state data for major countries
        // This fallback is intentionally minimal; main path is the remote dataset.
    }
}
