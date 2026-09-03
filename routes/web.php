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
use App\Http\Controllers\AssetManagementController;
use App\Http\Controllers\AssetQrVerificationController;
use App\Http\Controllers\AssetApiController;
use App\Http\Controllers\SubscriberManagementController;
use App\Http\Controllers\SubscriberApiController;
use App\Http\Controllers\BillingManagementController;
use App\Http\Controllers\BillingApiController;
use App\Http\Controllers\FinanceManagementController;
use App\Http\Controllers\InventoryManagementController;
use App\Http\Controllers\InventoryApiController;
use App\Http\Controllers\ProcurementManagementController;
use App\Http\Controllers\ProcurementApiController;
use App\Http\Controllers\ToolManagementController;
use App\Http\Controllers\ToolApiController;
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
use App\Http\Controllers\InstallationWorkOrderController;
use App\Http\Controllers\TechnicianInstallationController;
use App\Http\Controllers\CustomerInstallationAcceptanceController;
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
    // Customer Installation Acceptance Portal
    Route::get('customer/installations/{installation}/acceptance', [CustomerInstallationAcceptanceController::class, 'show'])->name('customer.installations.acceptance');
    Route::post('customer/installations/{installation}/accept', [CustomerInstallationAcceptanceController::class, 'accept'])->name('customer.installations.accept');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // QR Code Asset Lookup & Physical Audit Verification Routes (Protected)
    Route::get('assets/qr/{asset_tag}', [AssetQrVerificationController::class, 'lookup'])->name('assets.qr.lookup');
    Route::post('assets/qr/{asset}/verify', [AssetQrVerificationController::class, 'verify'])->name('assets.qr.verify');

    // Authenticated GIS Spatial Query APIs
    Route::get('api/gis/viewport', [GisApiController::class, 'viewport'])->name('api.gis.viewport');
    Route::get('api/gis/nearby', [GisApiController::class, 'nearby'])->name('api.gis.nearby');

    // Phase 12 Billing Engine REST APIs
    Route::get('api/billing/charges', [BillingApiController::class, 'indexCharges'])->name('api.billing.charges.index');
    Route::get('api/billing/runs', [BillingApiController::class, 'indexRuns'])->name('api.billing.runs.index');
    Route::post('api/billing/preview/{serviceAccount}', [BillingApiController::class, 'previewCustomer'])->name('api.billing.preview');
    Route::post('api/billing/runs/execute', [BillingApiController::class, 'executeRun'])->name('api.billing.runs.execute');

    // Phase 11 Subscriber REST APIs
    Route::get('api/subscribers', [SubscriberApiController::class, 'index'])->name('api.subscribers.index');
    Route::get('api/subscribers/{subscriber}', [SubscriberApiController::class, 'show'])->name('api.subscribers.show');

    // Phase 10 REST APIs
    Route::get('api/inventory', [InventoryApiController::class, 'index'])->name('api.inventory.index');
    Route::post('api/inventory/adjust', [InventoryApiController::class, 'adjust'])->name('api.inventory.adjust');
    Route::get('api/procurement/requests', [ProcurementApiController::class, 'indexRequests'])->name('api.procurement.requests');
    Route::get('api/procurement/orders', [ProcurementApiController::class, 'indexOrders'])->name('api.procurement.orders');
    Route::get('api/tools', [ToolApiController::class, 'index'])->name('api.tools.index');

    // Phase 9 Asset Management REST APIs
    Route::get('api/assets', [AssetApiController::class, 'index'])->name('api.assets.index');
    Route::get('api/assets/{asset}', [AssetApiController::class, 'show'])->name('api.assets.show');
    Route::post('api/assets', [AssetApiController::class, 'store'])->name('api.assets.store');

    // Phase 8 Mobile Technician Portal Routes
    Route::prefix('technician')->name('technician.')->group(function () {
        Route::get('installations', [TechnicianInstallationController::class, 'index'])->name('installations.index');
        Route::get('installations/{installation}', [TechnicianInstallationController::class, 'show'])->name('installations.show');
        Route::post('installations/{installation}/dispatch-enroute', [TechnicianInstallationController::class, 'dispatchEnRoute'])->name('installations.dispatch-enroute');
        Route::post('installations/{installation}/arrive', [TechnicianInstallationController::class, 'arrive'])->name('installations.arrive');
        Route::post('installations/{installation}/save-checklist', [TechnicianInstallationController::class, 'saveChecklist'])->name('installations.save-checklist');
        Route::post('installations/{installation}/issue-material', [TechnicianInstallationController::class, 'issueMaterial'])->name('installations.issue-material');
        Route::post('installations/{installation}/assign-equipment', [TechnicianInstallationController::class, 'assignEquipment'])->name('installations.assign-equipment');
        Route::post('installations/{installation}/record-test', [TechnicianInstallationController::class, 'recordTest'])->name('installations.record-test');
    });

    // Admin & Staff Routes
    Route::prefix('admin')->name('admin.')->group(function () {
        // Users & Core HR
        Route::resource('users', UserController::class);
        Route::resource('departments', DepartmentController::class);
        Route::resource('employees', EmployeeController::class);

        // Phase 13 Financial Portal Routes
        Route::get('finance/invoices', [FinanceManagementController::class, 'invoices'])->name('finance.invoices.index');
        Route::get('finance/invoices/{invoice}', [FinanceManagementController::class, 'showInvoice'])->name('finance.invoices.show');
        Route::post('finance/invoices/generate/{serviceAccount}', [FinanceManagementController::class, 'generateForAccount'])->name('finance.invoices.generate');
        Route::post('finance/invoices/{invoice}/finalize', [FinanceManagementController::class, 'finalizeInvoice'])->name('finance.invoices.finalize');
        Route::post('finance/invoices/{invoice}/cancel', [FinanceManagementController::class, 'cancelInvoice'])->name('finance.invoices.cancel');
        Route::get('finance/invoices/{invoice}/pdf', [FinanceManagementController::class, 'downloadInvoicePdf'])->name('finance.invoices.pdf');
        Route::get('finance/ledger', [FinanceManagementController::class, 'ledger'])->name('finance.ledger.index');
        Route::get('finance/reconciliation', [FinanceManagementController::class, 'reconciliation'])->name('finance.reconciliation');

        // Phase 12 Billing Engine Operations Portal
        Route::get('billing/dashboard', [BillingManagementController::class, 'dashboard'])->name('billing.dashboard');
        Route::get('billing/runs', [BillingManagementController::class, 'runs'])->name('billing.runs');
        Route::post('billing/runs/execute', [BillingManagementController::class, 'executeRun'])->name('billing.runs.execute');
        Route::get('billing/charges', [BillingManagementController::class, 'charges'])->name('billing.charges');
        Route::get('billing/exceptions', [BillingManagementController::class, 'exceptions'])->name('billing.exceptions');
        Route::get('billing/proration-calculator', [BillingManagementController::class, 'prorationCalculator'])->name('billing.proration-calculator');
        Route::post('billing/proration-calculator', [BillingManagementController::class, 'prorationCalculator'])->name('billing.proration-calculator.calculate');

        // Phase 11 Subscriber & Subscription Management Portal
        Route::get('subscribers', [SubscriberManagementController::class, 'index'])->name('subscribers.index');
        Route::get('subscribers/{subscriber}', [SubscriberManagementController::class, 'show'])->name('subscribers.show');
        Route::post('subscribers/activate-handoff/{handoff}', [SubscriberManagementController::class, 'activateHandoff'])->name('subscribers.activate-handoff');
        Route::post('subscribers/subscriptions/{subscription}/change-package', [SubscriberManagementController::class, 'changePackage'])->name('subscribers.change-package');
        Route::post('subscribers/subscriptions/{subscription}/status', [SubscriberManagementController::class, 'updateStatus'])->name('subscribers.update-status');

        // Phase 10 Inventory, Procurement & Tool Portal Routes
        Route::get('inventory', [InventoryManagementController::class, 'index'])->name('inventory.index');
        Route::post('inventory/adjust', [InventoryManagementController::class, 'adjustStock'])->name('inventory.adjust');
        Route::get('procurement', [ProcurementManagementController::class, 'index'])->name('procurement.index');
        Route::get('procurement/po/create', [ProcurementManagementController::class, 'createPo'])->name('procurement.create-po');
        Route::post('procurement/po', [ProcurementManagementController::class, 'storePo'])->name('procurement.store-po');
        Route::post('procurement/po/{po}/receive', [ProcurementManagementController::class, 'receivePo'])->name('procurement.receive-po');
        Route::get('tools', [ToolManagementController::class, 'index'])->name('tools.index');
        Route::post('tools/{tool}/checkout', [ToolManagementController::class, 'checkout'])->name('tools.checkout');
        Route::post('tools/checkouts/{checkout}/return', [ToolManagementController::class, 'return'])->name('tools.return');

        // Phase 9 Equipment & Technical Asset Management Portal
        Route::get('assets', [AssetManagementController::class, 'index'])->name('assets.index');
        Route::get('assets/create', [AssetManagementController::class, 'create'])->name('assets.create');
        Route::post('assets', [AssetManagementController::class, 'store'])->name('assets.store');
        Route::get('assets/{asset}', [AssetManagementController::class, 'show'])->name('assets.show');
        Route::post('assets/{asset}/transfer', [AssetManagementController::class, 'transfer'])->name('assets.transfer');
        Route::post('assets/{asset}/replace', [AssetManagementController::class, 'replace'])->name('assets.replace');
        Route::post('assets/{asset}/retire', [AssetManagementController::class, 'retire'])->name('assets.retire');
        Route::post('assets/{asset}/dispose', [AssetManagementController::class, 'dispose'])->name('assets.dispose');
        Route::post('assets/import', [AssetManagementController::class, 'importCsv'])->name('assets.import');

        // Phase 8 Installation Management & Dispatch
        Route::resource('installations', InstallationWorkOrderController::class);
        Route::post('installations/{installation}/assign', [InstallationWorkOrderController::class, 'assign'])->name('installations.assign');
        Route::post('installations/{installation}/schedule', [InstallationWorkOrderController::class, 'schedule'])->name('installations.schedule');
        Route::post('installations/{installation}/complete', [InstallationWorkOrderController::class, 'complete'])->name('installations.complete');
        Route::post('installations/{installation}/review-supervisor', [InstallationWorkOrderController::class, 'reviewSupervisor'])->name('installations.review-supervisor');
        Route::get('installations/{installation}/pdf', [InstallationWorkOrderController::class, 'downloadPdf'])->name('installations.download-pdf');

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
        Route::get('documents/{document}/download', [CustomerDocumentController::class, 'download'])->name('customers.download');
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
