<?php

namespace Database\Seeders;

use App\Models\Region;
use App\Models\Province;
use App\Models\CityMunicipality;
use App\Models\Barangay;
use Illuminate\Database\Seeder;

class GeographySeeder extends Seeder
{
    public function run(): void
    {
        $region = Region::firstOrCreate(
            ['code' => 'REG-NCR'],
            ['name' => 'National Capital Region', 'region_number' => 'NCR']
        );

        $province = Province::firstOrCreate(
            ['code' => 'PROV-MM', 'region_id' => $region->id],
            ['name' => 'Metro Manila']
        );

        $city = CityMunicipality::firstOrCreate(
            ['code' => 'CITY-QC', 'province_id' => $province->id],
            ['name' => 'Quezon City', 'type' => 'CITY', 'postal_code' => '1100']
        );

        $barangays = ['Central', 'Diliman', 'Kamuning', 'Loyola Heights', 'Batasan Hills'];
        foreach ($barangays as $idx => $bName) {
            Barangay::firstOrCreate(
                ['code' => 'BRGY-QC-' . ($idx + 1), 'city_municipality_id' => $city->id],
                ['name' => $bName, 'district' => 'District ' . ($idx + 1)]
            );
        }
    }
}
