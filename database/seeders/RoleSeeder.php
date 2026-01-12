<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate(
            ['name' => 'admin'],
            ['description' => 'Administrator sẽ có toàn quyền quản lý hệ thống']
        );

        Role::firstOrCreate(
            ['name' => 'staff'],
            ['description' => 'Staff Member sẽ là nhân viên hỗ trợ quản lý một số chức năng trong hệ thống nhưng không có toàn quyền như Administrator']
        );

        Role::firstOrCreate(
            ['name' => 'customer'],
            ['description' => 'Customer sẽ là người dùng cuối sử dụng dịch vụ và bị giới hạn quyền quản lý hệ thống']
        );
    }
}
