<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call(RoleSeeder::class);

        $adminRoleId = Role::query()->where('name', 'admin')->value('id');
        User::factory()->create([
            'username' => 'ticketbookingadmin2002',
            'name' => 'Admin Ticket Booking',
            'profile_picture' => 'https://testingbot.com/free-online-tools/random-avatar/45',
            'email' => 'ticketbooking22@gmail.com',
            'phone_number' => '0334920373',
            'address' => 'Hanoi, Vietnam',
            'date_of_birth' => '2002-08-27',
            'role_id' => $adminRoleId,
            'password' => Hash::make('Admin@2002')
        ]);
    }
}
