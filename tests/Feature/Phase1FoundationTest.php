<?php

namespace Tests\Feature;

use App\Enums\UserStatus;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Phase1FoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function createSuperAdmin(): User
    {
        $superAdminRole = Role::firstOrCreate(['code' => 'SUPER_ADMIN'], ['name' => 'Super Administrator']);
        $permission = Permission::firstOrCreate(['code' => 'users.view'], ['name' => 'View Users', 'module' => 'users']);
        $superAdminRole->permissions()->sync([$permission->id]);

        $admin = User::firstOrCreate(
            ['email' => 'admin@isp.example.com'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('AdminSecret123!'),
                'status' => 'ACTIVE',
            ]
        );
        $admin->roles()->sync([$superAdminRole->id]);

        return $admin;
    }

    protected function createCustomerUser(): User
    {
        $custRole = Role::firstOrCreate(['code' => 'CUSTOMER'], ['name' => 'Subscriber']);
        $customerUser = User::firstOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Maria Customer',
                'password' => Hash::make('CustomerSecret123!'),
                'status' => 'ACTIVE',
            ]
        );
        $customerUser->roles()->sync([$custRole->id]);

        return $customerUser;
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $this->createSuperAdmin();

        $response = $this->post('/login', [
            'email' => 'admin@isp.example.com',
            'password' => 'AdminSecret123!',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $this->createSuperAdmin();

        $response = $this->post('/login', [
            'email' => 'admin@isp.example.com',
            'password' => 'WrongPassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_inactive_user_is_rejected_at_login(): void
    {
        $inactiveUser = User::factory()->create([
            'email' => 'inactive@isp.example.com',
            'password' => Hash::make('password123'),
            'status' => 'INACTIVE',
        ]);

        $response = $this->post('/login', [
            'email' => 'inactive@isp.example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_unauthenticated_user_cannot_access_protected_admin_routes(): void
    {
        $response = $this->get('/admin/users');
        $response->assertRedirect('/login');
    }

    public function test_user_without_permission_cannot_access_users_admin(): void
    {
        $custUser = $this->createCustomerUser();

        $response = $this->actingAs($custUser)->get('/admin/users');
        $response->assertStatus(403);
    }

    public function test_super_admin_can_access_users_admin(): void
    {
        $admin = $this->createSuperAdmin();

        $response = $this->actingAs($admin)->get('/admin/users');
        $response->assertStatus(200);
    }

    public function test_admin_can_create_department(): void
    {
        $admin = $this->createSuperAdmin();

        $response = $this->actingAs($admin)->post('/admin/departments', [
            'name' => 'Billing Operations',
            'code' => 'BILLING_DEPT',
            'description' => 'Handles billing and invoices',
            'status' => 'ACTIVE',
        ]);

        $response->assertRedirect('/admin/departments');
        $this->assertDatabaseHas('departments', ['code' => 'BILLING_DEPT']);
    }

    public function test_admin_can_create_customer(): void
    {
        $admin = $this->createSuperAdmin();

        $response = $this->actingAs($admin)->post('/admin/customers', [
            'customer_number' => 'CUST-TEST-01',
            'account_number' => 'ACC-TEST-01',
            'customer_type' => 'RESIDENTIAL',
            'status' => 'ACTIVE',
            'contact_person' => 'John Doe',
            'primary_phone' => '+639178888888',
            'email' => 'johndoe@example.com',
            'installation_address' => '456 Main St, Manila',
        ]);

        $response->assertRedirect('/admin/customers');
        $this->assertDatabaseHas('customers', ['customer_number' => 'CUST-TEST-01']);
    }

    public function test_audit_log_records_events(): void
    {
        $admin = $this->createSuperAdmin();

        $this->actingAs($admin)->post('/admin/departments', [
            'name' => 'Audit Test Dept',
            'code' => 'AUDIT_DEPT',
            'description' => 'Test',
            'status' => 'ACTIVE',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'department.created',
            'user_id' => $admin->id,
        ]);
    }
}
