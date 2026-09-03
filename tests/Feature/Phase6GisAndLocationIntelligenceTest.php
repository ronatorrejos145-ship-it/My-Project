<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\NetworkTower;
use App\Models\DistributionPoint;
use App\Models\ServiceArea;
use App\Models\LocationHistory;
use App\Services\GisService;
use App\Services\NearbyInfrastructureService;
use App\Services\GeoJsonService;
use App\Services\GisImportExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class Phase6GisAndLocationIntelligenceTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->adminUser = User::factory()->create(['status' => 'ACTIVE']);
        $adminRole = Role::where('code', 'SUPER_ADMIN')->first();
        if ($adminRole) {
            $this->adminUser->roles()->attach($adminRole->id);
        }
    }

    public function test_viewport_bounding_box_spatial_query_api()
    {
        $response = $this->actingAs($this->adminUser)->getJson(route('api.gis.viewport', [
            'north' => 14.7000,
            'south' => 14.6000,
            'east' => 121.1000,
            'west' => 121.0000,
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data' => ['nodes', 'access_points', 'towers', 'distribution_points', 'customers', 'applications', 'service_areas']
        ]);
    }

    public function test_nearby_infrastructure_search_api()
    {
        $response = $this->actingAs($this->adminUser)->getJson(route('api.gis.nearby', [
            'latitude' => 14.6520000,
            'longitude' => 121.0320000,
            'radius_meters' => 3000,
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data' => [
                '*' => ['type', 'id', 'code', 'name', 'distance_meters', 'latitude', 'longitude']
            ]
        ]);
    }

    public function test_service_area_geojson_export_api()
    {
        $response = $this->getJson(route('api.gis.geojson.service-areas'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'type',
            'features' => [
                '*' => ['type', 'properties', 'geometry']
            ]
        ]);
    }

    public function test_csv_gps_coordinate_bulk_import()
    {
        $importService = app(GisImportExportService::class);

        $csvContent = "entity_type,code,name,latitude,longitude\n" .
            "NODE,NODE-IMP-01,Imported Test Node,14.6550000,121.0350000\n" .
            "TOWER,TWR-IMP-01,Imported Test Tower,14.6560000,121.0360000\n";

        $file = UploadedFile::fake()->createWithContent('gis_coordinates.csv', $csvContent);

        $import = $importService->importCsvCoordinates($file);

        $this->assertEquals(2, $import->records_processed);
        $this->assertEquals(2, $import->records_imported);
        $this->assertEquals(0, $import->records_failed);

        $this->assertDatabaseHas('network_nodes', ['node_code' => 'NODE-IMP-01']);
        $this->assertDatabaseHas('network_towers', ['code' => 'TWR-IMP-01']);
    }

    public function test_location_history_recording()
    {
        $history = LocationHistory::create([
            'entity_type' => 'NetworkNode',
            'entity_id' => 1,
            'previous_latitude' => 14.6500000,
            'previous_longitude' => 121.0290000,
            'new_latitude' => 14.6507000,
            'new_longitude' => 121.0300000,
            'reason' => 'GPS re-survey precision adjustment',
        ]);

        $this->assertDatabaseHas('location_histories', [
            'entity_type' => 'NetworkNode',
            'entity_id' => 1,
            'new_latitude' => 14.6507000,
        ]);
    }

    public function test_admin_can_view_gis_map_and_dashboard()
    {
        $responseMap = $this->actingAs($this->adminUser)->get(route('admin.gis.map'));
        $responseMap->assertStatus(200);
        $responseMap->assertSee('GIS Infrastructure');

        $responseDash = $this->actingAs($this->adminUser)->get(route('admin.gis.dashboard'));
        $responseDash->assertStatus(200);
        $responseDash->assertSee('GIS Location Intelligence Dashboard');
    }

    public function test_migration_rollback_and_reapply_cleanly()
    {
        $this->artisan('migrate:rollback')
            ->assertExitCode(0);

        $this->artisan('migrate')
            ->assertExitCode(0);
    }
}
