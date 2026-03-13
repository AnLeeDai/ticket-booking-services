<?php

namespace Tests\Feature;

use App\Models\Cinema;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RbacManagerScopingTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $managerA;

    protected User $managerB;

    protected User $customer;

    protected string $cinemaAId;

    protected string $cinemaBId;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();
        $customerRole = Role::where('name', 'customer')->first();

        $this->admin = User::create([
            'role_id' => $adminRole->role_id,
            'user_name' => 'admin_rbac',
            'full_name' => 'Admin RBAC',
            'email' => 'admin_rbac@test.com',
            'password' => Hash::make('Password@123'),
            'status' => 'IN_ACTIVE',
        ]);

        $this->managerA = User::create([
            'role_id' => $managerRole->role_id,
            'user_name' => 'manager_a',
            'full_name' => 'Manager A',
            'email' => 'manager_a@test.com',
            'password' => Hash::make('Password@123'),
            'status' => 'IN_ACTIVE',
        ]);

        $this->managerB = User::create([
            'role_id' => $managerRole->role_id,
            'user_name' => 'manager_b',
            'full_name' => 'Manager B',
            'email' => 'manager_b@test.com',
            'password' => Hash::make('Password@123'),
            'status' => 'IN_ACTIVE',
        ]);

        $this->customer = User::create([
            'role_id' => $customerRole->role_id,
            'user_name' => 'customer_rbac',
            'full_name' => 'Customer RBAC',
            'email' => 'customer_rbac@test.com',
            'password' => Hash::make('Password@123'),
            'status' => 'IN_ACTIVE',
        ]);

        // Tạo cinema A (manager A quản lý)
        Sanctum::actingAs($this->admin);
        $cinemaA = $this->postJson('/api/cinemas', [
            'name' => 'Cinema A',
            'location' => 'HCM',
            'manager_id' => $this->managerA->user_id,
        ]);
        $this->cinemaAId = $cinemaA->json('data.cinema_id');

        // Tạo cinema B (manager B quản lý)
        $cinemaB = $this->postJson('/api/cinemas', [
            'name' => 'Cinema B',
            'location' => 'HN',
            'manager_id' => $this->managerB->user_id,
        ]);
        $this->cinemaBId = $cinemaB->json('data.cinema_id');
    }

    // ============================================================
    // CINEMA - Manager scoping
    // ============================================================

    public function test_manager_can_update_own_cinema(): void
    {
        Sanctum::actingAs($this->managerA);

        $response = $this->putJson("/api/cinemas/{$this->cinemaAId}", [
            'name' => 'Cinema A Updated',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Cinema A Updated');
    }

    public function test_manager_cannot_update_other_cinema(): void
    {
        Sanctum::actingAs($this->managerA);

        $response = $this->putJson("/api/cinemas/{$this->cinemaBId}", [
            'name' => 'Hacked',
        ]);

        $response->assertStatus(403);
    }

    public function test_manager_cannot_change_manager_id_or_active(): void
    {
        Sanctum::actingAs($this->managerA);

        $response = $this->putJson("/api/cinemas/{$this->cinemaAId}", [
            'name' => 'Updated Name',
            'manager_id' => $this->managerB->user_id,
            'active' => 'UN_ACTIVE',
        ]);

        // Vẫn thành công nhưng manager_id và active bị bỏ qua
        $response->assertStatus(200);
        $cinema = Cinema::find($this->cinemaAId);
        $this->assertEquals($this->managerA->user_id, $cinema->manager_id);
        $this->assertEquals('IN_ACTIVE', $cinema->active);
    }

    public function test_manager_cannot_create_cinema(): void
    {
        Sanctum::actingAs($this->managerA);

        $response = $this->postJson('/api/cinemas', [
            'name' => 'New Cinema',
            'location' => 'DN',
        ]);

        $response->assertStatus(403);
    }

    public function test_manager_cannot_delete_cinema(): void
    {
        Sanctum::actingAs($this->managerA);

        $response = $this->deleteJson("/api/cinemas/{$this->cinemaAId}");

        $response->assertStatus(403);
    }

    // ============================================================
    // SHOWTIMES - Manager scoping
    // ============================================================

    public function test_manager_can_create_showtime_for_own_cinema(): void
    {
        $movieId = $this->createMovieViaApi();

        Sanctum::actingAs($this->managerA);

        $response = $this->postJson('/api/showtimes', [
            'cinema_id' => $this->cinemaAId,
            'movie_id' => $movieId,
            'starts_at' => '2026-06-15 18:00:00',
            'ends_at' => '2026-06-15 20:00:00',
            'screen_type' => '2D',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);
    }

    public function test_manager_cannot_create_showtime_for_other_cinema(): void
    {
        $movieId = $this->createMovieViaApi();

        Sanctum::actingAs($this->managerA);

        $response = $this->postJson('/api/showtimes', [
            'cinema_id' => $this->cinemaBId,
            'movie_id' => $movieId,
            'starts_at' => '2026-07-01 18:00:00',
            'ends_at' => '2026-07-01 20:00:00',
            'screen_type' => '2D',
        ]);

        $response->assertStatus(403);
    }

    public function test_manager_cannot_delete_showtime_of_other_cinema(): void
    {
        // Admin tạo showtime cho cinema B
        $movieId = $this->createMovieViaApi();

        Sanctum::actingAs($this->admin);
        $showtimeRes = $this->postJson('/api/showtimes', [
            'cinema_id' => $this->cinemaBId,
            'movie_id' => $movieId,
            'starts_at' => '2026-08-01 18:00:00',
            'ends_at' => '2026-08-01 20:00:00',
            'screen_type' => '3D',
        ]);
        $showtimeId = $showtimeRes->json('data.showtime_id');

        // Manager A cố xoá showtime của cinema B
        Sanctum::actingAs($this->managerA);

        $response = $this->deleteJson("/api/showtimes/{$showtimeId}");

        $response->assertStatus(403);
    }

    // ============================================================
    // SEATS - Manager scoping
    // ============================================================

    public function test_manager_can_create_seat_for_own_cinema_showtime(): void
    {
        $showtimeId = $this->createShowtimeForCinema($this->cinemaAId);

        Sanctum::actingAs($this->managerA);

        $response = $this->postJson('/api/seats', [
            'showtime_id' => $showtimeId,
            'seat_code' => 'A1',
            'seat_type' => 'VIP',
            'price' => 150000,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);
    }

    public function test_manager_cannot_create_seat_for_other_cinema_showtime(): void
    {
        $showtimeId = $this->createShowtimeForCinema($this->cinemaBId);

        Sanctum::actingAs($this->managerA);

        $response = $this->postJson('/api/seats', [
            'showtime_id' => $showtimeId,
            'seat_code' => 'B1',
            'seat_type' => 'NORMAL',
            'price' => 80000,
        ]);

        $response->assertStatus(403);
    }

    // ============================================================
    // EMPLOYEES - Manager scoping
    // ============================================================

    public function test_manager_can_create_employee_for_own_cinema(): void
    {
        Sanctum::actingAs($this->admin);
        $roleRes = $this->postJson('/api/employee-roles', ['name' => 'STAFF']);
        $empRoleId = $roleRes->json('data.employee_role_id');

        $empUser = User::create([
            'role_id' => Role::where('name', 'customer')->first()->role_id,
            'user_name' => 'emp_mgr_own',
            'full_name' => 'Emp Manager Own',
            'email' => 'emp_mgr_own@test.com',
            'password' => Hash::make('Password@123'),
            'status' => 'IN_ACTIVE',
        ]);

        Sanctum::actingAs($this->managerA);

        $response = $this->postJson('/api/employees', [
            'employee_role_id' => $empRoleId,
            'user_id' => $empUser->user_id,
            'cinema_id' => $this->cinemaAId,
            'name' => 'Nhân viên A',
            'hire_date' => '2026-03-01',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_manager_cannot_create_employee_for_other_cinema(): void
    {
        Sanctum::actingAs($this->admin);
        $roleRes = $this->postJson('/api/employee-roles', ['name' => 'STAFF']);
        $empRoleId = $roleRes->json('data.employee_role_id');

        $empUser = User::create([
            'role_id' => Role::where('name', 'customer')->first()->role_id,
            'user_name' => 'emp_mgr_other',
            'full_name' => 'Emp Manager Other',
            'email' => 'emp_mgr_other@test.com',
            'password' => Hash::make('Password@123'),
            'status' => 'IN_ACTIVE',
        ]);

        Sanctum::actingAs($this->managerA);

        $response = $this->postJson('/api/employees', [
            'employee_role_id' => $empRoleId,
            'user_id' => $empUser->user_id,
            'cinema_id' => $this->cinemaBId,
            'name' => 'Hacked Employee',
            'hire_date' => '2026-03-01',
        ]);

        $response->assertStatus(403);
    }

    public function test_manager_can_list_only_own_cinema_employees(): void
    {
        Sanctum::actingAs($this->admin);
        $roleRes = $this->postJson('/api/employee-roles', ['name' => 'STAFF']);
        $empRoleId = $roleRes->json('data.employee_role_id');

        // Tạo employee cho cinema A
        $empUserA = User::create([
            'role_id' => Role::where('name', 'customer')->first()->role_id,
            'user_name' => 'emp_cinema_a',
            'full_name' => 'Emp Cinema A',
            'email' => 'emp_cinema_a@test.com',
            'password' => Hash::make('Password@123'),
            'status' => 'IN_ACTIVE',
        ]);
        $this->postJson('/api/employees', [
            'employee_role_id' => $empRoleId,
            'user_id' => $empUserA->user_id,
            'cinema_id' => $this->cinemaAId,
            'name' => 'Emp A',
            'hire_date' => '2026-01-01',
        ]);

        // Tạo employee cho cinema B
        $empUserB = User::create([
            'role_id' => Role::where('name', 'customer')->first()->role_id,
            'user_name' => 'emp_cinema_b',
            'full_name' => 'Emp Cinema B',
            'email' => 'emp_cinema_b@test.com',
            'password' => Hash::make('Password@123'),
            'status' => 'IN_ACTIVE',
        ]);
        $this->postJson('/api/employees', [
            'employee_role_id' => $empRoleId,
            'user_id' => $empUserB->user_id,
            'cinema_id' => $this->cinemaBId,
            'name' => 'Emp B',
            'hire_date' => '2026-01-01',
        ]);

        // Manager A chỉ thấy employee của cinema A
        Sanctum::actingAs($this->managerA);
        $response = $this->getJson('/api/employees');

        $response->assertStatus(200);
        $employees = $response->json('data.items');
        $names = collect($employees)->pluck('name')->toArray();
        $this->assertContains('Emp A', $names);
        $this->assertNotContains('Emp B', $names);
    }

    public function test_manager_cannot_view_employee_of_other_cinema(): void
    {
        Sanctum::actingAs($this->admin);
        $roleRes = $this->postJson('/api/employee-roles', ['name' => 'STAFF']);
        $empRoleId = $roleRes->json('data.employee_role_id');

        $empUser = User::create([
            'role_id' => Role::where('name', 'customer')->first()->role_id,
            'user_name' => 'emp_other_view',
            'full_name' => 'Emp Other View',
            'email' => 'emp_other_view@test.com',
            'password' => Hash::make('Password@123'),
            'status' => 'IN_ACTIVE',
        ]);

        $createRes = $this->postJson('/api/employees', [
            'employee_role_id' => $empRoleId,
            'user_id' => $empUser->user_id,
            'cinema_id' => $this->cinemaBId,
            'name' => 'Other Emp',
            'hire_date' => '2026-01-01',
        ]);
        $empId = $createRes->json('data.employee_id');

        Sanctum::actingAs($this->managerA);

        $response = $this->getJson("/api/employees/{$empId}");

        $response->assertStatus(403);
    }

    // ============================================================
    // CINEMA SALES - Manager scoping
    // ============================================================

    public function test_manager_can_create_sale_for_own_cinema(): void
    {
        Sanctum::actingAs($this->managerA);

        $response = $this->postJson('/api/cinema-sales', [
            'cinema_id' => $this->cinemaAId,
            'sale_date' => '2026-06-01',
            'gross_amount' => 10000000,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);
    }

    public function test_manager_cannot_create_sale_for_other_cinema(): void
    {
        Sanctum::actingAs($this->managerA);

        $response = $this->postJson('/api/cinema-sales', [
            'cinema_id' => $this->cinemaBId,
            'sale_date' => '2026-06-01',
            'gross_amount' => 5000000,
        ]);

        $response->assertStatus(403);
    }

    public function test_manager_can_list_only_own_cinema_sales(): void
    {
        Sanctum::actingAs($this->admin);

        // Tạo sale cho cinema A
        $this->postJson('/api/cinema-sales', [
            'cinema_id' => $this->cinemaAId,
            'sale_date' => '2026-06-01',
            'gross_amount' => 10000000,
        ]);
        // Tạo sale cho cinema B
        $this->postJson('/api/cinema-sales', [
            'cinema_id' => $this->cinemaBId,
            'sale_date' => '2026-06-01',
            'gross_amount' => 8000000,
        ]);

        // Manager A chỉ thấy sale của cinema A
        Sanctum::actingAs($this->managerA);
        $response = $this->getJson('/api/cinema-sales');

        $response->assertStatus(200);
        $sales = $response->json('data.items');
        $cinemaIds = collect($sales)->pluck('cinema_id')->unique()->toArray();
        $this->assertContains($this->cinemaAId, $cinemaIds);
        $this->assertNotContains($this->cinemaBId, $cinemaIds);
    }

    // ============================================================
    // CUSTOMER - Không được truy cập route quản lý
    // ============================================================

    public function test_customer_cannot_access_employees(): void
    {
        Sanctum::actingAs($this->customer);

        $this->getJson('/api/employees')->assertStatus(403);
    }

    public function test_customer_cannot_access_employee_salaries(): void
    {
        Sanctum::actingAs($this->customer);

        $this->getJson('/api/employee-salaries')->assertStatus(403);
    }

    public function test_customer_cannot_access_cinema_sales(): void
    {
        Sanctum::actingAs($this->customer);

        $this->getJson('/api/cinema-sales')->assertStatus(403);
    }

    public function test_customer_cannot_access_payments(): void
    {
        Sanctum::actingAs($this->customer);

        $this->getJson('/api/payments')->assertStatus(403);
    }

    public function test_customer_cannot_create_showtime(): void
    {
        Sanctum::actingAs($this->customer);

        $this->postJson('/api/showtimes', [
            'cinema_id' => $this->cinemaAId,
            'movie_id' => 'fake-id',
            'starts_at' => '2026-06-15 18:00:00',
            'ends_at' => '2026-06-15 20:00:00',
            'screen_type' => '2D',
        ])->assertStatus(403);
    }

    public function test_customer_cannot_create_seat(): void
    {
        Sanctum::actingAs($this->customer);

        $this->postJson('/api/seats', [
            'showtime_id' => 'fake-id',
            'seat_code' => 'A1',
            'seat_type' => 'VIP',
            'price' => 100000,
        ])->assertStatus(403);
    }

    public function test_customer_cannot_list_tickets_admin_route(): void
    {
        Sanctum::actingAs($this->customer);

        $this->getJson('/api/tickets')->assertStatus(403);
    }

    public function test_customer_cannot_create_employee(): void
    {
        Sanctum::actingAs($this->customer);

        $this->postJson('/api/employees', [
            'name' => 'Hack',
        ])->assertStatus(403);
    }

    // ============================================================
    // EMPLOYEE SALARY - Manager scoping
    // ============================================================

    public function test_manager_can_create_salary_for_own_cinema_employee(): void
    {
        Sanctum::actingAs($this->admin);
        $roleRes = $this->postJson('/api/employee-roles', ['name' => 'STAFF']);
        $empRoleId = $roleRes->json('data.employee_role_id');

        $empUser = User::create([
            'role_id' => Role::where('name', 'customer')->first()->role_id,
            'user_name' => 'emp_salary_own',
            'full_name' => 'Emp Salary Own',
            'email' => 'emp_salary_own@test.com',
            'password' => Hash::make('Password@123'),
            'status' => 'IN_ACTIVE',
        ]);

        $empRes = $this->postJson('/api/employees', [
            'employee_role_id' => $empRoleId,
            'user_id' => $empUser->user_id,
            'cinema_id' => $this->cinemaAId,
            'name' => 'Emp Salary',
            'hire_date' => '2026-01-01',
        ]);
        $empId = $empRes->json('data.employee_id');

        Sanctum::actingAs($this->managerA);

        $response = $this->postJson('/api/employee-salaries', [
            'employee_id' => $empId,
            'net_salary' => 8000000,
            'total_earn' => 8500000,
            'payment_status' => 'IS_PENDING',
            'bank_name' => 'Vietcombank',
            'bank_number' => '123456789',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);
    }

    public function test_manager_cannot_create_salary_for_other_cinema_employee(): void
    {
        Sanctum::actingAs($this->admin);
        $roleRes = $this->postJson('/api/employee-roles', ['name' => 'STAFF']);
        $empRoleId = $roleRes->json('data.employee_role_id');

        $empUser = User::create([
            'role_id' => Role::where('name', 'customer')->first()->role_id,
            'user_name' => 'emp_salary_other',
            'full_name' => 'Emp Salary Other',
            'email' => 'emp_salary_other@test.com',
            'password' => Hash::make('Password@123'),
            'status' => 'IN_ACTIVE',
        ]);

        $empRes = $this->postJson('/api/employees', [
            'employee_role_id' => $empRoleId,
            'user_id' => $empUser->user_id,
            'cinema_id' => $this->cinemaBId,
            'name' => 'Emp Other Salary',
            'hire_date' => '2026-01-01',
        ]);
        $empId = $empRes->json('data.employee_id');

        Sanctum::actingAs($this->managerA);

        $response = $this->postJson('/api/employee-salaries', [
            'employee_id' => $empId,
            'net_salary' => 8000000,
            'total_earn' => 8500000,
            'payment_status' => 'IS_PENDING',
            'bank_name' => 'ACB',
            'bank_number' => '987654321',
        ]);

        $response->assertStatus(403);
    }

    // ============================================================
    // TICKETS & PAYMENTS - Manager scoping
    // ============================================================

    public function test_manager_can_confirm_payment_for_own_cinema(): void
    {
        // Tạo showtime + seat cho cinema A
        $showtimeId = $this->createShowtimeForCinema($this->cinemaAId);

        Sanctum::actingAs($this->admin);
        $seatRes = $this->postJson('/api/seats', [
            'showtime_id' => $showtimeId,
            'seat_code' => 'C1',
            'seat_type' => 'NORMAL',
            'price' => 75000,
        ]);
        $seatId = $seatRes->json('data.seat_id');

        // Customer đặt vé
        Sanctum::actingAs($this->customer);
        $bookRes = $this->postJson('/api/tickets/book', [
            'showtime_id' => $showtimeId,
            'seat_id' => $seatId,
            'payment_method' => 'CASH',
        ]);
        $ticketId = $bookRes->json('data.ticket_id');

        // Manager A confirm payment
        Sanctum::actingAs($this->managerA);
        $response = $this->postJson("/api/tickets/{$ticketId}/confirm-payment");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_manager_cannot_confirm_payment_for_other_cinema(): void
    {
        // Tạo showtime + seat cho cinema B
        $showtimeId = $this->createShowtimeForCinema($this->cinemaBId);

        Sanctum::actingAs($this->admin);
        $seatRes = $this->postJson('/api/seats', [
            'showtime_id' => $showtimeId,
            'seat_code' => 'D1',
            'seat_type' => 'VIP',
            'price' => 120000,
        ]);
        $seatId = $seatRes->json('data.seat_id');

        // Customer đặt vé
        Sanctum::actingAs($this->customer);
        $bookRes = $this->postJson('/api/tickets/book', [
            'showtime_id' => $showtimeId,
            'seat_id' => $seatId,
            'payment_method' => 'TRANSFER',
        ]);
        $ticketId = $bookRes->json('data.ticket_id');

        // Manager A (cinema A) cố confirm payment ở cinema B
        Sanctum::actingAs($this->managerA);
        $response = $this->postJson("/api/tickets/{$ticketId}/confirm-payment");

        $response->assertStatus(403);
    }

    // ============================================================
    // ADMIN - Full access
    // ============================================================

    public function test_admin_can_access_all_cinemas(): void
    {
        Sanctum::actingAs($this->admin);

        // Admin xem employee của cả 2 cinema mà không bị chặn
        $roleRes = $this->postJson('/api/employee-roles', ['name' => 'STAFF']);
        $empRoleId = $roleRes->json('data.employee_role_id');

        $empA = User::create([
            'role_id' => Role::where('name', 'customer')->first()->role_id,
            'user_name' => 'emp_admin_a',
            'full_name' => 'Emp Admin A',
            'email' => 'emp_admin_a@test.com',
            'password' => Hash::make('Password@123'),
            'status' => 'IN_ACTIVE',
        ]);
        $this->postJson('/api/employees', [
            'employee_role_id' => $empRoleId,
            'user_id' => $empA->user_id,
            'cinema_id' => $this->cinemaAId,
            'name' => 'Admin Emp A',
            'hire_date' => '2026-01-01',
        ]);

        $empB = User::create([
            'role_id' => Role::where('name', 'customer')->first()->role_id,
            'user_name' => 'emp_admin_b',
            'full_name' => 'Emp Admin B',
            'email' => 'emp_admin_b@test.com',
            'password' => Hash::make('Password@123'),
            'status' => 'IN_ACTIVE',
        ]);
        $this->postJson('/api/employees', [
            'employee_role_id' => $empRoleId,
            'user_id' => $empB->user_id,
            'cinema_id' => $this->cinemaBId,
            'name' => 'Admin Emp B',
            'hire_date' => '2026-01-01',
        ]);

        $response = $this->getJson('/api/employees');
        $response->assertStatus(200);
        $employees = $response->json('data.items');
        $names = collect($employees)->pluck('name')->toArray();
        $this->assertContains('Admin Emp A', $names);
        $this->assertContains('Admin Emp B', $names);
    }

    // ============================================================
    // EMPLOYEE ROLE - chỉ admin mới được CRUD
    // ============================================================

    public function test_manager_cannot_create_employee_role(): void
    {
        Sanctum::actingAs($this->managerA);

        $this->postJson('/api/employee-roles', ['name' => 'INTERN'])
            ->assertStatus(403);
    }

    public function test_manager_can_view_employee_roles(): void
    {
        Sanctum::actingAs($this->admin);
        $this->postJson('/api/employee-roles', ['name' => 'STAFF']);

        Sanctum::actingAs($this->managerA);
        $response = $this->getJson('/api/employee-roles');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    // ============================================================
    // HELPERS
    // ============================================================

    /**
     * Tạo movie qua API (phải actingAs admin trước khi gọi).
     */
    private function createMovieViaApi(): string
    {
        Sanctum::actingAs($this->admin);

        $catRes = $this->postJson('/api/categories', ['name' => 'Cat '.uniqid()]);
        $categoryId = $catRes->json('data.id');

        $movieRes = $this->postJson('/api/movies', [
            'category_ids' => [$categoryId],
            'title' => 'Movie '.uniqid(),
            'name' => 'Movie '.uniqid(),
            'thumb_url' => 'https://example.com/thumb.jpg',
            'trailer_url' => 'https://example.com/trailer.mp4',
            'duration' => 120,
            'language' => 'Tiếng Việt',
            'age' => 13,
            'release_date' => '2026-03-15',
            'status' => 'IN_ACTIVE',
        ]);

        return $movieRes->json('data.movie_id');
    }

    /**
     * Tạo showtime cho cinema qua API.
     */
    private function createShowtimeForCinema(string $cinemaId): string
    {
        $movieId = $this->createMovieViaApi();

        Sanctum::actingAs($this->admin);

        $showtimeRes = $this->postJson('/api/showtimes', [
            'cinema_id' => $cinemaId,
            'movie_id' => $movieId,
            'starts_at' => '2026-06-15 18:00:00',
            'ends_at' => '2026-06-15 20:00:00',
            'screen_type' => '2D',
        ]);

        return $showtimeRes->json('data.showtime_id');
    }
}
