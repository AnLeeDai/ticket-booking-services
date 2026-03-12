<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EmployeeManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::where('name', 'admin')->first();
        $customerRole = Role::where('name', 'customer')->first();

        $this->admin = User::create([
            'role_id' => $adminRole->role_id,
            'user_name' => 'admin_emp',
            'full_name' => 'Admin Emp',
            'email' => 'admin_emp@test.com',
            'password' => Hash::make('Password@123'),
            'status' => 'IN_ACTIVE',
        ]);

        $this->customer = User::create([
            'role_id' => $customerRole->role_id,
            'user_name' => 'customer_emp',
            'full_name' => 'Customer Emp',
            'email' => 'customer_emp@test.com',
            'password' => Hash::make('Password@123'),
            'status' => 'IN_ACTIVE',
        ]);
    }

    // ======================== EMPLOYEE ROLES ========================

    public function test_admin_can_create_employee_role(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/employee-roles', [
            'name' => 'STAFF',
            'description' => 'Nhân viên chính thức',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'STAFF');
    }

    public function test_admin_can_list_employee_roles(): void
    {
        Sanctum::actingAs($this->admin);

        $this->postJson('/api/employee-roles', ['name' => 'STAFF']);
        $this->postJson('/api/employee-roles', ['name' => 'PROBATION']);

        $response = $this->getJson('/api/employee-roles');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertCount(2, $response->json('data.items'));
    }

    public function test_admin_can_update_employee_role(): void
    {
        Sanctum::actingAs($this->admin);

        $createRes = $this->postJson('/api/employee-roles', ['name' => 'STAFF']);
        $roleId = $createRes->json('data.employee_role_id');

        $response = $this->putJson("/api/employee-roles/{$roleId}", [
            'description' => 'Nhân viên chính thức - updated',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.description', 'Nhân viên chính thức - updated');
    }

    public function test_admin_can_delete_employee_role(): void
    {
        Sanctum::actingAs($this->admin);

        $createRes = $this->postJson('/api/employee-roles', ['name' => 'PROBATION']);
        $roleId = $createRes->json('data.employee_role_id');

        $response = $this->deleteJson("/api/employee-roles/{$roleId}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('employee_roles', ['employee_role_id' => $roleId]);
    }

    public function test_employee_role_validates_name_enum(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/employee-roles', [
            'name' => 'INVALID_ROLE',
        ]);

        $response->assertStatus(422);
    }

    // ======================== EMPLOYEES ========================

    public function test_admin_can_create_employee(): void
    {
        Sanctum::actingAs($this->admin);

        // Tạo employee role qua API
        $roleRes = $this->postJson('/api/employee-roles', ['name' => 'STAFF']);
        $empRoleId = $roleRes->json('data.employee_role_id');

        // Tạo cinema qua API
        $cinemaRes = $this->postJson('/api/cinemas', ['name' => 'Cinema Emp Test', 'location' => 'HCM']);
        $cinemaId = $cinemaRes->json('data.cinema_id');

        // Tạo user mới cho employee
        $empUser = User::create([
            'role_id' => Role::where('name', 'customer')->first()->role_id,
            'user_name' => 'emp_user1',
            'full_name' => 'Employee User 1',
            'email' => 'emp1@test.com',
            'password' => Hash::make('Password@123'),
            'status' => 'IN_ACTIVE',
        ]);

        $response = $this->postJson('/api/employees', [
            'employee_role_id' => $empRoleId,
            'user_id' => $empUser->user_id,
            'cinema_id' => $cinemaId,
            'name' => 'Nguyễn Văn A',
            'hire_date' => '2026-01-15',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Nguyễn Văn A')
            ->assertJsonStructure([
                'success', 'message',
                'data' => ['employee_id', 'code', 'name', 'hire_date'],
            ]);

        // Code auto-generated
        $this->assertStringStartsWith('EMP-', $response->json('data.code'));
    }

    public function test_admin_can_list_employees(): void
    {
        Sanctum::actingAs($this->admin);

        $roleRes = $this->postJson('/api/employee-roles', ['name' => 'STAFF']);
        $empRoleId = $roleRes->json('data.employee_role_id');

        $cinemaRes = $this->postJson('/api/cinemas', ['name' => 'Cinema List', 'location' => 'HN']);
        $cinemaId = $cinemaRes->json('data.cinema_id');

        $empUser = User::create([
            'role_id' => Role::where('name', 'customer')->first()->role_id,
            'user_name' => 'emp_list',
            'full_name' => 'Employee List',
            'email' => 'emplist@test.com',
            'password' => Hash::make('Password@123'),
            'status' => 'IN_ACTIVE',
        ]);

        $this->postJson('/api/employees', [
            'employee_role_id' => $empRoleId,
            'user_id' => $empUser->user_id,
            'cinema_id' => $cinemaId,
            'name' => 'Test Employee',
            'hire_date' => '2026-01-15',
        ]);

        $response = $this->getJson('/api/employees');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertCount(1, $response->json('data.items'));
    }

    public function test_admin_can_get_employee_detail(): void
    {
        Sanctum::actingAs($this->admin);

        $roleRes = $this->postJson('/api/employee-roles', ['name' => 'STAFF']);
        $empRoleId = $roleRes->json('data.employee_role_id');

        $cinemaRes = $this->postJson('/api/cinemas', ['name' => 'Cinema Detail', 'location' => 'DN']);
        $cinemaId = $cinemaRes->json('data.cinema_id');

        $empUser = User::create([
            'role_id' => Role::where('name', 'customer')->first()->role_id,
            'user_name' => 'emp_detail',
            'full_name' => 'Employee Detail',
            'email' => 'empdetail@test.com',
            'password' => Hash::make('Password@123'),
            'status' => 'IN_ACTIVE',
        ]);

        $createRes = $this->postJson('/api/employees', [
            'employee_role_id' => $empRoleId,
            'user_id' => $empUser->user_id,
            'cinema_id' => $cinemaId,
            'name' => 'Detail Employee',
            'hire_date' => '2026-02-01',
        ]);
        $empId = $createRes->json('data.employee_id');

        $response = $this->getJson("/api/employees/{$empId}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.employee_id', $empId)
            ->assertJsonPath('data.name', 'Detail Employee');
    }

    public function test_admin_can_update_employee(): void
    {
        Sanctum::actingAs($this->admin);

        $roleRes = $this->postJson('/api/employee-roles', ['name' => 'STAFF']);
        $empRoleId = $roleRes->json('data.employee_role_id');

        $cinemaRes = $this->postJson('/api/cinemas', ['name' => 'Cinema Update', 'location' => 'HP']);
        $cinemaId = $cinemaRes->json('data.cinema_id');

        $empUser = User::create([
            'role_id' => Role::where('name', 'customer')->first()->role_id,
            'user_name' => 'emp_update',
            'full_name' => 'Employee Update',
            'email' => 'empupdate@test.com',
            'password' => Hash::make('Password@123'),
            'status' => 'IN_ACTIVE',
        ]);

        $createRes = $this->postJson('/api/employees', [
            'employee_role_id' => $empRoleId,
            'user_id' => $empUser->user_id,
            'cinema_id' => $cinemaId,
            'name' => 'Old Name',
            'hire_date' => '2026-01-15',
        ]);
        $empId = $createRes->json('data.employee_id');

        $response = $this->putJson("/api/employees/{$empId}", [
            'name' => 'New Name',
            'status' => 'UN_ACTIVE',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'New Name')
            ->assertJsonPath('data.status', 'UN_ACTIVE');
    }

    public function test_admin_can_delete_employee(): void
    {
        Sanctum::actingAs($this->admin);

        $roleRes = $this->postJson('/api/employee-roles', ['name' => 'STAFF']);
        $empRoleId = $roleRes->json('data.employee_role_id');

        $cinemaRes = $this->postJson('/api/cinemas', ['name' => 'Cinema Delete', 'location' => 'HN']);
        $cinemaId = $cinemaRes->json('data.cinema_id');

        $empUser = User::create([
            'role_id' => Role::where('name', 'customer')->first()->role_id,
            'user_name' => 'emp_delete',
            'full_name' => 'Employee Delete',
            'email' => 'empdelete@test.com',
            'password' => Hash::make('Password@123'),
            'status' => 'IN_ACTIVE',
        ]);

        $createRes = $this->postJson('/api/employees', [
            'employee_role_id' => $empRoleId,
            'user_id' => $empUser->user_id,
            'cinema_id' => $cinemaId,
            'name' => 'Delete Me',
            'hire_date' => '2026-01-15',
        ]);
        $empId = $createRes->json('data.employee_id');

        $response = $this->deleteJson("/api/employees/{$empId}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        // Employee uses SoftDeletes — row stays but deleted_at is set
        $this->assertSoftDeleted('employees', ['employee_id' => $empId]);
    }

    public function test_employee_user_id_must_be_unique(): void
    {
        Sanctum::actingAs($this->admin);

        $roleRes = $this->postJson('/api/employee-roles', ['name' => 'STAFF']);
        $empRoleId = $roleRes->json('data.employee_role_id');

        $cinemaRes = $this->postJson('/api/cinemas', ['name' => 'Cinema Unique', 'location' => 'HN']);
        $cinemaId = $cinemaRes->json('data.cinema_id');

        $empUser = User::create([
            'role_id' => Role::where('name', 'customer')->first()->role_id,
            'user_name' => 'emp_unique',
            'full_name' => 'Employee Unique',
            'email' => 'empunique@test.com',
            'password' => Hash::make('Password@123'),
            'status' => 'IN_ACTIVE',
        ]);

        // Tạo lần 1 — OK
        $this->postJson('/api/employees', [
            'employee_role_id' => $empRoleId,
            'user_id' => $empUser->user_id,
            'cinema_id' => $cinemaId,
            'name' => 'First',
            'hire_date' => '2026-01-15',
        ])->assertStatus(200);

        // Tạo lần 2 cùng user_id — bị reject
        $response = $this->postJson('/api/employees', [
            'employee_role_id' => $empRoleId,
            'user_id' => $empUser->user_id,
            'cinema_id' => $cinemaId,
            'name' => 'Second',
            'hire_date' => '2026-02-15',
        ]);

        $response->assertStatus(422);
    }

    // ======================== EMPLOYEE SALARIES ========================

    public function test_admin_can_create_employee_salary(): void
    {
        Sanctum::actingAs($this->admin);
        $empId = $this->createEmployeeViaApi();

        $response = $this->postJson('/api/employee-salaries', [
            'employee_id' => $empId,
            'bank_number' => '1234567890',
            'bank_name' => 'Vietcombank',
            'net_salary' => 15000000,
            'bonus' => 2000000,
            'total_earn' => 17000000,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.bank_name', 'Vietcombank');
    }

    public function test_admin_can_list_employee_salaries(): void
    {
        Sanctum::actingAs($this->admin);
        $empId = $this->createEmployeeViaApi();

        $this->postJson('/api/employee-salaries', [
            'employee_id' => $empId,
            'bank_number' => '1234567890',
            'bank_name' => 'Vietcombank',
            'net_salary' => 15000000,
            'total_earn' => 15000000,
        ]);

        $response = $this->getJson('/api/employee-salaries');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertCount(1, $response->json('data.items'));
    }

    public function test_admin_can_update_employee_salary(): void
    {
        Sanctum::actingAs($this->admin);
        $empId = $this->createEmployeeViaApi();

        $createRes = $this->postJson('/api/employee-salaries', [
            'employee_id' => $empId,
            'bank_number' => '1234567890',
            'bank_name' => 'Vietcombank',
            'net_salary' => 15000000,
            'total_earn' => 15000000,
        ]);
        $salaryId = $createRes->json('data.employee_salary_id');

        $response = $this->putJson("/api/employee-salaries/{$salaryId}", [
            'net_salary' => 20000000,
            'bonus' => 3000000,
            'total_earn' => 23000000,
            'payment_status' => 'IN_ACTIVE',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.payment_status', 'IN_ACTIVE');
    }

    public function test_admin_can_delete_employee_salary(): void
    {
        Sanctum::actingAs($this->admin);
        $empId = $this->createEmployeeViaApi();

        $createRes = $this->postJson('/api/employee-salaries', [
            'employee_id' => $empId,
            'bank_number' => '9876543210',
            'bank_name' => 'BIDV',
            'net_salary' => 10000000,
            'total_earn' => 10000000,
        ]);
        $salaryId = $createRes->json('data.employee_salary_id');

        $response = $this->deleteJson("/api/employee-salaries/{$salaryId}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('employee_salaries', ['employee_salary_id' => $salaryId]);
    }

    public function test_employee_salary_employee_id_must_be_unique(): void
    {
        Sanctum::actingAs($this->admin);
        $empId = $this->createEmployeeViaApi();

        // Tạo salary lần 1
        $this->postJson('/api/employee-salaries', [
            'employee_id' => $empId,
            'bank_number' => '1111111111',
            'bank_name' => 'VPBank',
            'net_salary' => 10000000,
            'total_earn' => 10000000,
        ])->assertStatus(201);

        // Tạo salary lần 2 cùng employee_id → reject
        $response = $this->postJson('/api/employee-salaries', [
            'employee_id' => $empId,
            'bank_number' => '2222222222',
            'bank_name' => 'ACB',
            'net_salary' => 12000000,
            'total_earn' => 12000000,
        ]);

        $response->assertStatus(422);
    }

    // ======================== AUTHORIZATION ========================

    public function test_customer_cannot_access_employee_routes(): void
    {
        Sanctum::actingAs($this->customer);

        $this->getJson('/api/employees')->assertStatus(403);
        $this->getJson('/api/employee-roles')->assertStatus(403);
        $this->getJson('/api/employee-salaries')->assertStatus(403);
    }

    // ======================== HELPERS ========================

    private function createEmployeeViaApi(): string
    {
        $roleRes = $this->postJson('/api/employee-roles', ['name' => 'STAFF']);
        $empRoleId = $roleRes->json('data.employee_role_id');

        $cinemaRes = $this->postJson('/api/cinemas', ['name' => 'Cinema Helper ' . uniqid(), 'location' => 'HCM']);
        $cinemaId = $cinemaRes->json('data.cinema_id');

        $empUser = User::create([
            'role_id' => Role::where('name', 'customer')->first()->role_id,
            'user_name' => 'emp_helper_'.uniqid(),
            'full_name' => 'Employee Helper',
            'email' => 'emphelper_'.uniqid().'@test.com',
            'password' => Hash::make('Password@123'),
            'status' => 'IN_ACTIVE',
        ]);

        $createRes = $this->postJson('/api/employees', [
            'employee_role_id' => $empRoleId,
            'user_id' => $empUser->user_id,
            'cinema_id' => $cinemaId,
            'name' => 'Helper Employee',
            'hire_date' => '2026-01-15',
        ]);

        return $createRes->json('data.employee_id');
    }
}
