<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileUpdateNullableTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_profile_and_clear_nullable_fields(): void
    {
        $user = User::where('email', 'admin@ticketbooking.com')->firstOrFail();

        Sanctum::actingAs($user);

        $updateValues = [
            'phone' => '0909999999',
            'dob' => '1995-05-20',
            'address' => 'Ho Chi Minh',
            'avatar_url' => 'https://example.com/avatar-test.jpg',
        ];

        $this->putJson('/api/users/profile', $updateValues)
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('users', [
            'user_id' => $user->user_id,
            'phone' => '0909999999',
            'address' => 'Ho Chi Minh',
            'avatar_url' => 'https://example.com/avatar-test.jpg',
        ]);

        $this->putJson('/api/users/profile', [
            'phone' => null,
            'dob' => null,
            'address' => null,
            'avatar_url' => null,
        ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('users', [
            'user_id' => $user->user_id,
            'phone' => null,
            'dob' => null,
            'address' => null,
            'avatar_url' => null,
        ]);
    }

    public function test_user_can_update_non_nullable_profile_fields_normally(): void
    {
        $user = User::where('email', 'admin@ticketbooking.com')->firstOrFail();

        Sanctum::actingAs($user);

        $this->putJson('/api/users/profile', [
            'full_name' => 'Admin Updated Name',
            'user_name' => 'admin_updated',
        ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('users', [
            'user_id' => $user->user_id,
            'full_name' => 'Admin Updated Name',
            'user_name' => 'admin_updated',
        ]);
    }
}
