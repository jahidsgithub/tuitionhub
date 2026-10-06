<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class BangladeshLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $url = 'https://iqbalhasandev.github.io/bangladesh-geo-json/bangladesh-geo.json';

        $response = Http::timeout(60)
            ->retry(3, 1000)
            ->get($url);

        if (! $response->successful()) {
            throw new RuntimeException(
                'Could not download Bangladesh location dataset.'
            );
        }

        $divisions = $response->json();

        if (! is_array($divisions)) {
            throw new RuntimeException(
                'Invalid Bangladesh location dataset.'
            );
        }

        foreach ($divisions as $divisionData) {
            $division = $this->normalizeDivision(
                $divisionData['name'] ?? ''
            );

            foreach ($divisionData['districts'] ?? [] as $districtData) {
                $district = $this->normalizeDistrict(
                    $districtData['name'] ?? ''
                );

                foreach ($districtData['upazilas'] ?? [] as $upazilaData) {
                    $area = trim(
                        (string) ($upazilaData['name'] ?? '')
                    );

                    if (
                        $division === '' ||
                        $district === '' ||
                        $area === ''
                    ) {
                        continue;
                    }

                    $slug = Str::slug(
                        $division
                        . '-'
                        . $district
                        . '-'
                        . $area
                    );

                    Location::updateOrCreate(
                        [
                            'slug' => $slug,
                        ],
                        [
                            'division' => $division,
                            'district' => $district,
                            'area' => $area,
                            'status' => true,
                        ]
                    );
                }
            }
        }
    }

    /**
     * Normalize division spellings to the naming style
     * already used by Tuition Hub.
     */
    private function normalizeDivision(string $division): string
    {
        $division = trim($division);

        return match (Str::lower($division)) {
            'chattagram',
            'chittagong',
            'chattogram' => 'Chattogram',

            'barisal',
            'barishal' => 'Barishal',

            'dhaka' => 'Dhaka',
            'khulna' => 'Khulna',
            'rajshahi' => 'Rajshahi',
            'rangpur' => 'Rangpur',
            'sylhet' => 'Sylhet',
            'mymensingh' => 'Mymensingh',

            default => $division,
        };
    }

    /**
     * Normalize common district spelling variations.
     */
    private function normalizeDistrict(string $district): string
    {
        $district = trim($district);

        return match (Str::lower($district)) {
            'comilla',
            'cumilla' => 'Cumilla',

            'chittagong',
            'chattagram',
            'chattogram' => 'Chattogram',

            'barisal',
            'barishal' => 'Barishal',

            'jessore',
            'jashore' => 'Jashore',

            'bogra',
            'bogura' => 'Bogura',

            'coxs bazar',
            "cox's bazar" => "Cox's Bazar",

            'jhalokati',
            'jhalokathi' => 'Jhalokathi',

            'moulvibazar',
            'maulvibazar' => 'Moulvibazar',

            'chapai nawabganj',
            'chapainawabganj' => 'Chapainawabganj',

            default => $district,
        };
    }
}