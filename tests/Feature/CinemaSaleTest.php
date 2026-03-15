<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CinemaSaleTest extends TestCase
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
            'user_name' => 'admin_sale',
            'full_name' => 'Admin Sale',
            'email' => 'admin_sale@test.com',
            'password' => Hash::make('Password@123'),
            'status' => 'IN_ACTIVE',
        ]);

        $this->customer = User::create([
            'role_id' => $customerRole->role_id,
            'user_name' => 'customer_sale',
            'full_name' => 'Customer Sale',
            'email' => 'customer_sale@test.com',
            'password' => Hash::make('Password@123'),
            'status' => 'IN_ACTIVE',
        ]);
    }

    public function test_admin_can_create_cinema_sale(): void
    {
        Sanctum::actingAs($this->admin);

        $cinemaRes = $this->postJson('/api/cinemas', [
            'name' => 'CGV Sale Test',
            'location' => 'TP.HCM',
        ]);
        $cinemaId = $cinemaRes->json('data.cinema_id');

        $response = $this->postJson('/api/cinema-sales', [
            'cinema_id' => $cinemaId,
            'sale_date' => '2026-03-15',
            'gross_amount' => 50000000,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.cinema_id', $cinemaId);
    }

    public function test_admin_can_list_cinema_sales(): void
    {
        Sanctum::actingAs($this->admin);

        $cinemaRes = $this->postJson('/api/cinemas', [
            'name' => 'CGV List Sale',
            'location' => 'HN',
        ]);
        $cinemaId = $cinemaRes->json('data.cinema_id');

        $this->postJson('/api/cinema-sales', [
            'cinema_id' => $cinemaId,
            'sale_date' => '2026-03-15',
            'gross_amount' => 30000000,
        ]);

        $this->postJson('/api/cinema-sales', [
            'cinema_id' => $cinemaId,
            'sale_date' => '2026-03-16',
            'gross_amount' => 45000000,
        ]);

        $response = $this->getJson('/api/cinema-sales');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertCount(2, $response->json('data.items'));
    }

    public function test_admin_can_get_cinema_sale_detail(): void
    {
        Sanctum::actingAs($this->admin);

        $cinemaRes = $this->postJson('/api/cinemas', [
            'name' => 'CGV Detail Sale',
            'location' => 'ĐN',
        ]);
        $cinemaId = $cinemaRes->json('data.cinema_id');

        $createRes = $this->postJson('/api/cinema-sales', [
            'cinema_id' => $cinemaId,
            'sale_date' => '2026-03-15',
            'gross_amount' => 60000000,
        ]);
        $saleId = $createRes->json('data.cinema_sale_id');

        $response = $this->getJson("/api/cinema-sales/{$saleId}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.cinema_sale_id', $saleId);
    }

    public function test_admin_can_update_cinema_sale(): void
    {
        Sanctum::actingAs($this->admin);

        $cinemaRes = $this->postJson('/api/cinemas', [
            'name' => 'CGV Update Sale',
            'location' => 'HP',
        ]);
        $cinemaId = $cinemaRes->json('data.cinema_id');

        $createRes = $this->postJson('/api/cinema-sales', [
            'cinema_id' => $cinemaId,
            'sale_date' => '2026-03-15',
            'gross_amount' => 30000000,
        ]);
        $saleId = $createRes->json('data.cinema_sale_id');

        $response = $this->putJson("/api/cinema-sales/{$saleId}", [
            'gross_amount' => 55000000,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_admin_can_delete_cinema_sale(): void
    {
        Sanctum::actingAs($this->admin);

        $cinemaRes = $this->postJson('/api/cinemas', [
            'name' => 'CGV Delete Sale',
            'location' => 'BĐ',
        ]);
        $cinemaId = $cinemaRes->json('data.cinema_id');

        $createRes = $this->postJson('/api/cinema-sales', [
            'cinema_id' => $cinemaId,
            'sale_date' => '2026-03-15',
        ]);
        $saleId = $createRes->json('data.cinema_sale_id');

        $response = $this->deleteJson("/api/cinema-sales/{$saleId}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('cinemas_sales', ['cinema_sale_id' => $saleId]);
    }

    public function test_customer_cannot_access_cinema_sales(): void
    {
        Sanctum::actingAs($this->customer);

        $this->getJson('/api/cinema-sales')->assertStatus(403);
        $this->postJson('/api/cinema-sales', [])->assertStatus(403);
    }
}
