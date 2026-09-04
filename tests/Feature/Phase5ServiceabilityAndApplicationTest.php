<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\ServicePackage;
use App\Models\NetworkNode;
use App\Models\ServiceApplication;
use App\Models\ServiceabilityCheck;
use App\Services\ServiceabilityEngineService;
use App\Services\ServiceApplicationService;
use App\Services\ApplicationStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase5ServiceabilityAndApplicationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $supervisorUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->adminUser = User::factory()->create(['status' => 'ACTIVE']);
        $adminRole = Role::where('code', 'SUPER_ADMIN')->first();
        if ($adminRole) {
            $this->adminUser->roles()->attach($adminRole->id);
        }

        $this->supervisorUser = User::factory()->create(['status' => 'ACTIVE']);
        $supRole = Role::where('code', 'TECHNICAL_SUPERVISOR')->first();
        if ($supRole) {
            $this->supervisorUser->roles()->attach($supRole->id);
        }
    }

    public function test_haversine_distance_calculation()
    {
        $engine = app(ServiceabilityEngineService::class);

        // Manila to Quezon City ~9.5km
        $distance = $engine->calculateHaversineDistance(14.5995, 120.9842, 14.6507, 121.0300);

        $this->assertGreaterThan(9000, $distance);
        $this->assertLessThan(11000, $distance);
    }

    public function test_serviceability_engine_returns_serviceable_when_node_in_range()
    {
        $engine = app(ServiceabilityEngineService::class);
        $package = ServicePackage::first();

        // Node is at 14.6507000, 121.0300000
        // Customer location at 14.6520000, 121.0320000 (~250m away)
        $check = $engine->evaluate(14.6520000, 121.0320000, $package);

        $this->assertEquals('SERVICEABLE', $check->result_status);
        $this->assertEquals('FIBER_NODE_IN_RANGE', $check->reason_code);
    }

    public function test_serviceability_engine_returns_survey_required_when_node_at_extended_distance()
    {
        $engine = app(ServiceabilityEngineService::class);
        $package = ServicePackage::first();

        // Customer location ~1.8km away
        $check = $engine->evaluate(14.6650000, 121.0450000, $package);

        $this->assertEquals('REQUIRES_TECHNICAL_SURVEY', $check->result_status);
        $this->assertEquals('FIBER_EXTENSION_REQUIRED', $check->reason_code);
    }

    public function test_service_application_submission_generates_sequential_number()
    {
        $appService = app(ServiceApplicationService::class);
        $package = ServicePackage::first();

        $application = $appService->submitApplication([
            'applicant_type' => 'RESIDENTIAL',
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'primary_phone' => '+63 917 888 7766',
            'email' => 'juan.test@demo.local',
            'service_package_id' => $package->id,
            'installation_address' => '123 Sampaguita St, QC',
            'latitude' => 14.6520000,
            'longitude' => 121.0320000,
            'gps_accuracy' => 4.5,
        ]);

        $this->assertNotEmpty($application->application_number);
        $this->assertStringStartsWith('APP-', $application->application_number);
        $this->assertDatabaseHas('service_applications', ['id' => $application->id]);
        $this->assertDatabaseHas('serviceability_checks', ['application_id' => $application->id]);
    }

    public function test_supervisor_can_override_serviceability_result()
    {
        $statusService = app(ApplicationStatusService::class);
        $check = ServiceabilityCheck::first();

        $updatedCheck = $statusService->overrideServiceability(
            $check,
            'SERVICEABLE',
            'Approved by supervisor: Line of Sight confirmed via aerial imagery'
        );

        $this->assertTrue($updatedCheck->is_overridden);
        $this->assertEquals('SERVICEABLE', $updatedCheck->override_result_status);
        $this->assertDatabaseHas('serviceability_checks', [
            'id' => $check->id,
            'is_overridden' => true,
        ]);
    }

    public function test_public_application_status_api()
    {
        $app = ServiceApplication::first();

        $response = $this->getJson(route('api.applications.status', ['number' => $app->application_number]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data' => ['application_number', 'applicant_name', 'status']
        ]);
    }

    public function test_admin_can_view_applications_and_review_portal()
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.applications.index'));
        $response->assertStatus(200);

        $app = ServiceApplication::first();
        $responseShow = $this->actingAs($this->adminUser)->get(route('admin.applications.show', $app));
        $responseShow->assertStatus(200);
        $responseShow->assertSee($app->application_number);
    }

    public function test_migration_rollback_and_reapply_cleanly()
    {
        $this->artisan('migrate:rollback')
            ->assertExitCode(0);

        $this->artisan('migrate')
            ->assertExitCode(0);
    }
}
