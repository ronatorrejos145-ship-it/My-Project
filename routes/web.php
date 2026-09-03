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
use App\Http\Controllers\RoleAndPermissionController;
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

// Public Customer Application Wizard & APIs
Route::get('apply', [ServiceApplicationController::class, 'wizard'])->name('public.applications.wizard');
Route::post('apply', [ServiceApplicationController::class, 'store'])->name('public.applications.submit');

Route::get('api/public/packages', [ServicePackageApiController::class, 'index'])->name('api.packages.index');
Route::post('api/public/serviceability/check', [PublicApplicationApiController::class, 'checkServiceability'])->name('api.serviceability.check');
Route::post('api/public/applications', [PublicApplicationApiController::class, 'submit'])->name('api.applications.submit');
Route::get('api/public/applications/{number}/status', [PublicApplicationApiController::class, 'status'])->name('api.applications.status');

// Authenticated Staff Routes
Route::middleware(['auth', 'account.status'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin & Staff Routes
    Route::prefix('admin')->name('admin.')->group(function () {
        // Users & Core HR
        Route::resource('users', UserController::class);
        Route::resource('departments', DepartmentController::class);
        Route::resource('employees', EmployeeController::class);

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
        Route::get('roles-permissions', [RoleAndPermissionController::class, 'index'])->name('roles-permissions.index');
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
