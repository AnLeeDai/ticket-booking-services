# Frontend Validation Map

Project scope: Backend API only (Laravel). Mapping này tổng hợp rule từ FormRequest và các chỗ validate trực tiếp trong service để frontend dựng form đúng chuẩn.

Base API: /api
Auth: Bearer token (Sanctum) cho endpoint private

## Tổng hợp endpoint create/update đã rà soát

- POST /api/login -> AuthController@login -> LoginRequest
- POST /api/register -> AuthController@register -> RegisterRequest
- POST /api/forgot-password -> AuthController@forgotPassword -> ForgotPasswordRequest
- POST /api/reset-password -> AuthController@resetPassword -> ResetPasswordRequest
- POST /api/auth/change-password -> AuthController@changePassword -> ChangePasswordRequest
- POST /api/categories -> CategoryController@store -> CreateCategoryRequest
- PUT /api/categories/{id} -> CategoryController@update -> UpdateCategoryRequest
- POST /api/cinemas -> CinemaController@store -> CreateCinemaRequest
- PUT /api/cinemas/{id} -> CinemaController@update -> UpdateCinemaRequest
- POST /api/cinema-sales -> CinemaSaleController@store -> CreateCinemaSaleRequest
- PUT /api/cinema-sales/{id} -> CinemaSaleController@update -> UpdateCinemaSaleRequest
- POST /api/combos -> ComboController@store -> CreateComboRequest
- PUT /api/combos/{id} -> ComboController@update -> UpdateComboRequest
- POST /api/employee-roles -> EmployeeRoleController@store -> CreateEmployeeRoleRequest
- PUT /api/employee-roles/{id} -> EmployeeRoleController@update -> UpdateEmployeeRoleRequest
- POST /api/employee-salaries -> EmployeeSalaryController@store -> CreateEmployeeSalaryRequest
- PUT /api/employee-salaries/{id} -> EmployeeSalaryController@update -> UpdateEmployeeSalaryRequest
- POST /api/employees -> EmployeeController@store -> CreateEmployeeRequest
- PUT /api/employees/{id} -> EmployeeController@update -> UpdateEmployeeRequest
- POST /api/movies -> MovieController@store -> CreateMovieRequest
- PUT /api/movies/{id} -> MovieController@update -> UpdateMovieRequest
- POST /api/seats -> SeatController@store -> CreateSeatRequest
- POST /api/seats/bulk -> SeatController@storeBulk -> validate() trực tiếp trong SeatServices
- PUT /api/seats/{id} -> SeatController@update -> UpdateSeatRequest
- POST /api/showtimes -> ShowtimeController@store -> CreateShowtimeRequest
- PUT /api/showtimes/{id} -> ShowtimeController@update -> UpdateShowtimeRequest
- PUT /api/users/profile -> UserController@updateProfile -> UpdateProfileRequest
- PUT /api/users/{id} -> UserController@update -> UpdateUserRequest
- POST /api/tickets/book -> TicketController@book -> CreateTicketRequest
- POST /api/tickets/{id}/confirm-payment -> TicketController@confirmPayment -> không dùng FormRequest, không yêu cầu body
- POST /api/tickets/{id}/cancel -> TicketController@cancel -> không dùng FormRequest, không yêu cầu body

## Resource: Auth

### Login
- Endpoint: POST /api/login
- Method: POST
- Request class: LoginRequest
- Fields:
  - email: required, type email, rules required|email
  - password: required, type string, rules required|string
  - device_name: required, type string, max 100, rules required|string|max:100
- Custom messages: có

### Register
- Endpoint: POST /api/register
- Method: POST
- Request class: RegisterRequest
- Fields:
  - user_name: nullable, string, max 50, unique users.user_name
  - full_name: required, string, max 255
  - email: required, email, max 255, unique users.email
  - phone: nullable, string, max 20, unique users.phone
  - dob: nullable, date
  - address: nullable, string, max 255
  - password: required, string, min 8, confirmed, regex /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/
  - device_name: nullable, string, max 100
- Custom messages: có

### Forgot Password
- Endpoint: POST /api/forgot-password
- Method: POST
- Request class: ForgotPasswordRequest
- Fields:
  - email: required, email, exists users.email
- Custom messages: có

### Reset Password
- Endpoint: POST /api/reset-password
- Method: POST
- Request class: ResetPasswordRequest
- Fields:
  - token: required|string
  - email: required|email|exists:users,email
  - password: required|string|min:8|confirmed
- Custom messages: có

### Change Password
- Endpoint: POST /api/auth/change-password
- Method: POST
- Request class: ChangePasswordRequest
- Fields:
  - current_password: required|string
  - password: required|string|min:8|confirmed
- Custom messages: có

### Logout / confirm endpoint không có body validate
- POST /api/auth/logout
- POST /api/auth/logout-all-devices
- POST /api/auth/logout/{tokenId}/device
- POST /api/tickets/{id}/confirm-payment
- POST /api/tickets/{id}/cancel
- Ghi chú FE: gửi Authorization Bearer token, xử lý lỗi quyền 401/403 từ API.

## Resource: Users

### Update Profile
- Endpoint: PUT /api/users/profile
- Request class: UpdateProfileRequest
- Fields:
  - full_name: sometimes|string|max:255
  - user_name: sometimes|string|max:50|unique:users,user_name,{current_user_id},user_id
  - phone: sometimes|nullable|string|max:20|unique:users,phone,{current_user_id},user_id
  - dob: sometimes|nullable|date
  - address: sometimes|nullable|string|max:255
  - avatar_url: sometimes|nullable|url|max:500
- Create vs Update: profile chỉ có update
- Unique ignore: theo user hiện tại
- Custom messages: có

### Admin Update User
- Endpoint: PUT /api/users/{id}
- Request class: UpdateUserRequest
- Fields:
  - full_name: sometimes|string|max:255
  - user_name: sometimes|string|max:50|unique ignore user_id route {id}
  - phone: sometimes|nullable|string|max:20|unique ignore user_id route {id}
  - role_id: sometimes|uuid|exists:roles,role_id
  - status: sometimes|in:IN_ACTIVE,UN_ACTIVE
  - dob: sometimes|nullable|date
  - address: sometimes|nullable|string|max:255
  - avatar_url: sometimes|nullable|url|max:500
- Unique ignore: Rule::unique(...)->ignore($userId, user_id)
- Custom messages: có

## Resource: Categories

### Create
- Endpoint: POST /api/categories
- Request class: CreateCategoryRequest
- Fields:
  - name: required|string|max:255
  - description: nullable|string
- Custom messages: có

### Update
- Endpoint: PUT /api/categories/{id}
- Request class: UpdateCategoryRequest
- Fields:
  - name: sometimes|required|string|max:255
  - description: nullable|string
- Custom messages: có

## Resource: Cinemas

### Create
- Endpoint: POST /api/cinemas
- Request class: CreateCinemaRequest
- Fields:
  - name: required|string|max:255
  - location: required|string|max:500
  - active: nullable|in:IN_ACTIVE,UN_ACTIVE
  - manager_id: nullable|uuid|exists:users,user_id
- Custom messages: có

### Update
- Endpoint: PUT /api/cinemas/{id}
- Request class: UpdateCinemaRequest
- Fields:
  - name: sometimes|required|string|max:255
  - location: sometimes|required|string|max:500
  - active: sometimes|required|in:IN_ACTIVE,UN_ACTIVE
  - manager_id: nullable|uuid|exists:users,user_id
- Custom messages: có
- Note backend logic thêm: manager bị chặn sửa manager_id và active trong service.

## Resource: Cinema Sales

### Create
- Endpoint: POST /api/cinema-sales
- Request class: CreateCinemaSaleRequest
- Fields:
  - cinema_id: required|uuid|exists:cinemas,cinema_id
  - sale_date: required|date|unique theo cinema_id (Rule::unique(...)->where(cinema_id))
  - gross_amount: nullable|numeric|min:0
- Custom messages: có

### Update
- Endpoint: PUT /api/cinema-sales/{id}
- Request class: UpdateCinemaSaleRequest
- Fields:
  - cinema_id: sometimes|required|uuid|exists:cinemas,cinema_id
  - sale_date: sometimes|required|date|unique theo cinema_id và ignore cinema_sale_id hiện tại
  - gross_amount: nullable|numeric|min:0
- Custom messages: có
- Unique ignore: Rule::unique(...)->ignore($saleId, cinema_sale_id)

## Resource: Combos

### Create
- Endpoint: POST /api/combos
- Request class: CreateComboRequest
- Fields:
  - name: required|string|max:255|unique:combos,name
  - price: nullable|numeric|min:0
  - stock: nullable|integer|min:0
- Custom messages: có

### Update
- Endpoint: PUT /api/combos/{id}
- Request class: UpdateComboRequest
- Fields:
  - name: sometimes|required|string|max:255|unique ignore combo_id route {id}
  - price: nullable|numeric|min:0
  - stock: nullable|integer|min:0
- Custom messages: có
- Unique ignore: Rule::unique(combos,name)->ignore($comboId, combo_id)

## Resource: Employee Roles

### Create
- Endpoint: POST /api/employee-roles
- Request class: CreateEmployeeRoleRequest
- Fields:
  - name: required|in:STAFF,PROBATION
  - description: nullable|string
- Custom messages: có

### Update
- Endpoint: PUT /api/employee-roles/{id}
- Request class: UpdateEmployeeRoleRequest
- Fields:
  - name: sometimes|required|in:STAFF,PROBATION
  - description: nullable|string
- Custom messages: có

## Resource: Employees

### Create
- Endpoint: POST /api/employees
- Request class: CreateEmployeeRequest
- Fields:
  - employee_role_id: required|uuid|exists:employee_roles,employee_role_id
  - user_id: required|uuid|exists:users,user_id|unique:employees,user_id
  - cinema_id: required|uuid|exists:cinemas,cinema_id
  - name: required|string|max:255
  - hire_date: required|date
  - end_date: nullable|date|after:hire_date
  - status: nullable|in:IN_ACTIVE,UN_ACTIVE
- Custom messages: có

### Update
- Endpoint: PUT /api/employees/{id}
- Request class: UpdateEmployeeRequest
- Fields:
  - employee_role_id: sometimes|required|uuid|exists:employee_roles,employee_role_id
  - cinema_id: sometimes|required|uuid|exists:cinemas,cinema_id
  - name: sometimes|required|string|max:255
  - hire_date: sometimes|required|date
  - end_date: nullable|date|after:hire_date
  - status: sometimes|required|in:IN_ACTIVE,UN_ACTIVE
- Custom messages: có

## Resource: Employee Salaries

### Create
- Endpoint: POST /api/employee-salaries
- Request class: CreateEmployeeSalaryRequest
- Fields:
  - employee_id: required|uuid|exists:employees,employee_id|unique:employee_salaries,employee_id
  - bank_number: required|string|max:50
  - bank_name: required|string|max:255
  - net_salary: required|numeric|min:0
  - bonus: nullable|numeric|min:0
  - payment_status: nullable|in:IS_PENDING,IN_ACTIVE,UN_ACTIVE
- Custom messages: có

### Update
- Endpoint: PUT /api/employee-salaries/{id}
- Request class: UpdateEmployeeSalaryRequest
- Fields:
  - bank_number: sometimes|required|string|max:50
  - bank_name: sometimes|required|string|max:255
  - net_salary: sometimes|required|numeric|min:0
  - bonus: nullable|numeric|min:0
  - payment_status: sometimes|required|in:IS_PENDING,IN_ACTIVE,UN_ACTIVE
- Custom messages: có

## Resource: Movies

### Create
- Endpoint: POST /api/movies
- Request class: CreateMovieRequest
- Fields:
  - genre_id: nullable|uuid|exists:categories,id
  - category_ids: required|array|min:1
  - category_ids.*: uuid|exists:categories,id
  - title: required|string|max:255
  - name: required|string|max:255
  - description: nullable|string
  - thumb_url: required|string|max:500
  - trailer_url: required|string|max:500
  - gallery: nullable|array
  - gallery.*: string|max:500
  - duration: required|integer|min:1
  - language: required|string|max:100
  - age: required|integer|min:0|max:255
  - rating: nullable|numeric|min:0|max:9.9
  - release_date: required|date
  - end_date: nullable|date|after_or_equal:release_date
  - status: required|in:IN_ACTIVE,UN_ACTIVE,IS_PENDING
- Custom messages: có

### Update
- Endpoint: PUT /api/movies/{id}
- Request class: UpdateMovieRequest
- Fields:
  - genre_id: nullable|uuid|exists:categories,id
  - category_ids: sometimes|required|array|min:1
  - category_ids.*: uuid|exists:categories,id
  - title: sometimes|required|string|max:255
  - name: sometimes|required|string|max:255
  - description: nullable|string
  - thumb_url: sometimes|required|string|max:500
  - trailer_url: sometimes|required|string|max:500
  - gallery: nullable|array
  - gallery.*: string|max:500
  - duration: sometimes|required|integer|min:1
  - language: sometimes|required|string|max:100
  - age: sometimes|required|integer|min:0|max:255
  - rating: nullable|numeric|min:0|max:9.9
  - release_date: sometimes|required|date
  - end_date: nullable|date|after_or_equal:release_date
  - status: sometimes|required|in:IN_ACTIVE,UN_ACTIVE,IS_PENDING
- Custom messages: có

## Resource: Showtimes

### Create
- Endpoint: POST /api/showtimes
- Request class: CreateShowtimeRequest
- Fields:
  - cinema_id: required|uuid|exists:cinemas,cinema_id
  - movie_id: required|uuid|exists:movies,movie_id
  - starts_at: required|date|after:now
  - ends_at: required|date|after:starts_at
  - screen_type: required|in:2D,3D
- Custom messages: có

### Update
- Endpoint: PUT /api/showtimes/{id}
- Request class: UpdateShowtimeRequest
- Fields:
  - cinema_id: sometimes|required|uuid|exists:cinemas,cinema_id
  - movie_id: sometimes|required|uuid|exists:movies,movie_id
  - starts_at: sometimes|required|date
  - ends_at: sometimes|required|date|after:starts_at
  - screen_type: sometimes|required|in:2D,3D
- Custom messages: có
- Note backend logic thêm: backend tự check overlap lịch chiếu tại service.

## Resource: Seats

### Create
- Endpoint: POST /api/seats
- Request class: CreateSeatRequest
- Fields:
  - showtime_id: required|uuid|exists:showtimes,showtime_id
  - seat_code: required|string|unique theo showtime_id (Rule::unique(...)->where(showtime_id))
  - seat_type: required|in:VIP,COUPLE,NORMAL
  - price: required|numeric|min:0
  - active: nullable|in:IN_ACTIVE,UN_ACTIVE
- Custom messages: có

### Create Bulk
- Endpoint: POST /api/seats/bulk
- Request class: none (validate trực tiếp trong SeatServices::storeBulk)
- Validation direct:
  - showtime_id: required|uuid|exists:showtimes,showtime_id
  - seats: required|array|min:1
  - seats.*.seat_code: required|string
  - seats.*.seat_type: required|in:VIP,COUPLE,NORMAL
  - seats.*.price: required|numeric|min:0
- Validation bổ sung ở service:
  - reject nếu seat_code trùng trong chính request
  - reject nếu seat_code đã tồn tại trong showtime
- Custom messages: có trong validate()

### Update
- Endpoint: PUT /api/seats/{id}
- Request class: UpdateSeatRequest
- Fields:
  - seat_code: sometimes|required|string
  - seat_type: sometimes|required|in:VIP,COUPLE,NORMAL
  - price: sometimes|required|numeric|min:0
  - active: sometimes|required|in:IN_ACTIVE,UN_ACTIVE,HOLD,SOLD
  - hold_until: nullable|date
- Custom messages: có
- Note backend logic thêm: service kiểm tra unique seat_code trong cùng showtime khi đổi mã ghế.

## Resource: Tickets

### Book Ticket
- Endpoint: POST /api/tickets/book
- Request class: CreateTicketRequest
- Fields:
  - showtime_id: required|uuid|exists:showtimes,showtime_id
  - seat_id: required|uuid|exists:seats,seat_id
  - payment_method: required|in:TRANSFER,CARD,CASH
  - combos: nullable|array
  - combos.*.combo_id: required_with:combos|uuid|exists:combos,combo_id|distinct
  - combos.*.qty: required_with:combos|integer|min:1|max:10
- Custom messages: có
- Nested array: có (combos.*.combo_id, combos.*.qty)
- Rule phụ thuộc: required_with, distinct

### Confirm Payment
- Endpoint: POST /api/tickets/{id}/confirm-payment
- Request class: none
- Body: không bắt buộc
- Backend check nghiệp vụ tại service:
  - ticket tồn tại
  - phải có quyền theo role + cinema access
  - ticket phải đang IS_PENDING

## Resource: Payments

### Create/Update
- Không có endpoint create/update public cho payments.
- Payment được tạo tự động khi book ticket.

## Ghi chú cho frontend implementation

## Rule FE mirror được tốt
- required/nullable/sometimes
- type cơ bản: string, integer, numeric, date, email, url, uuid format
- min/max
- enum in:
- regex (password đăng ký)
- confirmed (password_confirmation)
- nested array (combos)

## Rule FE chỉ mirror một phần hoặc nên để backend quyết định
- exists (phụ thuộc DB)
- unique (phụ thuộc DB)
- unique theo context/where/ignore id (cinema sale, combo name, profile/user update)
- các check nghiệp vụ trong service:
  - quyền role/cinema access
  - overlap showtime
  - seat availability HOLD/SOLD
  - stock combo còn đủ
  - trạng thái ticket cho confirm-payment/cancel

## Message handling khuyến nghị cho FE
- Ưu tiên hiển thị message từ response API khi success=false.
- Với lỗi 422, đọc object errors để map vào từng field.
- Với lỗi 401/403, xử lý chung theo auth flow (re-login hoặc thông báo không đủ quyền).

## Custom attributes label
- Không thấy custom attribute labels riêng (attributes()) trong các FormRequest đã rà soát.
- Hệ thống hiện dùng messages() tùy biến theo từng field/rule.
