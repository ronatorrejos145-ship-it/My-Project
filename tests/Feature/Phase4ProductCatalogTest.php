<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\ServiceCategory;
use App\Models\ServicePackage;
use App\Models\ServicePackageVersion;
use App\Models\Promotion;
use App\Models\Branch;
use App\Models\ServiceArea;
use App\Services\ServicePackageService;
use App\Services\PackageVersionService;
use App\Services\PromotionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase4ProductCatalogTest extends TestCase
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

    public function test_package_creation_initializes_version_1()
    {
        $pkgService = app(ServicePackageService::class);

        $package = $pkgService->createPackage([
            'package_code' => 'TEST-100M',
            'name' => 'Test Fiber Plan 100 Mbps',
            'package_type' => 'RESIDENTIAL',
            'technology' => 'FIBER',
            'download_speed' => 100,
            'upload_speed' => 100,
            'speed_unit' => 'Mbps',
            'base_price' => 1499.00,
            'installation_fee' => 1500.00,
            'status' => 'ACTIVE',
        ]);

        $this->assertDatabaseHas('service_packages', ['package_code' => 'TEST-100M']);
        $this->assertDatabaseHas('service_package_versions', [
            'package_id' => $package->id,
            'version_number' => 1,
            'price' => 1499.00,
            'download_speed' => 100,
        ]);
    }

    public function test_version_service_creates_version_2_and_closes_version_1()
    {
        $package = ServicePackage::where('package_code', 'HOME-20')->first();
        $versionService = app(PackageVersionService::class);

        $v1 = $package->activeVersion;
        $this->assertEquals(1, $v1->version_number);

        $v2 = $versionService->createVersion($package, [
            'version_name' => 'Annual Price Adjustment',
            'effective_from' => now()->addDay(),
            'price' => 899.00,
            'installation_fee' => 1500.00,
            'download_speed' => 25,
            'upload_speed' => 25,
            'change_reason' => 'Speed and price upgrade',
        ]);

        $this->assertEquals(2, $v2->version_number);
        $this->assertEquals(899.00, $v2->price);
        $this->assertNotNull($v1->fresh()->effective_until);
    }

    public function test_promotion_eligibility()
    {
        $package = ServicePackage::first();
        $promoService = app(PromotionService::class);

        $promo = Promotion::factory()->create([
            'status' => 'ACTIVE',
            'discount_amount' => 500.00,
        ]);

        $this->assertTrue($promoService->isEligible($promo, $package));
    }

    public function test_public_package_api_endpoint()
    {
        $response = $this->getJson(route('api.packages.index'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data' => [
                '*' => ['package_code', 'name', 'monthly_price', 'download_speed']
            ]
        ]);
    }

    public function test_admin_can_view_product_catalog_and_categories()
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.packages.index'));
        $response->assertStatus(200);
        $response->assertSee('HOME-20');

        $responseCategories = $this->actingAs($this->adminUser)->get(route('admin.packages.categories.index'));
        $responseCategories->assertStatus(200);
        $responseCategories->assertSee('Home Fiber Internet');
    }

    public function test_migration_rollback_and_reapply_cleanly()
    {
        $this->artisan('migrate:rollback')
            ->assertExitCode(0);

        $this->artisan('migrate')
            ->assertExitCode(0);
    }
}
