<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerDocumentController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\CrmDashboardController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\ServiceAreaController;
use App\Http\Controllers\NetworkNodeController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\ServicePackageController;
use App\Http\Controllers\ServiceCategoryController;
use App\Http\Controllers\ServicePackageVersionController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\ServicePackageApiController;
use App\Http\Controllers\ServiceApplicationController;
use App\Http\Controllers\ServiceabilityController;
use App\Http\Controllers\PublicApplicationApiController;
use App\Http\Controllers\GisMapController;
use App\Http\Controllers\GisApiController;
use App\Http\Controllers\NetworkTowerController;
use App\Http\Controllers\DistributionPointController;
use App\Http\Controllers\TechnicalSurveyController;
use App\Http\Controllers\TechnicalSurveyReviewController;
use App\Http\Controllers\TechnicalSurveyApiController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\NumberSequenceController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Public Customer Application Wizard & Public APIs (Sanitized output)
Route::get('apply', [ServiceApplicationController::class, 'wizard'])->name('public.applications.wizard');
Route::post('apply', [ServiceApplicationController::class, 'store'])->name('public.applications.submit');

Route::get('api/public/packages', [ServicePackageApiController::class, 'index'])->name('api.packages.index');
Route::post('api/public/serviceability/check', [PublicApplicationApiController::class, 'checkServiceability'])->name('api.serviceability.check');
Route::post('api/public/applications', [PublicApplicationApiController::class, 'submit'])->name('api.applications.submit');
Route::get('api/public/applications/{number}/status', [PublicApplicationApiController::class, 'status'])->name('api.applications.status');
Route::get('api/gis/geojson/service-areas', [GisApiController::class, 'serviceAreaGeoJson'])->name('api.gis.geojson.service-areas');

// Authenticated Staff Routes
Route::middleware(['auth', 'account.status'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Authenticated GIS Spatial Query APIs
    Route::get('api/gis/viewport', [GisApiController::class, 'viewport'])->name('api.gis.viewport');
    Route::get('api/gis/nearby', [GisApiController::class, 'nearby'])->name('api.gis.nearby');

    // Admin & Staff Routes
    Route::prefix('admin')->name('admin.')->group(function () {
        // Users & Core HR
        Route::resource('users', UserController::class);
        Route::resource('departments', DepartmentController::class);
        Route::resource('employees', EmployeeController::class);

        // Phase 7 Field Technical Surveys & Site Inspections
        Route::resource('technical-surveys', TechnicalSurveyController::class);
        Route::post('technical-surveys/{survey}/assign', [TechnicalSurveyController::class, 'assign'])->name('technical-surveys.assign');
        Route::post('technical-surveys/{survey}/verify-gps', [TechnicalSurveyController::class, 'verifyGps'])->name('technical-surveys.verify-gps');
        Route::post('technical-surveys/{survey}/upload-photo', [TechnicalSurveyController::class, 'uploadPhoto'])->name('technical-surveys.upload-photo');
        Route::post('technical-surveys/{survey}/submit', [TechnicalSurveyController::class, 'submit'])->name('technical-surveys.submit');
        Route::get('technical-surveys/{survey}/review', [TechnicalSurveyReviewController::class, 'reviewForm'])->name('technical-surveys.review.form');
        Route::post('technical-surveys/{survey}/review', [TechnicalSurveyReviewController::class, 'review'])->name('technical-surveys.review');
        Route::get('technical-surveys/{survey}/report', [TechnicalSurveyController::class, 'downloadReport'])->name('technical-surveys.report');

        // Phase 6 GIS Operations & Infrastructure Mapping
        Route::get('gis/map', [GisMapController::class, 'map'])->name('gis.map');
        Route::get('gis/dashboard', [GisMapController::class, 'dashboard'])->name('gis.dashboard');
        Route::get('gis/import', [GisMapController::class, 'importForm'])->name('gis.import.form');
        Route::post('gis/import', [GisMapController::class, 'importCsv'])->name('gis.import.csv');
        Route::resource('gis/towers', NetworkTowerController::class, ['names' => 'gis.towers']);
        Route::resource('gis/distribution-points', DistributionPointController::class, ['names' => 'gis.distribution-points']);

        // Phase 5 Applications & Serviceability
        Route::resource('applications', ServiceApplicationController::class);
        Route::post('applications/{application}/status', [ServiceApplicationController::class, 'updateStatus'])->name('applications.status');
        Route::get('serviceability/check', [ServiceabilityController::class, 'checkForm'])->name('serviceability.check.form');
        Route::post('serviceability/check', [ServiceabilityController::class, 'check'])->name('serviceability.check');
        Route::post('serviceability/{check}/override', [ServiceabilityController::class, 'override'])->name('serviceability.override');

        // Phase 3 CRM & Customer 360
        Route::get('crm/dashboard', [CrmDashboardController::class, 'index'])->name('crm.dashboard');
        Route::resource('customers', CustomerController::class);
        Route::post('customers/{customer}/status', [CustomerController::class, 'changeStatus'])->name('customers.status');
        Route::post('customers/{customer}/notes', [CustomerController::class, 'addNote'])->name('customers.notes.store');
        Route::post('customers/{customer}/documents', [CustomerDocumentController::class, 'store'])->name('customers.documents.store');
        Route::get('documents/{document}/download', [CustomerDocumentController::class, 'download'])->name('customers.documents.download');
        Route::post('documents/{document}/verify', [CustomerDocumentController::class, 'verify'])->name('customers.documents.verify');

        // Phase 3 Leads & Pipeline
        Route::resource('leads', LeadController::class);
        Route::post('leads/{lead}/convert', [LeadController::class, 'convert'])->name('leads.convert');
        Route::post('leads/{lead}/activities', [LeadController::class, 'addActivity'])->name('leads.activities.store');

        // Phase 4 Product Catalog & Service Packages
        Route::get('packages/categories', [ServiceCategoryController::class, 'index'])->name('packages.categories.index');
        Route::post('packages/categories', [ServiceCategoryController::class, 'store'])->name('packages.categories.store');
        Route::get('packages/promotions', [PromotionController::class, 'index'])->name('packages.promotions.index');
        Route::post('packages/promotions', [PromotionController::class, 'store'])->name('packages.promotions.store');
        Route::resource('packages', ServicePackageController::class);
        Route::get('packages/{package}/versions/create', [ServicePackageVersionController::class, 'create'])->name('packages.versions.create');
        Route::post('packages/{package}/versions', [ServicePackageVersionController::class, 'store'])->name('packages.versions.store');

        // RBAC, Audit, Settings
        Route::get('roles-permissions', [RoleController::class, 'index'])->name('roles-permissions.index');
        Route::resource('roles', RoleController::class);
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

        // Phase 2 Domain Master Data
        Route::resource('companies', CompanyController::class);
        Route::resource('branches', BranchController::class);
        Route::get('service-areas', [ServiceAreaController::class, 'index'])->name('service-areas.index');
        Route::get('network/nodes', [NetworkNodeController::class, 'index'])->name('network.nodes.index');
        Route::get('network/devices', [NetworkNodeController::class, 'devices'])->name('network.devices.index');

        // Assets & Tools
        Route::get('assets', [AssetController::class, 'index'])->name('assets.index');
        Route::get('assets/tools', [AssetController::class, 'tools'])->name('assets.tools');

        // Warehouse & Items
        Route::get('warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
        Route::get('warehouses/items', [WarehouseController::class, 'items'])->name('warehouses.items');
        Route::get('warehouses/suppliers', [WarehouseController::class, 'suppliers'])->name('warehouses.suppliers');

        // Finance Accounts
        Route::get('finance/accounts', [AccountController::class, 'index'])->name('finance.accounts.index');

        // Number Sequences
        Route::get('number-sequences', [NumberSequenceController::class, 'index'])->name('number-sequences.index');
    });
});
