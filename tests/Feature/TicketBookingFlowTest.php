<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Cinema;
use App\Models\Combo;
use App\Models\Movie;
use App\Models\Role;
use App\Models\Seat;
use App\Models\Showtime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Test toàn bộ luồng đặt vé - tất cả dữ liệu tạo qua controller API.
 */
class TicketBookingFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        // Roles đã được seed trong migration
        $adminRole = Role::where('name', 'admin')->first();
        $customerRole = Role::where('name', 'customer')->first();
        $this->admin = User::create([
            'role_id' => $adminRole->role_id,
            'user_name' => 'admin_test',
            'full_name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => Hash::make('Password@123'),
            'status' => 'IN_ACTIVE',
        ]);

        // Tạo customer user
        $this->customer = User::create([
            'role_id' => $customerRole->role_id,
            'user_name' => 'customer_test',
            'full_name' => 'Customer Test',
            'email' => 'customer@test.com',
            'password' => Hash::make('Password@123'),
            'status' => 'IN_ACTIVE',
        ]);
    }

    // ======================== CATEGORY ========================

    public function test_admin_can_create_category(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/categories', [
            'name' => 'Hành động',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Hành động')
            ->assertJsonStructure(['success', 'message', 'data' => ['id', 'name', 'slug']]);
    }

    public function test_public_can_list_categories(): void
    {
        // Tạo data qua API (admin)
        Sanctum::actingAs($this->admin);
        $this->postJson('/api/categories', ['name' => 'Hành động']);
        $this->postJson('/api/categories', ['name' => 'Kinh dị']);

        // Public GET không cần auth
        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $items = $response->json('data.items');
        $this->assertCount(2, $items);
    }

    // ======================== CINEMA ========================

    public function test_admin_can_create_cinema(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/cinemas', [
            'name' => 'CGV Landmark 81',
            'location' => 'Quận Bình Thạnh, TP.HCM',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'CGV Landmark 81')
            ->assertJsonStructure(['success', 'message', 'data' => ['cinema_id', 'code', 'name', 'location', 'active']]);
    }

    public function test_admin_can_update_cinema(): void
    {
        Sanctum::actingAs($this->admin);

        $create = $this->postJson('/api/cinemas', [
            'name' => 'CGV Test',
            'location' => 'HN',
        ]);
        $cinemaId = $create->json('data.cinema_id');

        $response = $this->putJson("/api/cinemas/{$cinemaId}", [
            'name' => 'CGV Updated',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'CGV Updated');
    }

    public function test_admin_can_delete_cinema(): void
    {
        Sanctum::actingAs($this->admin);

        $create = $this->postJson('/api/cinemas', [
            'name' => 'CGV Xoá',
            'location' => 'Test',
        ]);
        $cinemaId = $create->json('data.cinema_id');

        $response = $this->deleteJson("/api/cinemas/{$cinemaId}");
        $response->assertStatus(200)->assertJsonPath('success', true);

        // Kiểm tra không còn trong DB
        $this->assertDatabaseMissing('cinemas', ['cinema_id' => $cinemaId]);
    }

    // ======================== COMBO ========================

    public function test_admin_can_create_combo(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/combos', [
            'name' => 'Combo Bắp Nước',
            'price' => 89000,
            'stock' => 100,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Combo Bắp Nước');
    }

    public function test_admin_can_update_combo(): void
    {
        Sanctum::actingAs($this->admin);

        $create = $this->postJson('/api/combos', [
            'name' => 'Combo Update Test',
            'price' => 50000,
            'stock' => 50,
        ]);
        $comboId = $create->json('data.combo_id');

        $response = $this->putJson("/api/combos/{$comboId}", [
            'price' => 65000,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.price', '65000');
    }

    // ======================== MOVIE ========================

    public function test_admin_can_create_movie(): void
    {
        Sanctum::actingAs($this->admin);

        // Tạo category qua API
        $catRes = $this->postJson('/api/categories', ['name' => 'Hành động']);
        $categoryId = $catRes->json('data.id');

        $response = $this->postJson('/api/movies', [
            'category_ids' => [$categoryId],
            'title' => 'Avengers: Endgame',
            'name' => 'Avengers Endgame',
            'description' => 'Phim siêu anh hùng Marvel',
            'thumb_url' => 'https://example.com/avengers.jpg',
            'trailer_url' => 'https://example.com/avengers-trailer.mp4',
            'duration' => 181,
            'language' => 'Tiếng Anh',
            'age' => 13,
            'rating' => 8.5,
            'release_date' => '2026-03-15',
            'status' => 'IN_ACTIVE',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'Avengers: Endgame')
            ->assertJsonStructure(['success', 'message', 'data' => ['movie_id', 'code', 'slug', 'categories']]);
    }

    public function test_public_can_get_movie_detail(): void
    {
        Sanctum::actingAs($this->admin);

        $catRes = $this->postJson('/api/categories', ['name' => 'Phiêu lưu']);
        $categoryId = $catRes->json('data.id');

        $movieRes = $this->postJson('/api/movies', [
            'category_ids' => [$categoryId],
            'title' => 'Movie Detail Test',
            'name' => 'Movie Detail',
            'thumb_url' => 'https://example.com/test.jpg',
            'trailer_url' => 'https://example.com/test.mp4',
            'duration' => 120,
            'language' => 'Tiếng Việt',
            'age' => 16,
            'release_date' => '2026-04-01',
            'status' => 'IN_ACTIVE',
        ]);
        $movieId = $movieRes->json('data.movie_id');

        // Public GET
        $response = $this->getJson("/api/movies/{$movieId}");
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.movie_id', $movieId);
    }

    // ======================== SHOWTIME ========================

    public function test_admin_can_create_showtime(): void
    {
        Sanctum::actingAs($this->admin);

        // Tạo cinema + movie qua API
        $cinemaRes = $this->postJson('/api/cinemas', [
            'name' => 'CGV Showtime Test',
            'location' => 'HCM',
        ]);
        $cinemaId = $cinemaRes->json('data.cinema_id');

        $catRes = $this->postJson('/api/categories', ['name' => 'Hài hước']);
        $categoryId = $catRes->json('data.id');

        $movieRes = $this->postJson('/api/movies', [
            'category_ids' => [$categoryId],
            'title' => 'Showtime Test Movie',
            'name' => 'Showtime Test',
            'thumb_url' => 'https://example.com/st.jpg',
            'trailer_url' => 'https://example.com/st.mp4',
            'duration' => 100,
            'language' => 'Tiếng Việt',
            'age' => 13,
            'release_date' => '2026-03-15',
            'status' => 'IN_ACTIVE',
        ]);
        $movieId = $movieRes->json('data.movie_id');

        $response = $this->postJson('/api/showtimes', [
            'cinema_id' => $cinemaId,
            'movie_id' => $movieId,
            'starts_at' => '2026-03-20 14:00:00',
            'ends_at' => '2026-03-20 16:00:00',
            'screen_type' => '2D',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['success', 'message', 'data' => ['showtime_id', 'cinema_id', 'movie_id', 'starts_at', 'ends_at', 'screen_type']]);
    }

    // ======================== SEAT ========================

    public function test_admin_can_create_seat(): void
    {
        Sanctum::actingAs($this->admin);
        $showtimeId = $this->createShowtimeViaApi();

        $response = $this->postJson('/api/seats', [
            'showtime_id' => $showtimeId,
            'seat_code' => 'A1',
            'seat_type' => 'NORMAL',
            'price' => 75000,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.seat_code', 'A1');
    }

    public function test_admin_can_bulk_create_seats(): void
    {
        Sanctum::actingAs($this->admin);
        $showtimeId = $this->createShowtimeViaApi();

        $response = $this->postJson('/api/seats/bulk', [
            'showtime_id' => $showtimeId,
            'seats' => [
                ['seat_code' => 'A1', 'seat_type' => 'NORMAL', 'price' => 75000],
                ['seat_code' => 'A2', 'seat_type' => 'NORMAL', 'price' => 75000],
                ['seat_code' => 'B1', 'seat_type' => 'VIP', 'price' => 120000],
                ['seat_code' => 'C1', 'seat_type' => 'COUPLE', 'price' => 200000],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseCount('seats', 4);
    }

    public function test_bulk_seats_rejects_duplicate_codes(): void
    {
        Sanctum::actingAs($this->admin);
        $showtimeId = $this->createShowtimeViaApi();

        $response = $this->postJson('/api/seats/bulk', [
            'showtime_id' => $showtimeId,
            'seats' => [
                ['seat_code' => 'A1', 'seat_type' => 'NORMAL', 'price' => 75000],
                ['seat_code' => 'A1', 'seat_type' => 'VIP', 'price' => 120000],
            ],
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('success', false);
    }

    public function test_get_seats_by_showtime(): void
    {
        Sanctum::actingAs($this->admin);
        $showtimeId = $this->createShowtimeViaApi();

        $this->postJson('/api/seats/bulk', [
            'showtime_id' => $showtimeId,
            'seats' => [
                ['seat_code' => 'A1', 'seat_type' => 'NORMAL', 'price' => 75000],
                ['seat_code' => 'A2', 'seat_type' => 'VIP', 'price' => 120000],
            ],
        ]);

        $response = $this->getJson("/api/seats/showtime/{$showtimeId}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $items = $response->json('data.items');
        $this->assertCount(2, $items);
    }

    // ======================== BOOKING FLOW ========================

    public function test_customer_can_book_ticket_without_combos(): void
    {
        // 1. Admin tạo dữ liệu
        Sanctum::actingAs($this->admin);
        $showtimeId = $this->createShowtimeViaApi();

        $seatRes = $this->postJson('/api/seats', [
            'showtime_id' => $showtimeId,
            'seat_code' => 'A1',
            'seat_type' => 'NORMAL',
            'price' => 75000,
        ]);
        $seatId = $seatRes->json('data.seat_id');

        // 2. Customer đặt vé
        Sanctum::actingAs($this->customer);

        $response = $this->postJson('/api/tickets/book', [
            'showtime_id' => $showtimeId,
            'seat_id' => $seatId,
            'payment_method' => 'TRANSFER',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'IS_PENDING')
            ->assertJsonPath('data.price', '75000')
            ->assertJsonStructure([
                'success', 'message',
                'data' => [
                    'ticket_id', 'code', 'price', 'status',
                    'seat', 'showtime', 'movie', 'payment',
                ],
            ]);

        // Ghế đã SOLD
        $this->assertDatabaseHas('seats', ['seat_id' => $seatId, 'active' => 'SOLD']);

        // Payment được tạo
        $ticketId = $response->json('data.ticket_id');
        $this->assertDatabaseHas('payments', [
            'ticket_id' => $ticketId,
            'method' => 'TRANSFER',
            'status' => 'IS_PENDING',
        ]);
    }

    public function test_customer_can_book_ticket_with_combos(): void
    {
        // 1. Admin setup
        Sanctum::actingAs($this->admin);
        $showtimeId = $this->createShowtimeViaApi();

        $seatRes = $this->postJson('/api/seats', [
            'showtime_id' => $showtimeId,
            'seat_code' => 'B1',
            'seat_type' => 'VIP',
            'price' => 120000,
        ]);
        $seatId = $seatRes->json('data.seat_id');

        $comboRes = $this->postJson('/api/combos', [
            'name' => 'Combo Bắp Lớn',
            'price' => 89000,
            'stock' => 50,
        ]);
        $comboId = $comboRes->json('data.combo_id');

        // 2. Customer đặt vé + combo
        Sanctum::actingAs($this->customer);

        $response = $this->postJson('/api/tickets/book', [
            'showtime_id' => $showtimeId,
            'seat_id' => $seatId,
            'payment_method' => 'CARD',
            'combos' => [
                ['combo_id' => $comboId, 'qty' => 2],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        // Giá = seat(120000) + combo(89000 * 2) = 298000
        $this->assertEquals('298000', $response->json('data.price'));

        // Stock giảm
        $this->assertDatabaseHas('combos', ['combo_id' => $comboId, 'stock' => 48]);

        // ticket_combos pivot
        $this->assertDatabaseHas('ticket_combos', [
            'ticket_id' => $response->json('data.ticket_id'),
            'combo_id' => $comboId,
            'qty' => 2,
        ]);
    }

    public function test_cannot_book_sold_seat(): void
    {
        Sanctum::actingAs($this->admin);
        $showtimeId = $this->createShowtimeViaApi();

        $seatRes = $this->postJson('/api/seats', [
            'showtime_id' => $showtimeId,
            'seat_code' => 'A1',
            'seat_type' => 'NORMAL',
            'price' => 75000,
        ]);
        $seatId = $seatRes->json('data.seat_id');

        // Customer 1 mua
        Sanctum::actingAs($this->customer);
        $this->postJson('/api/tickets/book', [
            'showtime_id' => $showtimeId,
            'seat_id' => $seatId,
            'payment_method' => 'CASH',
        ]);

        // Customer 2 mua cùng ghế
        $customer2 = User::create([
            'role_id' => Role::where('name', 'customer')->first()->role_id,
            'user_name' => 'customer2',
            'full_name' => 'Customer 2',
            'email' => 'customer2@test.com',
            'password' => Hash::make('Password@123'),
            'status' => 'IN_ACTIVE',
        ]);
        Sanctum::actingAs($customer2);

        $response = $this->postJson('/api/tickets/book', [
            'showtime_id' => $showtimeId,
            'seat_id' => $seatId,
            'payment_method' => 'CASH',
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('success', false);
    }

    public function test_cannot_book_inactive_seat(): void
    {
        Sanctum::actingAs($this->admin);
        $showtimeId = $this->createShowtimeViaApi();

        $seatRes = $this->postJson('/api/seats', [
            'showtime_id' => $showtimeId,
            'seat_code' => 'X1',
            'seat_type' => 'NORMAL',
            'price' => 75000,
            'active' => 'UN_ACTIVE',
        ]);
        $seatId = $seatRes->json('data.seat_id');

        Sanctum::actingAs($this->customer);
        $response = $this->postJson('/api/tickets/book', [
            'showtime_id' => $showtimeId,
            'seat_id' => $seatId,
            'payment_method' => 'CASH',
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('success', false);
    }

    // ======================== CONFIRM PAYMENT ========================

    public function test_admin_can_confirm_payment(): void
    {
        // Setup + book ticket
        Sanctum::actingAs($this->admin);
        $showtimeId = $this->createShowtimeViaApi();

        $seatRes = $this->postJson('/api/seats', [
            'showtime_id' => $showtimeId,
            'seat_code' => 'A1',
            'seat_type' => 'NORMAL',
            'price' => 75000,
        ]);
        $seatId = $seatRes->json('data.seat_id');

        Sanctum::actingAs($this->customer);
        $bookRes = $this->postJson('/api/tickets/book', [
            'showtime_id' => $showtimeId,
            'seat_id' => $seatId,
            'payment_method' => 'TRANSFER',
        ]);
        $ticketId = $bookRes->json('data.ticket_id');

        // Admin confirm
        Sanctum::actingAs($this->admin);
        $response = $this->postJson("/api/tickets/{$ticketId}/confirm-payment");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('tickets', ['ticket_id' => $ticketId, 'status' => 'IN_ACTIVE']);
        $this->assertDatabaseHas('payments', ['ticket_id' => $ticketId, 'status' => 'IN_ACTIVE']);
    }

    // ======================== CANCEL TICKET ========================

    public function test_customer_can_cancel_own_ticket(): void
    {
        Sanctum::actingAs($this->admin);
        $showtimeId = $this->createShowtimeViaApi();

        $comboRes = $this->postJson('/api/combos', [
            'name' => 'Combo Cancel Test',
            'price' => 50000,
            'stock' => 20,
        ]);
        $comboId = $comboRes->json('data.combo_id');

        $seatRes = $this->postJson('/api/seats', [
            'showtime_id' => $showtimeId,
            'seat_code' => 'A1',
            'seat_type' => 'NORMAL',
            'price' => 75000,
        ]);
        $seatId = $seatRes->json('data.seat_id');

        // Book
        Sanctum::actingAs($this->customer);
        $bookRes = $this->postJson('/api/tickets/book', [
            'showtime_id' => $showtimeId,
            'seat_id' => $seatId,
            'payment_method' => 'CASH',
            'combos' => [
                ['combo_id' => $comboId, 'qty' => 3],
            ],
        ]);
        $ticketId = $bookRes->json('data.ticket_id');

        // Cancel
        $response = $this->postJson("/api/tickets/{$ticketId}/cancel");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        // Ticket cancelled
        $this->assertDatabaseHas('tickets', ['ticket_id' => $ticketId, 'status' => 'UN_ACTIVE']);

        // Seat released
        $this->assertDatabaseHas('seats', ['seat_id' => $seatId, 'active' => 'IN_ACTIVE']);

        // Combo stock restored
        $this->assertDatabaseHas('combos', ['combo_id' => $comboId, 'stock' => 20]);

        // Payment cancelled
        $this->assertDatabaseHas('payments', ['ticket_id' => $ticketId, 'status' => 'UN_ACTIVE']);
    }

    public function test_customer_cannot_cancel_another_users_ticket(): void
    {
        Sanctum::actingAs($this->admin);
        $showtimeId = $this->createShowtimeViaApi();

        $seatRes = $this->postJson('/api/seats', [
            'showtime_id' => $showtimeId,
            'seat_code' => 'A1',
            'seat_type' => 'NORMAL',
            'price' => 75000,
        ]);
        $seatId = $seatRes->json('data.seat_id');

        // Customer 1 book
        Sanctum::actingAs($this->customer);
        $bookRes = $this->postJson('/api/tickets/book', [
            'showtime_id' => $showtimeId,
            'seat_id' => $seatId,
            'payment_method' => 'CASH',
        ]);
        $ticketId = $bookRes->json('data.ticket_id');

        // Customer 2 try to cancel
        $customer2 = User::create([
            'role_id' => Role::where('name', 'customer')->first()->role_id,
            'user_name' => 'customer2_cancel',
            'full_name' => 'Customer 2',
            'email' => 'customer2cancel@test.com',
            'password' => Hash::make('Password@123'),
            'status' => 'IN_ACTIVE',
        ]);
        Sanctum::actingAs($customer2);

        $response = $this->postJson("/api/tickets/{$ticketId}/cancel");
        $response->assertStatus(403);
    }

    // ======================== MY TICKETS ========================

    public function test_customer_can_view_own_tickets(): void
    {
        Sanctum::actingAs($this->admin);
        $showtimeId = $this->createShowtimeViaApi();

        // Tạo 2 ghế
        $seat1 = $this->postJson('/api/seats', [
            'showtime_id' => $showtimeId,
            'seat_code' => 'A1',
            'seat_type' => 'NORMAL',
            'price' => 75000,
        ])->json('data.seat_id');

        $seat2 = $this->postJson('/api/seats', [
            'showtime_id' => $showtimeId,
            'seat_code' => 'A2',
            'seat_type' => 'NORMAL',
            'price' => 75000,
        ])->json('data.seat_id');

        // Customer đặt 2 vé
        Sanctum::actingAs($this->customer);
        $this->postJson('/api/tickets/book', [
            'showtime_id' => $showtimeId,
            'seat_id' => $seat1,
            'payment_method' => 'CASH',
        ]);
        $this->postJson('/api/tickets/book', [
            'showtime_id' => $showtimeId,
            'seat_id' => $seat2,
            'payment_method' => 'CARD',
        ]);

        $response = $this->getJson('/api/tickets/my-tickets');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $items = $response->json('data.items');
        $this->assertCount(2, $items);
    }

    // ======================== ADMIN TICKET LIST ========================

    public function test_admin_can_list_all_tickets(): void
    {
        Sanctum::actingAs($this->admin);
        $showtimeId = $this->createShowtimeViaApi();

        $seatRes = $this->postJson('/api/seats', [
            'showtime_id' => $showtimeId,
            'seat_code' => 'A1',
            'seat_type' => 'NORMAL',
            'price' => 75000,
        ]);
        $seatId = $seatRes->json('data.seat_id');

        Sanctum::actingAs($this->customer);
        $this->postJson('/api/tickets/book', [
            'showtime_id' => $showtimeId,
            'seat_id' => $seatId,
            'payment_method' => 'CASH',
        ]);

        // Admin list
        Sanctum::actingAs($this->admin);
        $response = $this->getJson('/api/tickets');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $items = $response->json('data.items');
        $this->assertCount(1, $items);
    }

    public function test_admin_can_get_ticket_detail(): void
    {
        Sanctum::actingAs($this->admin);
        $showtimeId = $this->createShowtimeViaApi();

        $seatRes = $this->postJson('/api/seats', [
            'showtime_id' => $showtimeId,
            'seat_code' => 'A1',
            'seat_type' => 'NORMAL',
            'price' => 75000,
        ]);
        $seatId = $seatRes->json('data.seat_id');

        Sanctum::actingAs($this->customer);
        $bookRes = $this->postJson('/api/tickets/book', [
            'showtime_id' => $showtimeId,
            'seat_id' => $seatId,
            'payment_method' => 'CASH',
        ]);
        $ticketId = $bookRes->json('data.ticket_id');

        Sanctum::actingAs($this->admin);
        $response = $this->getJson("/api/tickets/{$ticketId}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.ticket_id', $ticketId)
            ->assertJsonStructure([
                'success', 'message',
                'data' => ['ticket_id', 'code', 'price', 'status', 'seat', 'showtime', 'movie', 'payment'],
            ]);
    }

    // ======================== PAYMENTS ========================

    public function test_admin_can_list_payments(): void
    {
        Sanctum::actingAs($this->admin);
        $showtimeId = $this->createShowtimeViaApi();

        $seatRes = $this->postJson('/api/seats', [
            'showtime_id' => $showtimeId,
            'seat_code' => 'A1',
            'seat_type' => 'NORMAL',
            'price' => 75000,
        ]);
        $seatId = $seatRes->json('data.seat_id');

        Sanctum::actingAs($this->customer);
        $this->postJson('/api/tickets/book', [
            'showtime_id' => $showtimeId,
            'seat_id' => $seatId,
            'payment_method' => 'TRANSFER',
        ]);

        Sanctum::actingAs($this->admin);
        $response = $this->getJson('/api/payments');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $items = $response->json('data.items');
        $this->assertCount(1, $items);
    }

    public function test_admin_can_get_payment_detail(): void
    {
        Sanctum::actingAs($this->admin);
        $showtimeId = $this->createShowtimeViaApi();

        $seatRes = $this->postJson('/api/seats', [
            'showtime_id' => $showtimeId,
            'seat_code' => 'A1',
            'seat_type' => 'NORMAL',
            'price' => 75000,
        ]);
        $seatId = $seatRes->json('data.seat_id');

        Sanctum::actingAs($this->customer);
        $bookRes = $this->postJson('/api/tickets/book', [
            'showtime_id' => $showtimeId,
            'seat_id' => $seatId,
            'payment_method' => 'CASH',
        ]);
        $ticketId = $bookRes->json('data.ticket_id');

        Sanctum::actingAs($this->admin);
        $paymentId = \App\Models\Payment::where('ticket_id', $ticketId)->first()->payment_id;

        $response = $this->getJson("/api/payments/{$paymentId}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.method', 'CASH');
    }

    // ======================== AUTHORIZATION ========================

    public function test_customer_cannot_access_admin_routes(): void
    {
        Sanctum::actingAs($this->customer);

        $this->postJson('/api/cinemas', ['name' => 'Test', 'location' => 'HN'])
            ->assertStatus(403);

        $this->postJson('/api/categories', ['name' => 'Test'])
            ->assertStatus(403);

        $this->getJson('/api/tickets')
            ->assertStatus(403);
    }

    public function test_unauthenticated_cannot_book(): void
    {
        $response = $this->postJson('/api/tickets/book', [
            'showtime_id' => 'fake-id',
            'seat_id' => 'fake-id',
            'payment_method' => 'CASH',
        ]);

        $response->assertStatus(401);
    }

    // ======================== VALIDATION ========================

    public function test_booking_validates_required_fields(): void
    {
        Sanctum::actingAs($this->customer);

        $response = $this->postJson('/api/tickets/book', []);

        $response->assertStatus(422)
            ->assertJsonStructure(['success', 'message', 'errors']);
    }

    public function test_combo_out_of_stock_prevents_booking(): void
    {
        Sanctum::actingAs($this->admin);
        $showtimeId = $this->createShowtimeViaApi();

        $seatRes = $this->postJson('/api/seats', [
            'showtime_id' => $showtimeId,
            'seat_code' => 'A1',
            'seat_type' => 'NORMAL',
            'price' => 75000,
        ]);
        $seatId = $seatRes->json('data.seat_id');

        $comboRes = $this->postJson('/api/combos', [
            'name' => 'Combo Empty',
            'price' => 50000,
            'stock' => 1,
        ]);
        $comboId = $comboRes->json('data.combo_id');

        Sanctum::actingAs($this->customer);
        $response = $this->postJson('/api/tickets/book', [
            'showtime_id' => $showtimeId,
            'seat_id' => $seatId,
            'payment_method' => 'CASH',
            'combos' => [
                ['combo_id' => $comboId, 'qty' => 5],
            ],
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('success', false);
    }

    // ======================== HELPERS ========================

    /**
     * Tạo showtime qua API và trả về showtime_id.
     * Phải gọi khi đã actingAs admin.
     */
    private function createShowtimeViaApi(): string
    {
        $cinemaRes = $this->postJson('/api/cinemas', [
            'name' => 'Cinema ' . uniqid(),
            'location' => 'TP.HCM',
        ]);
        $cinemaId = $cinemaRes->json('data.cinema_id');

        $catRes = $this->postJson('/api/categories', ['name' => 'Cat ' . uniqid()]);
        $categoryId = $catRes->json('data.id');

        $movieRes = $this->postJson('/api/movies', [
            'category_ids' => [$categoryId],
            'title' => 'Movie ' . uniqid(),
            'name' => 'Movie ' . uniqid(),
            'thumb_url' => 'https://example.com/thumb.jpg',
            'trailer_url' => 'https://example.com/trailer.mp4',
            'duration' => 120,
            'language' => 'Tiếng Việt',
            'age' => 13,
            'release_date' => '2026-03-15',
            'status' => 'IN_ACTIVE',
        ]);
        $movieId = $movieRes->json('data.movie_id');

        $showtimeRes = $this->postJson('/api/showtimes', [
            'cinema_id' => $cinemaId,
            'movie_id' => $movieId,
            'starts_at' => '2026-03-20 18:00:00',
            'ends_at' => '2026-03-20 20:00:00',
            'screen_type' => '2D',
        ]);

        return $showtimeRes->json('data.showtime_id');
    }
}
