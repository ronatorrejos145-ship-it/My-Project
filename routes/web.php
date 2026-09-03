<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\CustomerController;
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

// Authenticated Routes
Route::middleware(['auth', 'account.status'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin & Master Data Routes
    Route::prefix('admin')->name('admin.')->group(function () {
        // Users & Core HR
        Route::resource('users', UserController::class);
        Route::resource('departments', DepartmentController::class);
        Route::resource('employees', EmployeeController::class);
        Route::resource('customers', CustomerController::class);

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

        // Packages & Billing
        Route::get('packages', [ServicePackageController::class, 'index'])->name('packages.index');

        // Finance Accounts
        Route::get('finance/accounts', [AccountController::class, 'index'])->name('finance.accounts.index');

        // Number Sequences
        Route::get('number-sequences', [NumberSequenceController::class, 'index'])->name('number-sequences.index');
    });
});
