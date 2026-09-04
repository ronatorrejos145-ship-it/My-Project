<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\ServiceArea;
use App\Models\Barangay;
use Illuminate\Database\Seeder;

class ServiceAreaSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::where('code', 'HQ-MNL')->first();

        $sa1 = ServiceArea::firstOrCreate(
            ['code' => 'SA-QC-CENTRAL'],
            [
                'name' => 'Quezon City Central Zone [DEMO]',
                'branch_id' => $branch?->id,
                'description' => 'Primary fiber coverage zone in Central QC',
                'status' => 'ACTIVE',
                'serviceability_status' => 'SERVICEABLE',
            ]
        );

        $barangays = Barangay::all();
        foreach ($barangays as $brgy) {
            $sa1->barangays()->syncWithoutDetaching([$brgy->id]);
        }
    }
}
