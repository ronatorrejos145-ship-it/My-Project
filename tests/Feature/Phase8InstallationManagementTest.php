<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\InstallationChecklistItem;
use App\Models\InstallationChecklistSection;
use App\Models\InstallationChecklistTemplate;
use App\Models\InstallationWorkOrder;
use App\Models\Item;
use App\Models\Location;
use App\Models\ServiceApplication;
use App\Models\ServicePackage;
use App\Models\ServicePackageVersion;
use App\Models\TechnicalSurvey;
use App\Models\User;
use App\Services\InstallationAcceptanceService;
use App\Services\InstallationArrivalService;
use App\Services\InstallationAssignmentService;
use App\Services\InstallationChecklistService;
use App\Services\InstallationCompletionService;
use App\Services\InstallationCreationService;
use App\Services\InstallationEquipmentService;
use App\Services\InstallationMaterialService;
use App\Services\InstallationSchedulingService;
use App\Services\InstallationSupervisorReviewService;
use App\Services\InstallationTestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class Phase8InstallationManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $techUser;
    protected Employee $techEmployee;
    protected Employee $supervisorEmployee;
    protected Branch $branch;
    protected Customer $customer;
    protected ServicePackage $package;
    protected ServicePackageVersion $packageVersion;
    protected ServiceApplication $application;
    protected TechnicalSurvey $survey;
    protected Location $location;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders::class);

        $this->branch = Branch::first();

        $this->adminUser = User::where('email', 'admin@isp.test')->first() ?? User::factory()->create();

        $this->techEmployee = Employee::where('employment_status', 'ACTIVE')->first();
        if (!$this->techEmployee) {
            $this->techEmployee = Employee::create([
                'employee_number' => 'EMP-TEST-001',
                'first_name' => 'John',
                'last_name' => 'Tech',
                'branch_id' => $this->branch->id,
                'employment_status' => 'ACTIVE',
            ]);
        }
        $this->techUser = User::factory()->create();
        $this->techUser->employee()->save($this->techEmployee);

        $this->supervisorEmployee = Employee::create([
            'employee_number' => 'EMP-SUP-001',
            'first_name' => 'Super',
            'last_name' => 'Visor',
            'branch_id' => $this->branch->id,
            'employment_status' => 'ACTIVE',
        ]);

        $this->customer = Customer::first();
        $this->package = ServicePackage::first();
        $this->packageVersion = ServicePackageVersion::where('package_id', $this->package->id)->latest()->first();

        $this->location = Location::create([
            'latitude' => 14.5995123,
            'longitude' => 120.9842234,
            'accuracy_meters' => 3.50,
            'address_text' => '123 Installation St, Manila',
        ]);

        $this->application = ServiceApplication::create([
            'application_number' => 'APP-2026-999888',
            'customer_id' => $this->customer->id,
            'package_id' => $this->package->id,
            'package_version_id' => $this->packageVersion->id,
            'branch_id' => $this->branch->id,
            'installation_location_id' => $this->location->id,
            'status' => 'APPROVED',
        ]);

        $this->survey = TechnicalSurvey::create([
            'survey_number' => 'SUR-2026-999888',
            'application_id' => $this->application->id,
            'customer_id' => $this->customer->id,
            'package_id' => $this->package->id,
            'package_version_id' => $this->packageVersion->id,
            'installation_location_id' => $this->location->id,
            'latitude' => 14.5995123,
            'longitude' => 120.9842234,
            'gps_accuracy' => 3.50,
            'approval_status' => 'APPROVED',
            'status' => 'APPROVED',
        ]);
    }

    public function test_can_create_installation_work_order_from_approved_survey(): void
    {
        $creationService = app(InstallationCreationService::class);

        $workOrder = $creationService->createFromApprovedSurvey(
            $this->survey->id,
            ['work_type' => 'NEW_INSTALLATION', 'priority' => 'HIGH'],
            $this->adminUser->id
        );

        $this->assertDatabaseHas('installation_work_orders', [
            'id' => $workOrder->id,
            'technical_survey_id' => $this->survey->id,
            'status' => 'PENDING',
            'work_type' => 'NEW_INSTALLATION',
        ]);

        $this->assertDatabaseHas('installation_status_histories', [
            'installation_id' => $workOrder->id,
            'new_status' => 'PENDING',
        ]);
    }

    public function test_cannot_create_installation_from_unapproved_survey(): void
    {
        $unapprovedSurvey = TechnicalSurvey::create([
            'survey_number' => 'SUR-UNAPP-123',
            'application_id' => $this->application->id,
            'customer_id' => $this->customer->id,
            'package_id' => $this->package->id,
            'package_version_id' => $this->packageVersion->id,
            'approval_status' => 'PENDING',
        ]);

        $creationService = app(InstallationCreationService::class);

        $this->expectException(InvalidArgumentException::class);
        $creationService->createFromApprovedSurvey($unapprovedSurvey->id);
    }

    public function test_assigns_technician_and_schedules_appointment_with_conflict_detection(): void
    {
        $creationService = app(InstallationCreationService::class);
        $workOrder = $creationService->createFromApprovedSurvey($this->survey->id);

        $assignService = app(InstallationAssignmentService::class);
        $assignService->assignTechnician($workOrder, $this->techEmployee->id, 'ALPHA-TEAM', null, 'Initial dispatch', $this->adminUser->id);

        $this->assertEquals('ASSIGNED', $workOrder->fresh()->status);
        $this->assertEquals($this->techEmployee->id, $workOrder->fresh()->assigned_technician_id);

        $scheduleService = app(InstallationSchedulingService::class);
        $schedule = $scheduleService->scheduleInstallation($workOrder, '2026-05-10', '09:00', '11:00', $this->techEmployee->id, false, null, $this->adminUser->id);

        $this->assertEquals('SCHEDULED', $workOrder->fresh()->status);

        // Attempt overlapping schedule for same technician without override -> expects exception
        $survey2 = TechnicalSurvey::create([
            'survey_number' => 'SUR-2026-777666',
            'application_id' => ServiceApplication::factory()->create(['status' => 'APPROVED'])->id,
            'customer_id' => $this->customer->id,
            'package_id' => $this->package->id,
            'package_version_id' => $this->packageVersion->id,
            'approval_status' => 'APPROVED',
        ]);
        $wo2 = $creationService->createFromApprovedSurvey($survey2->id);

        $this->expectException(InvalidArgumentException::class);
        $scheduleService->scheduleInstallation($wo2, '2026-05-10', '10:00', '12:00', $this->techEmployee->id, false);
    }

    public function test_enroute_and_gps_arrival_verification(): void
    {
        $creationService = app(InstallationCreationService::class);
        $workOrder = $creationService->createFromApprovedSurvey($this->survey->id);

        $dispatchService = app(InstallationDispatchService::class);
        $dispatchService->dispatchEnRoute($workOrder, $this->techUser->id);
        $this->assertEquals('EN_ROUTE', $workOrder->fresh()->status);

        $arrivalService = app(InstallationArrivalService::class);
        // Arrive within allowed radius
        $arrivalService->recordArrival($workOrder, 14.5995123, 120.9842234, 2.5, false, null, $this->techUser->id);
        $this->assertEquals('ON_SITE', $workOrder->fresh()->status);
    }

    public function test_checklist_material_equipment_and_test_execution(): void
    {
        $creationService = app(InstallationCreationService::class);
        $workOrder = $creationService->createFromApprovedSurvey($this->survey->id);

        // Checklist response
        $checklistService = app(InstallationChecklistService::class);
        $template = $checklistService->getDefaultTemplate();
        $item = $template->sections->first()->items->first();

        $checklistService->recordResponse($workOrder, $item->id, 'YES', true, 'Identity verified', $this->techUser->id);
        $this->assertDatabaseHas('installation_checklist_responses', [
            'installation_id' => $workOrder->id,
            'checklist_item_id' => $item->id,
            'response_value' => 'YES',
        ]);

        // Issue Material
        $materialService = app(InstallationMaterialService::class);
        $materialService->issueMaterial($workOrder, null, 'UTP Cable Cat6', 50.0, 'meters', 'Drop wire', $this->techUser->id);
        $this->assertDatabaseHas('installation_materials', [
            'installation_id' => $workOrder->id,
            'item_name' => 'UTP Cable Cat6',
            'issued_qty' => 50.0,
        ]);

        // Assign Equipment Asset
        $asset = Asset::create([
            'asset_tag' => 'AST-ROUTER-999',
            'name' => 'Dual Band WiFi 6 Router',
            'serial_number' => 'SN-WIFI6-999888',
            'mac_address' => 'AA:BB:CC:DD:EE:FF',
            'status' => 'IN_STOCK',
        ]);

        $equipmentService = app(InstallationEquipmentService::class);
        $equipmentService->assignEquipment($workOrder, 'ROUTER', $asset->id, 'WiFi 6 Router', 'SN-WIFI6-999888', 'AA:BB:CC:DD:EE:FF', 'New in box', $this->techUser->id);
        $this->assertDatabaseHas('installation_equipment', [
            'installation_id' => $workOrder->id,
            'serial_number' => 'SN-WIFI6-999888',
        ]);

        // Technical Test
        $testService = app(InstallationTestService::class);
        $testService->recordTest($workOrder, 'DOWNLOAD', '95.5', 'Mbps', null, 'Speedtest CLI', null, 'Gigabit port', $this->techUser->id);
        $this->assertDatabaseHas('installation_tests', [
            'installation_id' => $workOrder->id,
            'test_type' => 'DOWNLOAD',
            'result' => 'PASS',
        ]);
    }

    public function test_customer_acceptance_supervisor_review_and_completion_handoff(): void
    {
        $creationService = app(InstallationCreationService::class);
        $workOrder = $creationService->createFromApprovedSurvey($this->survey->id);

        // Record customer acceptance
        $acceptanceService = app(InstallationAcceptanceService::class);
        $acceptanceService->recordAcceptance($workOrder, 'John Doe', 'OWNER', 'ACCEPTED', null, null, '127.0.0.1', 'Mozilla/5.0', 'Satisfied', $this->adminUser->id);

        $this->assertEquals('PENDING_ACCEPTANCE', $workOrder->fresh()->status);

        // Supervisor review
        $reviewService = app(InstallationSupervisorReviewService::class);
        $reviewService->submitReview($workOrder, $this->supervisorEmployee->id, 'APPROVE', 'Looks good to go', $this->adminUser->id);

        // Complete installation and generate handoff
        $completionService = app(InstallationCompletionService::class);
        $completionService->completeInstallation($workOrder, $this->adminUser->id, true, 'Testing complete handoff');

        $this->assertEquals('COMPLETED', $workOrder->fresh()->status);

        $this->assertDatabaseHas('installation_handoffs', [
            'installation_id' => $workOrder->id,
            'status' => 'READY_FOR_ACTIVATION',
        ]);
    }
}
