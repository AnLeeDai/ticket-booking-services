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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('Password@123');
        $roles = Role::pluck('role_id', 'name');

        // =====================================================================
        // CATEGORIES (12 thể loại phim)
        // =====================================================================
        $categoryList = [
            ['name' => 'Hành động', 'desc' => 'Phim có các pha hành động, đánh nhau, rượt đuổi gay cấn'],
            ['name' => 'Kinh dị', 'desc' => 'Phim mang yếu tố rùng rợn, ma quái, giật gân'],
            ['name' => 'Hài hước', 'desc' => 'Phim gây cười, giải trí nhẹ nhàng'],
            ['name' => 'Tâm lý', 'desc' => 'Phim khai thác chiều sâu tâm lý nhân vật'],
            ['name' => 'Phiêu lưu', 'desc' => 'Phim hành trình khám phá, mạo hiểm'],
            ['name' => 'Khoa học viễn tưởng', 'desc' => 'Phim về công nghệ tương lai, vũ trụ'],
            ['name' => 'Hoạt hình', 'desc' => 'Phim hoạt hình dành cho mọi lứa tuổi'],
            ['name' => 'Tình cảm', 'desc' => 'Phim lãng mạn, tình yêu đôi lứa'],
            ['name' => 'Gia đình', 'desc' => 'Phim dành cho cả gia đình cùng xem'],
            ['name' => 'Chiến tranh', 'desc' => 'Phim về đề tài chiến tranh, lịch sử'],
            ['name' => 'Tài liệu', 'desc' => 'Phim tài liệu, phóng sự'],
            ['name' => 'Âm nhạc', 'desc' => 'Phim ca nhạc, nhạc kịch'],
        ];
        $categories = [];
        foreach ($categoryList as $c) {
            $categories[] = Category::create([
                'name' => $c['name'],
                'slug' => Str::slug($c['name']),
                'description' => $c['desc'],
            ]);
        }

        // =====================================================================
        // COMBOS (8 combo đồ ăn/uống)
        // =====================================================================
        $comboList = [
            ['name' => 'Combo Solo', 'price' => 69000, 'stock' => 500],
            ['name' => 'Combo Bắp Nước Lớn', 'price' => 89000, 'stock' => 400],
            ['name' => 'Combo Couple Sweet', 'price' => 139000, 'stock' => 250],
            ['name' => 'Combo Family 4 người', 'price' => 199000, 'stock' => 150],
            ['name' => 'Nước ngọt size L', 'price' => 35000, 'stock' => 1000],
            ['name' => 'Bắp rang bơ size L', 'price' => 45000, 'stock' => 800],
            ['name' => 'Snack khoai tây', 'price' => 29000, 'stock' => 600],
            ['name' => 'Combo Premium VIP', 'price' => 259000, 'stock' => 80],
        ];
        $combos = [];
        foreach ($comboList as $data) {
            $combos[] = Combo::create($data);
        }

        // =====================================================================
        // USERS — 8 managers, 20 employees, 50 customers
        // =====================================================================
        $vnFirstNames = ['Minh', 'Hải', 'Hương', 'Lan', 'Tuấn', 'Thảo', 'Phong', 'Mai', 'Đức', 'Ngọc',
            'Anh', 'Bình', 'Châu', 'Duy', 'Hà', 'Khoa', 'Linh', 'Nam', 'Quang', 'Trang',
            'Việt', 'Yến', 'Hùng', 'Thu', 'Long', 'Uyên', 'Kiên', 'Nhung', 'Tùng', 'Vân'];
        $vnLastNames = ['Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng', 'Huỳnh', 'Phan', 'Vũ', 'Võ', 'Đặng',
            'Bùi', 'Đỗ', 'Hồ', 'Ngô', 'Dương'];
        $vnMiddleNames = ['Văn', 'Thị', 'Hoàng', 'Minh', 'Thanh', 'Đình', 'Quốc', 'Ngọc', 'Xuân', 'Kim'];
        $cities = ['TP. Hồ Chí Minh', 'Hà Nội', 'Đà Nẵng', 'Hải Phòng', 'Cần Thơ', 'Biên Hoà', 'Nha Trang', 'Huế', 'Vũng Tàu', 'Buôn Ma Thuột'];
        $districts = [
            'TP. Hồ Chí Minh' => ['Quận 1', 'Quận 3', 'Quận 5', 'Quận 7', 'Quận 10', 'Bình Thạnh', 'Tân Bình', 'Phú Nhuận', 'Gò Vấp', 'Thủ Đức'],
            'Hà Nội' => ['Ba Đình', 'Hoàn Kiếm', 'Hai Bà Trưng', 'Đống Đa', 'Cầu Giấy', 'Thanh Xuân', 'Long Biên', 'Nam Từ Liêm'],
            'Đà Nẵng' => ['Hải Châu', 'Thanh Khê', 'Sơn Trà', 'Ngũ Hành Sơn', 'Liên Chiểu'],
        ];

        $makeFullName = function () use ($vnFirstNames, $vnLastNames, $vnMiddleNames) {
            return $vnLastNames[array_rand($vnLastNames)] . ' ' . $vnMiddleNames[array_rand($vnMiddleNames)] . ' ' . $vnFirstNames[array_rand($vnFirstNames)];
        };
        $makeAddress = function () use ($cities, $districts) {
            $city = $cities[array_rand($cities)];
            $districtList = $districts[$city] ?? ['Trung tâm'];
            return $districtList[array_rand($districtList)] . ', ' . $city;
        };

        // --- Managers (8) ---
        $managers = [];
        for ($i = 1; $i <= 8; $i++) {
            $managers[] = User::create([
                'role_id' => $roles['manager'],
                'user_name' => "manager_{$i}",
                'full_name' => $makeFullName(),
                'email' => "manager{$i}@ticketbooking.com",
                'password' => $password,
                'phone' => '091' . str_pad($i, 7, '0', STR_PAD_LEFT),
                'dob' => Carbon::create(rand(1980, 1995), rand(1, 12), rand(1, 28)),
                'address' => $makeAddress(),
                'status' => 'IN_ACTIVE',
            ]);
        }

        // --- Employee users (20) ---
        $employeeUsers = [];
        for ($i = 1; $i <= 20; $i++) {
            $employeeUsers[] = User::create([
                'role_id' => $roles['employee'],
                'user_name' => "employee_{$i}",
                'full_name' => $makeFullName(),
                'email' => "employee{$i}@ticketbooking.com",
                'password' => $password,
                'phone' => '092' . str_pad($i, 7, '0', STR_PAD_LEFT),
                'dob' => Carbon::create(rand(1990, 2004), rand(1, 12), rand(1, 28)),
                'address' => $makeAddress(),
                'status' => 'IN_ACTIVE',
            ]);
        }

        // --- Customers (50) ---
        $customers = [];
        for ($i = 1; $i <= 50; $i++) {
            $customers[] = User::create([
                'role_id' => $roles['customer'],
                'user_name' => "customer_{$i}",
                'full_name' => $makeFullName(),
                'email' => "customer{$i}@gmail.com",
                'password' => $password,
                'phone' => '093' . str_pad($i, 7, '0', STR_PAD_LEFT),
                'dob' => Carbon::create(rand(1985, 2006), rand(1, 12), rand(1, 28)),
                'address' => $makeAddress(),
                'status' => $i <= 47 ? 'IN_ACTIVE' : 'UN_ACTIVE',
            ]);
        }

        // =====================================================================
        // CINEMAS (8 rạp chiếu)
        // =====================================================================
        $cinemaList = [
            ['name' => 'CGV Landmark 81', 'location' => 'Tầng B1, Landmark 81, 720A Điện Biên Phủ, Bình Thạnh, TP.HCM'],
            ['name' => 'CGV Aeon Mall Tân Phú', 'location' => 'Tầng 3, Aeon Mall, 30 Bờ Bao Tân Thắng, Tân Phú, TP.HCM'],
            ['name' => 'CGV Vincom Centre', 'location' => 'Tầng 5, Vincom Centre, 72 Lê Thánh Tôn, Quận 1, TP.HCM'],
            ['name' => 'Lotte Cinema Gò Vấp', 'location' => 'Tầng 4, Lotte Mart, 242 Nguyễn Văn Lượng, Gò Vấp, TP.HCM'],
            ['name' => 'Galaxy Nguyễn Du', 'location' => '116 Nguyễn Du, Quận 1, TP.HCM'],
            ['name' => 'BHD Star Vincom Mega Mall', 'location' => 'Tầng 4, Vincom Mega Mall, Vinhomes Grand Park, TP. Thủ Đức, TP.HCM'],
            ['name' => 'CGV Aeon Mall Long Biên', 'location' => 'Tầng 4, Aeon Mall Long Biên, 27 Cổ Linh, Long Biên, Hà Nội'],
            ['name' => 'Lotte Cinema Đà Nẵng', 'location' => 'Tầng 5, Lotte Mart, 6 Nại Nam, Hải Châu, Đà Nẵng'],
        ];
        $cinemas = [];
        foreach ($cinemaList as $idx => $data) {
            $cinemas[] = Cinema::create([
                'code' => sprintf('CN-%06d', $idx + 1),
                'name' => $data['name'],
                'location' => $data['location'],
                'active' => $idx < 7 ? 'IN_ACTIVE' : 'UN_ACTIVE',
                'manager_id' => $managers[$idx]->user_id,
            ]);
        }

        // =====================================================================
        // MOVIES (15 phim — đa dạng trạng thái + ngôn ngữ)
        // =====================================================================
        $movieList = [
            // Phim đang chiếu (IN_ACTIVE)
            ['title' => 'Avengers: Secret Wars', 'name' => 'Avengers Secret Wars', 'duration' => 180, 'lang' => 'Tiếng Anh - Phụ đề Việt', 'age' => 13, 'rating' => 8.5, 'release' => '2026-02-14', 'end' => '2026-04-14', 'status' => 'IN_ACTIVE', 'cats' => [0, 4, 5]],
            ['title' => 'Conjuring 4: Last Rites', 'name' => 'Conjuring 4', 'duration' => 115, 'lang' => 'Tiếng Anh - Phụ đề Việt', 'age' => 18, 'rating' => 7.8, 'release' => '2026-02-28', 'end' => '2026-04-28', 'status' => 'IN_ACTIVE', 'cats' => [1, 3]],
            ['title' => 'Lật Mặt 8: Vòng Xoáy Tử Thần', 'name' => 'Lat Mat 8', 'duration' => 132, 'lang' => 'Tiếng Việt', 'age' => 13, 'rating' => 7.5, 'release' => '2026-02-10', 'end' => '2026-04-10', 'status' => 'IN_ACTIVE', 'cats' => [0, 3, 7]],
            ['title' => 'Dune: Messiah', 'name' => 'Dune Messiah', 'duration' => 168, 'lang' => 'Tiếng Anh - Phụ đề Việt', 'age' => 13, 'rating' => 8.2, 'release' => '2026-03-01', 'end' => '2026-05-01', 'status' => 'IN_ACTIVE', 'cats' => [5, 4, 9]],
            ['title' => 'Mai 2: Yêu Thương Trở Lại', 'name' => 'Mai 2', 'duration' => 120, 'lang' => 'Tiếng Việt', 'age' => 16, 'rating' => 7.9, 'release' => '2026-02-14', 'end' => '2026-04-14', 'status' => 'IN_ACTIVE', 'cats' => [7, 3, 8]],
            ['title' => 'Kung Fu Panda 5', 'name' => 'Kung Fu Panda 5', 'duration' => 95, 'lang' => 'Lồng tiếng Việt', 'age' => 0, 'rating' => 8.1, 'release' => '2026-03-01', 'end' => '2026-04-30', 'status' => 'IN_ACTIVE', 'cats' => [6, 2, 8]],
            ['title' => 'Deadpool vs Wolverine 2', 'name' => 'Deadpool Wolverine 2', 'duration' => 128, 'lang' => 'Tiếng Anh - Phụ đề Việt', 'age' => 16, 'rating' => 9.0, 'release' => '2026-03-07', 'end' => '2026-05-07', 'status' => 'IN_ACTIVE', 'cats' => [0, 2]],
            ['title' => 'Tết Ở Làng Địa Ngục 2', 'name' => 'Tet O Lang Dia Nguc 2', 'duration' => 110, 'lang' => 'Tiếng Việt', 'age' => 18, 'rating' => 6.9, 'release' => '2026-01-25', 'end' => '2026-03-25', 'status' => 'IN_ACTIVE', 'cats' => [1]],
            ['title' => 'Interstellar 2: Beyond Time', 'name' => 'Interstellar 2', 'duration' => 175, 'lang' => 'Tiếng Anh - Phụ đề Việt', 'age' => 13, 'rating' => 8.8, 'release' => '2026-03-10', 'end' => '2026-05-10', 'status' => 'IN_ACTIVE', 'cats' => [5, 3, 4]],
            ['title' => 'Nhà Bà Nữ 2', 'name' => 'Nha Ba Nu 2', 'duration' => 125, 'lang' => 'Tiếng Việt', 'age' => 13, 'rating' => 7.2, 'release' => '2026-03-08', 'end' => '2026-05-08', 'status' => 'IN_ACTIVE', 'cats' => [2, 8]],
            // Phim sắp chiếu (IS_PENDING)
            ['title' => 'Spider-Man: Beyond the Spider-Verse', 'name' => 'Spider Man Beyond', 'duration' => 140, 'lang' => 'Tiếng Anh - Phụ đề Việt', 'age' => 13, 'rating' => null, 'release' => '2026-04-18', 'end' => null, 'status' => 'IS_PENDING', 'cats' => [0, 6, 5]],
            ['title' => 'Inside Out 3', 'name' => 'Inside Out 3', 'duration' => 100, 'lang' => 'Lồng tiếng Việt', 'age' => 0, 'rating' => null, 'release' => '2026-05-01', 'end' => null, 'status' => 'IS_PENDING', 'cats' => [6, 2, 8]],
            ['title' => 'The Batman 2', 'name' => 'The Batman 2', 'duration' => 155, 'lang' => 'Tiếng Anh - Phụ đề Việt', 'age' => 13, 'rating' => null, 'release' => '2026-06-01', 'end' => null, 'status' => 'IS_PENDING', 'cats' => [0, 3]],
            // Phim ngừng chiếu (UN_ACTIVE)
            ['title' => 'Wonka', 'name' => 'Wonka', 'duration' => 116, 'lang' => 'Tiếng Anh - Phụ đề Việt', 'age' => 0, 'rating' => 7.4, 'release' => '2025-12-01', 'end' => '2026-02-01', 'status' => 'UN_ACTIVE', 'cats' => [8, 2, 11]],
            ['title' => 'Bố Già 4', 'name' => 'Bo Gia 4', 'duration' => 128, 'lang' => 'Tiếng Việt', 'age' => 13, 'rating' => 7.0, 'release' => '2026-01-01', 'end' => '2026-02-28', 'status' => 'UN_ACTIVE', 'cats' => [2, 3, 8]],
        ];

        $movies = [];
        $mvCode = 1;
        foreach ($movieList as $m) {
            $catIdxs = $m['cats'];
            $movie = Movie::create([
                'code' => sprintf('MV-202603-%04d', $mvCode++),
                'title' => $m['title'],
                'name' => $m['name'],
                'slug' => Str::slug($m['name']),
                'description' => "Bộ phim {$m['title']} mang đến trải nghiệm điện ảnh đầy ấn tượng cho khán giả năm 2026.",
                'thumb_url' => 'https://placehold.co/300x450?text=' . urlencode($m['name']),
                'trailer_url' => 'https://www.youtube.com/watch?v=example_' . Str::slug($m['name']),
                'gallery' => [
                    'https://placehold.co/800x400?text=' . urlencode($m['name'] . ' 1'),
                    'https://placehold.co/800x400?text=' . urlencode($m['name'] . ' 2'),
                ],
                'duration' => $m['duration'],
                'language' => $m['lang'],
                'age' => $m['age'],
                'rating' => $m['rating'],
                'release_date' => $m['release'],
                'end_date' => $m['end'],
                'status' => $m['status'],
                'genre_id' => $categories[$catIdxs[0]]->id,
            ]);
            $movie->categories()->attach(array_map(fn ($i) => $categories[$i]->id, $catIdxs));
            $movies[] = $movie;
        }

        $activeMovies = array_values(array_filter($movies, fn ($m) => $m->status === 'IN_ACTIVE'));

        // =====================================================================
        // EMPLOYEE ROLES
        // =====================================================================
        $staffRole = EmployeeRole::create(['name' => 'STAFF', 'description' => 'Nhân viên chính thức, đã qua thử việc']);
        $probRole = EmployeeRole::create(['name' => 'PROBATION', 'description' => 'Nhân viên thử việc, đang trong giai đoạn đánh giá']);

        // =====================================================================
        // EMPLOYEES (20 nhân viên — ~2-3 nhân viên/rạp active)
        // =====================================================================
        $employees = [];
        $empCode = 1;
        $activeCinemas = array_values(array_filter($cinemas, fn ($c) => $c->active === 'IN_ACTIVE'));

        foreach ($employeeUsers as $idx => $user) {
            $cinemaIdx = $idx % count($activeCinemas);
            $isStaff = $idx % 4 !== 0;
            $employees[] = Employee::create([
                'employee_role_id' => $isStaff ? $staffRole->employee_role_id : $probRole->employee_role_id,
                'user_id' => $user->user_id,
                'cinema_id' => $activeCinemas[$cinemaIdx]->cinema_id,
                'name' => $user->full_name,
                'code' => sprintf('EMP-%06d', $empCode++),
                'hire_date' => now()->subMonths(rand(1, 36))->format('Y-m-d'),
                'end_date' => $idx >= 18 ? now()->addMonths(rand(1, 6))->format('Y-m-d') : null,
                'status' => $idx < 18 ? 'IN_ACTIVE' : 'UN_ACTIVE',
            ]);
        }

        // =====================================================================
        // EMPLOYEE SALARIES
        // =====================================================================
        $bankNames = ['Vietcombank', 'BIDV', 'Techcombank', 'VPBank', 'ACB', 'MBBank', 'Sacombank', 'TPBank', 'VietinBank', 'HDBank'];
        foreach ($employees as $employee) {
            $netSalary = $employee->status === 'IN_ACTIVE'
                ? rand(8, 25) * 1000000
                : rand(5, 10) * 1000000;
            $bonus = rand(0, 8) * 500000;
            EmployeeSalary::create([
                'employee_id' => $employee->employee_id,
                'bank_number' => (string) rand(10000000000, 99999999999),
                'bank_name' => $bankNames[array_rand($bankNames)],
                'net_salary' => $netSalary,
                'bonus' => $bonus,
                'total_earn' => $netSalary + $bonus,
                'payment_status' => ['IS_PENDING', 'IN_ACTIVE', 'IN_ACTIVE', 'IN_ACTIVE'][rand(0, 3)],
            ]);
        }

        // =====================================================================
        // SHOWTIMES
        // =====================================================================
        $showtimes = [];
        $timeSlots = [
            ['09:00', 15], ['11:30', 15], ['14:00', 15],
            ['16:30', 15], ['19:00', 15], ['21:30', 15],
        ];

        // Suất chiếu TƯƠNG LAI (7 rạp active × 14 ngày × 4-5 suất/ngày)
        foreach ($activeCinemas as $cinema) {
            $cinemaMovies = $activeMovies;
            shuffle($cinemaMovies);
            $cinemaMovies = array_slice($cinemaMovies, 0, min(5, count($cinemaMovies)));

            for ($dayOffset = 0; $dayOffset <= 13; $dayOffset++) {
                $date = now()->addDays($dayOffset)->format('Y-m-d');
                $daySlots = $timeSlots;
                shuffle($daySlots);
                $daySlots = array_slice($daySlots, 0, rand(4, 5));
                usort($daySlots, fn ($a, $b) => strcmp($a[0], $b[0]));

                foreach ($daySlots as $slotIdx => $slot) {
                    $movie = $cinemaMovies[$slotIdx % count($cinemaMovies)];
                    $startsAt = Carbon::parse("{$date} {$slot[0]}:00");
                    $endsAt = (clone $startsAt)->addMinutes($movie->duration + $slot[1]);

                    $showtimes[] = Showtime::create([
                        'cinema_id' => $cinema->cinema_id,
                        'movie_id' => $movie->movie_id,
                        'starts_at' => $startsAt,
                        'ends_at' => $endsAt,
                        'screen_type' => $slotIdx % 3 === 0 ? '3D' : '2D',
                    ]);
                }
            }
        }

        // Suất chiếu QUÁ KHỨ (3 rạp × 2 phim × 7 ngày = 42 suất)
        $pastShowtimes = [];
        foreach (array_slice($activeCinemas, 0, 3) as $cinema) {
            foreach (array_slice($activeMovies, 0, 2) as $movie) {
                for ($d = 1; $d <= 7; $d++) {
                    $date = now()->subDays($d)->format('Y-m-d');
                    $startsAt = Carbon::parse("{$date} 19:00:00");
                    $endsAt = (clone $startsAt)->addMinutes($movie->duration + 15);
                    $st = Showtime::create([
                        'cinema_id' => $cinema->cinema_id,
                        'movie_id' => $movie->movie_id,
                        'starts_at' => $startsAt,
                        'ends_at' => $endsAt,
                        'screen_type' => '2D',
                    ]);
                    $pastShowtimes[] = $st;
                    $showtimes[] = $st;
                }
            }
        }

        // =====================================================================
        // SEATS (mỗi suất chiếu 65 ghế theo layout thực tế)
        // =====================================================================
        $seatLayout = [
            'A' => ['type' => 'NORMAL', 'count' => 10, 'price2D' => 75000, 'price3D' => 95000],
            'B' => ['type' => 'NORMAL', 'count' => 10, 'price2D' => 75000, 'price3D' => 95000],
            'C' => ['type' => 'NORMAL', 'count' => 10, 'price2D' => 85000, 'price3D' => 105000],
            'D' => ['type' => 'NORMAL', 'count' => 10, 'price2D' => 85000, 'price3D' => 105000],
            'E' => ['type' => 'VIP', 'count' => 10, 'price2D' => 120000, 'price3D' => 150000],
            'F' => ['type' => 'VIP', 'count' => 10, 'price2D' => 120000, 'price3D' => 150000],
            'G' => ['type' => 'COUPLE', 'count' => 5, 'price2D' => 200000, 'price3D' => 250000],
        ];

        $allSeats = [];
        foreach ($showtimes as $showtime) {
            $showtimeSeats = [];
            foreach ($seatLayout as $row => $config) {
                for ($num = 1; $num <= $config['count']; $num++) {
                    $price = $showtime->screen_type === '3D' ? $config['price3D'] : $config['price2D'];
                    $showtimeSeats[] = Seat::create([
                        'showtime_id' => $showtime->showtime_id,
                        'seat_code' => $row . $num,
                        'seat_type' => $config['type'],
                        'price' => $price,
                        'active' => 'IN_ACTIVE',
                    ]);
                }
            }
            $allSeats[$showtime->showtime_id] = $showtimeSeats;
        }

        // =====================================================================
        // TICKETS + PAYMENTS
        // =====================================================================
        $ticketCode = 1;
        $ticketsCreated = 0;
        $paymentsCreated = 0;
        $methods = ['TRANSFER', 'CARD', 'CASH'];

        // --- Vé QUÁ KHỨ: 60-80% ghế đã bán ---
        foreach ($pastShowtimes as $showtime) {
            $seats = $allSeats[$showtime->showtime_id] ?? [];
            $soldCount = (int) (count($seats) * (rand(60, 80) / 100));
            $soldSeats = array_slice($seats, 0, $soldCount);

            foreach ($soldSeats as $seat) {
                $customer = $customers[array_rand($customers)];
                $seat->update(['active' => 'SOLD']);

                $comboTotal = 0;
                $attachCombos = [];
                if (rand(1, 3) === 1) {
                    $combo = $combos[array_rand($combos)];
                    $qty = rand(1, 2);
                    $comboTotal = $combo->price * $qty;
                    $attachCombos[] = ['combo' => $combo, 'qty' => $qty];
                }

                $totalPrice = $seat->price + $comboTotal;
                $ticket = Ticket::create([
                    'showtime_id' => $showtime->showtime_id,
                    'seat_id' => $seat->seat_id,
                    'user_id' => $customer->user_id,
                    'movie_id' => $showtime->movie_id,
                    'code' => sprintf('TK-%s-%04d', now()->format('Ymd'), $ticketCode++),
                    'price' => $totalPrice,
                    'status' => 'IN_ACTIVE',
                ]);

                foreach ($attachCombos as $ac) {
                    $ticket->combos()->attach($ac['combo']->combo_id, ['qty' => $ac['qty']]);
                    $ac['combo']->refresh();
                    if ($ac['combo']->stock !== null && $ac['combo']->stock >= $ac['qty']) {
                        $ac['combo']->decrement('stock', $ac['qty']);
                    }
                }

                Payment::create([
                    'ticket_id' => $ticket->ticket_id,
                    'method' => $methods[array_rand($methods)],
                    'amount' => $totalPrice,
                    'status' => 'IN_ACTIVE',
                ]);

                $ticketsCreated++;
                $paymentsCreated++;
            }
        }

        // --- Vé TƯƠNG LAI: 10-30% ghế đặt (hỗn hợp trạng thái) ---
        $futureShowtimes = array_values(array_filter($showtimes, fn ($st) => $st->starts_at > now()));
        $bookedShowtimes = array_slice($futureShowtimes, 0, (int) (count($futureShowtimes) * 0.35));

        foreach ($bookedShowtimes as $showtime) {
            $seats = $allSeats[$showtime->showtime_id] ?? [];
            $bookCount = max(1, (int) (count($seats) * (rand(10, 25) / 100)));
            $offset = rand(0, max(0, count($seats) - $bookCount));
            $bookSeats = array_slice($seats, $offset, $bookCount);

            foreach ($bookSeats as $seat) {
                $customer = $customers[array_rand($customers)];

                $rand = rand(1, 10);
                if ($rand <= 7) {
                    $ticketStatus = 'IN_ACTIVE';
                    $seatStatus = 'SOLD';
                } elseif ($rand <= 9) {
                    $ticketStatus = 'IS_PENDING';
                    $seatStatus = 'HOLD';
                } else {
                    $ticketStatus = 'UN_ACTIVE';
                    $seatStatus = 'IN_ACTIVE';
                }

                $seat->update([
                    'active' => $seatStatus,
                    'hold_until' => $seatStatus === 'HOLD' ? now()->addMinutes(15) : null,
                ]);

                $comboTotal = 0;
                $attachCombos = [];
                if (rand(1, 4) === 1) {
                    $combo = $combos[array_rand($combos)];
                    $qty = rand(1, 3);
                    $comboTotal = $combo->price * $qty;
                    $attachCombos[] = ['combo' => $combo, 'qty' => $qty];
                }

                $totalPrice = $seat->price + $comboTotal;
                $ticket = Ticket::create([
                    'showtime_id' => $showtime->showtime_id,
                    'seat_id' => $seat->seat_id,
                    'user_id' => $customer->user_id,
                    'movie_id' => $showtime->movie_id,
                    'code' => sprintf('TK-%s-%04d', now()->format('Ymd'), $ticketCode++),
                    'price' => $totalPrice,
                    'status' => $ticketStatus,
                ]);

                foreach ($attachCombos as $ac) {
                    $ticket->combos()->attach($ac['combo']->combo_id, ['qty' => $ac['qty']]);
                    $ac['combo']->refresh();
                    if ($ticketStatus !== 'UN_ACTIVE' && $ac['combo']->stock !== null && $ac['combo']->stock >= $ac['qty']) {
                        $ac['combo']->decrement('stock', $ac['qty']);
                    }
                }

                Payment::create([
                    'ticket_id' => $ticket->ticket_id,
                    'method' => $methods[array_rand($methods)],
                    'amount' => $totalPrice,
                    'status' => $ticketStatus,
                ]);

                $ticketsCreated++;
                $paymentsCreated++;
            }
        }

        // =====================================================================
        // CINEMA SALES (30 ngày doanh thu — cuối tuần cao hơn)
        // =====================================================================
        $salesCreated = 0;
        foreach ($activeCinemas as $cinema) {
            for ($d = 1; $d <= 30; $d++) {
                $saleDate = now()->subDays($d)->format('Y-m-d');
                $dayOfWeek = Carbon::parse($saleDate)->dayOfWeek;
                $isWeekend = in_array($dayOfWeek, [0, 6]);
                $baseAmount = $isWeekend ? rand(80, 180) : rand(30, 90);

                CinemaSale::create([
                    'cinema_id' => $cinema->cinema_id,
                    'sale_date' => $saleDate,
                    'gross_amount' => $baseAmount * 1000000,
                ]);
                $salesCreated++;
            }
        }

        // =====================================================================
        // SUMMARY
        // =====================================================================
        $this->command->newLine();
        $this->command->info('=== Seed data thanh cong! ===');
        $this->command->table(
            ['Du lieu', 'So luong'],
            [
                ['Roles', Role::count()],
                ['Users (Admin + Manager + Employee + Customer)', User::count()],
                ['Cinemas', Cinema::count()],
                ['Categories', Category::count()],
                ['Movies', Movie::count()],
                ['Combos', Combo::count()],
                ['Employee Roles', EmployeeRole::count()],
                ['Employees', Employee::count()],
                ['Employee Salaries', EmployeeSalary::count()],
                ['Showtimes', Showtime::count()],
                ['Seats', Seat::count()],
                ['Tickets', $ticketsCreated],
                ['Payments', $paymentsCreated],
                ['Cinema Sales', $salesCreated],
            ]
        );
        $this->command->newLine();
        $this->command->info('Tai khoan test - mat khau: Password@123');
        $this->command->info('   Admin:    admin@ticketbooking.com');
        $this->command->info('   Manager:  manager1@ticketbooking.com -> manager8@ticketbooking.com');
        $this->command->info('   Employee: employee1@ticketbooking.com -> employee20@ticketbooking.com');
        $this->command->info('   Customer: customer1@gmail.com -> customer50@gmail.com');
    }
}
