<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Cinema;
use App\Models\CinemaSale;
use App\Models\Combo;
use App\Models\Employee;
use App\Models\EmployeeRole;
use App\Models\EmployeeSalary;
use App\Models\Movie;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Seat;
use App\Models\Showtime;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // ====== ROLES (already seeded in migration, just fetch) ======
        $roles = Role::pluck('role_id', 'name');

        // ====== USERS (5 managers, 10 employees, 20 customers) ======
        $password = Hash::make('Password@123');

        $managers = [];
        for ($i = 1; $i <= 5; $i++) {
            $managers[] = User::create([
                'role_id' => $roles['manager'],
                'user_name' => "manager_{$i}",
                'full_name' => "Quản lý {$i}",
                'email' => "manager{$i}@ticketbooking.com",
                'password' => $password,
                'phone' => "091000000{$i}",
                'address' => 'TP.HCM',
                'status' => 'IN_ACTIVE',
            ]);
        }

        $employeeUsers = [];
        for ($i = 1; $i <= 10; $i++) {
            $employeeUsers[] = User::create([
                'role_id' => $roles['employee'],
                'user_name' => "employee_{$i}",
                'full_name' => "Nhân viên {$i}",
                'email' => "employee{$i}@ticketbooking.com",
                'password' => $password,
                'phone' => '09200000'.str_pad($i, 2, '0', STR_PAD_LEFT),
                'address' => 'Hà Nội',
                'status' => 'IN_ACTIVE',
            ]);
        }

        $customers = [];
        for ($i = 1; $i <= 20; $i++) {
            $customers[] = User::create([
                'role_id' => $roles['customer'],
                'user_name' => "customer_{$i}",
                'full_name' => "Khách hàng {$i}",
                'email' => "customer{$i}@ticketbooking.com",
                'password' => $password,
                'phone' => '09300000'.str_pad($i, 2, '0', STR_PAD_LEFT),
                'address' => ['TP.HCM', 'Hà Nội', 'Đà Nẵng', 'Cần Thơ'][$i % 4],
                'status' => $i <= 18 ? 'IN_ACTIVE' : 'UN_ACTIVE',
            ]);
        }

        // ====== CINEMAS (5 cinemas, each managed by a manager) ======
        $cinemaData = [
            ['name' => 'CGV Landmark 81', 'location' => 'Vinhomes Central Park, Quận Bình Thạnh, TP.HCM'],
            ['name' => 'CGV Aeon Mall Tân Phú', 'location' => '30 Bờ Bao Tân Thắng, Quận Tân Phú, TP.HCM'],
            ['name' => 'Lotte Cinema Gò Vấp', 'location' => '242 Nguyễn Văn Lượng, Quận Gò Vấp, TP.HCM'],
            ['name' => 'Galaxy Nguyễn Du', 'location' => '116 Nguyễn Du, Quận 1, TP.HCM'],
            ['name' => 'BHD Star Vincom Đà Nẵng', 'location' => '910A Ngô Quyền, Quận Sơn Trà, Đà Nẵng'],
        ];

        $cinemas = [];
        $codeCounter = 1;
        foreach ($cinemaData as $idx => $data) {
            $cinemas[] = Cinema::create([
                'code' => sprintf('CIN-%06d', $codeCounter++),
                'name' => $data['name'],
                'location' => $data['location'],
                'active' => 'IN_ACTIVE',
                'manager_id' => $managers[$idx]->user_id,
            ]);
        }

        // ====== CATEGORIES (7 genres) ======
        $categoryNames = ['Hành động', 'Kinh dị', 'Hài hước', 'Tâm lý', 'Phiêu lưu', 'Khoa học viễn tưởng', 'Hoạt hình'];
        $categories = [];
        foreach ($categoryNames as $name) {
            $categories[] = Category::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => "Thể loại phim {$name}",
            ]);
        }

        // ====== COMBOS (5) ======
        $comboData = [
            ['name' => 'Combo Bắp Nước Lớn', 'price' => 89000, 'stock' => 200],
            ['name' => 'Combo Couple', 'price' => 139000, 'stock' => 100],
            ['name' => 'Nước ngọt lớn', 'price' => 35000, 'stock' => 500],
            ['name' => 'Bắp rang bơ', 'price' => 45000, 'stock' => 300],
            ['name' => 'Combo Family', 'price' => 199000, 'stock' => 50],
        ];

        $combos = [];
        foreach ($comboData as $data) {
            $combos[] = Combo::create($data);
        }

        // ====== MOVIES (8 movies) ======
        $movieData = [
            ['title' => 'Avengers: Secret Wars', 'name' => 'Avengers Secret Wars', 'duration' => 180, 'language' => 'Tiếng Anh', 'age' => 13, 'rating' => 8.5, 'release_date' => '2026-03-01', 'status' => 'IN_ACTIVE', 'cats' => [0, 4]],
            ['title' => 'Conjuring 4', 'name' => 'Conjuring 4', 'duration' => 115, 'language' => 'Tiếng Anh', 'age' => 18, 'rating' => 7.8, 'release_date' => '2026-03-10', 'status' => 'IN_ACTIVE', 'cats' => [1, 3]],
            ['title' => 'Deadpool vs Wolverine 2', 'name' => 'Deadpool Wolverine 2', 'duration' => 128, 'language' => 'Tiếng Anh', 'age' => 16, 'rating' => 9.0, 'release_date' => '2026-03-15', 'status' => 'IN_ACTIVE', 'cats' => [0, 2]],
            ['title' => 'Lật Mặt 8', 'name' => 'Lat Mat 8', 'duration' => 130, 'language' => 'Tiếng Việt', 'age' => 13, 'rating' => 7.5, 'release_date' => '2026-02-20', 'status' => 'IN_ACTIVE', 'cats' => [0, 3]],
            ['title' => 'Inside Out 3', 'name' => 'Inside Out 3', 'duration' => 100, 'language' => 'Tiếng Anh', 'age' => 0, 'rating' => 8.8, 'release_date' => '2026-04-01', 'status' => 'IS_PENDING', 'cats' => [6, 2]],
            ['title' => 'Dune: Part Three', 'name' => 'Dune Part Three', 'duration' => 165, 'language' => 'Tiếng Anh', 'age' => 13, 'rating' => 8.2, 'release_date' => '2026-03-20', 'status' => 'IN_ACTIVE', 'cats' => [5, 4]],
            ['title' => 'Tết Ở Làng Địa Ngục 2', 'name' => 'Tet O Lang Dia Nguc 2', 'duration' => 110, 'language' => 'Tiếng Việt', 'age' => 18, 'rating' => 6.9, 'release_date' => '2026-01-25', 'status' => 'IN_ACTIVE', 'cats' => [1]],
            ['title' => 'Spider-Man: Beyond', 'name' => 'Spider Man Beyond', 'duration' => 140, 'language' => 'Tiếng Anh', 'age' => 13, 'rating' => 8.7, 'release_date' => '2026-05-01', 'status' => 'IS_PENDING', 'cats' => [0, 5]],
        ];

        $movies = [];
        $movieCodeCounter = 1;
        foreach ($movieData as $data) {
            $catIds = $data['cats'];
            unset($data['cats']);

            $movie = Movie::create(array_merge($data, [
                'code' => sprintf('MOV-%06d', $movieCodeCounter++),
                'slug' => Str::slug($data['name']),
                'description' => "Phim {$data['title']} - phim hay nhất năm 2026",
                'thumb_url' => 'https://example.com/thumb/'.Str::slug($data['name']).'.jpg',
                'trailer_url' => 'https://example.com/trailer/'.Str::slug($data['name']).'.mp4',
                'gender_id' => $categories[$catIds[0]]->id,
            ]));

            $movie->categories()->attach(array_map(fn ($i) => $categories[$i]->id, $catIds));
            $movies[] = $movie;
        }

        // ====== EMPLOYEE ROLES ======
        $staffRole = EmployeeRole::create(['name' => 'STAFF', 'description' => 'Nhân viên chính thức']);
        $probRole = EmployeeRole::create(['name' => 'PROBATION', 'description' => 'Nhân viên thử việc']);

        // ====== EMPLOYEES (10 employees, 2 per cinema) ======
        $employees = [];
        $empCodeCounter = 1;
        foreach ($employeeUsers as $idx => $user) {
            $cinemaIdx = intdiv($idx, 2); // 0,1->cinema0, 2,3->cinema1, etc.
            $employees[] = Employee::create([
                'employee_role_id' => $idx % 3 === 0 ? $probRole->employee_role_id : $staffRole->employee_role_id,
                'user_id' => $user->user_id,
                'cinema_id' => $cinemas[$cinemaIdx]->cinema_id,
                'name' => $user->full_name,
                'code' => sprintf('EMP-%06d', $empCodeCounter++),
                'hire_date' => now()->subMonths(rand(1, 24))->format('Y-m-d'),
                'status' => 'IN_ACTIVE',
            ]);
        }

        // ====== EMPLOYEE SALARIES ======
        foreach ($employees as $employee) {
            $netSalary = rand(8, 20) * 1000000;
            $bonus = rand(0, 5) * 500000;
            EmployeeSalary::create([
                'employee_id' => $employee->employee_id,
                'bank_number' => (string) rand(1000000000, 9999999999),
                'bank_name' => ['Vietcombank', 'BIDV', 'Techcombank', 'VPBank', 'ACB'][rand(0, 4)],
                'net_salary' => $netSalary,
                'bonus' => $bonus,
                'total_earn' => $netSalary + $bonus,
                'payment_status' => ['IS_PENDING', 'IN_ACTIVE', 'IN_ACTIVE'][rand(0, 2)],
            ]);
        }

        // ====== SHOWTIMES (3 movies × 5 cinemas × 2 slots = 30 showtimes) ======
        $activeMovies = array_slice($movies, 0, 6); // first 6 are IN_ACTIVE
        $showtimes = [];
        foreach ($cinemas as $cinema) {
            foreach (array_slice($activeMovies, 0, 3) as $movieIdx => $movie) {
                foreach ([['14:00', '16:00'], ['19:00', '21:30']] as $slotIdx => $slot) {
                    $day = now()->addDays(rand(1, 14));
                    $showtimes[] = Showtime::create([
                        'cinema_id' => $cinema->cinema_id,
                        'movie_id' => $movie->movie_id,
                        'starts_at' => $day->format('Y-m-d')." {$slot[0]}:00",
                        'ends_at' => $day->format('Y-m-d')." {$slot[1]}:00",
                        'screen_type' => $slotIdx === 0 ? '2D' : '3D',
                    ]);
                }
            }
        }

        // ====== SEATS (8 seats per showtime = 240 seats) ======
        $seatTypes = ['NORMAL', 'NORMAL', 'NORMAL', 'NORMAL', 'VIP', 'VIP', 'COUPLE', 'COUPLE'];
        $seatPrices = [75000, 75000, 75000, 75000, 120000, 120000, 200000, 200000];
        $seats = [];

        foreach ($showtimes as $showtime) {
            $row = 'A';
            foreach ($seatTypes as $sIdx => $type) {
                $seats[] = Seat::create([
                    'showtime_id' => $showtime->showtime_id,
                    'seat_code' => $row.($sIdx + 1),
                    'seat_type' => $type,
                    'price' => $seatPrices[$sIdx],
                    'active' => 'IN_ACTIVE',
                ]);
            }
        }

        // ====== TICKETS + PAYMENTS (40 bookings across different customers/showtimes) ======
        $ticketCodeCounter = 1;
        for ($t = 0; $t < 40; $t++) {
            $customer = $customers[$t % count($customers)];
            $seat = $seats[$t]; // each seat is unique

            // Mark seat as SOLD
            $seat->update(['active' => 'SOLD']);

            $showtime = Showtime::find($seat->showtime_id);

            $ticket = Ticket::create([
                'showtime_id' => $seat->showtime_id,
                'seat_id' => $seat->seat_id,
                'user_id' => $customer->user_id,
                'movie_id' => $showtime->movie_id,
                'code' => sprintf('TIK-%06d', $ticketCodeCounter++),
                'price' => $seat->price,
                'status' => $t < 30 ? 'IN_ACTIVE' : ($t < 36 ? 'IS_PENDING' : 'UN_ACTIVE'),
            ]);

            Payment::create([
                'ticket_id' => $ticket->ticket_id,
                'method' => ['TRANSFER', 'CARD', 'CASH'][$t % 3],
                'status' => $ticket->status,
            ]);

            // Attach a combo to some tickets
            if ($t % 3 === 0 && isset($combos[$t % count($combos)])) {
                $combo = $combos[$t % count($combos)];
                $qty = rand(1, 3);
                $ticket->combos()->attach($combo->combo_id, ['qty' => $qty]);
                if ($combo->stock !== null) {
                    $combo->decrement('stock', $qty);
                }
                $ticket->update(['price' => $ticket->price + ($combo->price * $qty)]);
            }
        }

        // ====== CINEMA SALES (daily sales for each cinema) ======
        foreach ($cinemas as $cinema) {
            for ($d = 1; $d <= 7; $d++) {
                CinemaSale::create([
                    'cinema_id' => $cinema->cinema_id,
                    'sale_date' => now()->subDays($d)->format('Y-m-d'),
                    'gross_amount' => rand(20, 100) * 1000000,
                ]);
            }
        }

        $this->command->info('Test data seeded successfully!');
        $this->command->info('  - 5 managers, 10 employee users, 20 customers');
        $this->command->info('  - 5 cinemas (each with manager)');
        $this->command->info('  - 7 categories, 8 movies, 5 combos');
        $this->command->info('  - 10 employees (2 per cinema), 10 salaries');
        $this->command->info('  - 30 showtimes, 240 seats, 40 tickets, 40 payments');
        $this->command->info('  - 35 cinema sale records');
    }
}
