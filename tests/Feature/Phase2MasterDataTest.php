<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Company;
use App\Models\Branch;
use App\Models\ServiceArea;
use App\Models\NetworkNode;
use App\Models\NetworkDevice;
use App\Models\Asset;
use App\Models\Warehouse;
use App\Models\Item;
use App\Models\ServicePackage;
use App\Models\Account;
use App\Models\NumberSequence;
use App\Services\NumberSequenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase2MasterDataTest extends TestCase
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

    public function test_number_sequence_service_generates_atomic_identifiers()
    {
        $service = app(NumberSequenceService::class);

        $num1 = $service->getNextNumber('CUSTOMER');
        $num2 = $service->getNextNumber('CUSTOMER');

        $this->assertNotEmpty($num1);
        $this->assertNotEmpty($num2);
        $this->assertNotEquals($num1, $num2);
        $this->assertStringStartsWith('CUST-', $num1);
    }

    public function test_master_data_tables_are_seeded()
    {
        $this->assertDatabaseHas('companies', ['code' => 'DEMO-ISP']);
        $this->assertDatabaseHas('branches', ['code' => 'HQ-MNL']);
        $this->assertDatabaseHas('service_areas', ['code' => 'SA-QC-CENTRAL']);
        $this->assertDatabaseHas('network_nodes', ['node_code' => 'NODE-CORE-01']);
        $this->assertDatabaseHas('network_devices', ['device_code' => 'DEV-MK-01']);
        $this->assertDatabaseHas('service_packages', ['package_code' => 'PKG-RES-35M']);
        $this->assertDatabaseHas('accounts', ['account_code' => '1100']);
        $this->assertDatabaseHas('number_sequences', ['code' => 'CUSTOMER']);
    }

    public function test_admin_can_view_companies_and_branches()
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.companies.index'));
        $response->assertStatus(200);
        $response->assertSee('DEMO-ISP');

        $responseBranch = $this->actingAs($this->adminUser)->get(route('admin.branches.index'));
        $responseBranch->assertStatus(200);
        $responseBranch->assertSee('HQ-MNL');
    }

    public function test_admin_can_create_new_company()
    {
        $payload = [
            'code' => 'NEW-COMP-101',
            'legal_name' => 'New Broadband Telecom Inc.',
            'trade_name' => 'NewBroadband',
            'status' => 'ACTIVE',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('admin.companies.store'), $payload);
        $response->assertRedirect(route('admin.companies.index'));

        $this->assertDatabaseHas('companies', ['code' => 'NEW-COMP-101']);
    }

    public function test_admin_can_view_network_nodes_and_devices()
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.network.nodes.index'));
        $response->assertStatus(200);
        $response->assertSee('NODE-CORE-01');

        $responseDevices = $this->actingAs($this->adminUser)->get(route('admin.network.devices.index'));
        $responseDevices->assertStatus(200);
        $responseDevices->assertSee('DEV-MK-01');
    }

    public function test_admin_can_view_assets_and_warehouse()
    {
        $responseAssets = $this->actingAs($this->adminUser)->get(route('admin.assets.index'));
        $responseAssets->assertStatus(200);

        $responseWH = $this->actingAs($this->adminUser)->get(route('admin.warehouses.index'));
        $responseWH->assertStatus(200);
        $responseWH->assertSee('WH-MAIN-MNL');
    }

    public function test_admin_can_view_packages_and_finance_accounts()
    {
        $responsePkg = $this->actingAs($this->adminUser)->get(route('admin.packages.index'));
        $responsePkg->assertStatus(200);

        $responseAccounts = $this->actingAs($this->adminUser)->get(route('admin.finance.accounts.index'));
        $responseAccounts->assertStatus(200);
        $responseAccounts->assertSee('1100');
    }

    public function test_migration_rollback_and_reapply_cleanly()
    {
        $this->artisan('migrate:rollback')
            ->assertExitCode(0);

        $this->artisan('migrate')
            ->assertExitCode(0);
    }
}
