<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\CheckAccountStatus;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Support\Facades\Route;

// Public Guest Routes
Route::middleware([SecurityHeaders::class, 'guest'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('login');
    });

    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');
});

// Authenticated Routes
Route::middleware([SecurityHeaders::class, 'auth', CheckAccountStatus::class])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin & Management Area
    Route::prefix('admin')->name('admin.')->group(function () {
        // Users Management
        Route::resource('users', UserController::class);

        // Roles & Permissions
        Route::resource('roles', RoleController::class)->except(['show', 'destroy']);

        // Departments
        Route::resource('departments', DepartmentController::class)->except(['show', 'destroy']);

        // Employees
        Route::resource('employees', EmployeeController::class)->except(['show', 'destroy']);

        // Customers Foundation
        Route::resource('customers', CustomerController::class)->except(['show', 'destroy']);

        // Audit Logs
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

        // Settings
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
    });
});
