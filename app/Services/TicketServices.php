<?php

namespace App\Services;

use App\Http\Requests\CreateTicketRequest;
use App\Models\Combo;
use App\Models\Payment;
use App\Models\Seat;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TicketServices extends Services
{
    /**
     * Thời gian giữ ghế (phút). Sau thời gian này vé IS_PENDING sẽ bị tự động huỷ.
     */
    public const HOLD_MINUTES = 15;

    public function __construct(
        protected Ticket $ticketModel,
        protected Seat $seatModel,
    ) {}

    /**
     * Danh sách vé (admin/manager).
     */
    public function getAll(Request $request)
    {
        $query = $this->ticketModel->with([
            'showtime:showtime_id,cinema_id,movie_id,starts_at,ends_at,screen_type',
            'showtime.cinema:cinema_id,code,name',
            'seat:seat_id,seat_code,seat_type,price',
            'movie:movie_id,code,name,title,thumb_url',
            'user:user_id,full_name,email,phone',
            'combos:combo_id,name,price',
            'payment',
        ]);

        $cinemaIds = $this->getManagedCinemaIds($request);
        if ($cinemaIds !== null) {
            $query->whereHas('showtime', fn ($q) => $q->whereIn('cinema_id', $cinemaIds));
        }

        return $this->filterAndPaginate(
            query: $query,
            request: $request,
            searchableFields: ['code'],
            filterableFields: ['status', 'showtime_id', 'movie_id', 'user_id'],
            sortableFields: ['code', 'price', 'status', 'created_at'],
            message: 'Lấy danh sách vé thành công',
        );
    }

    /**
     * Chi tiết vé.
     */
    public function getById(Request $request, string $id)
    {
        return $this->tryCatch(function () use ($request, $id) {
            $ticket = $this->ticketModel->with([
                'showtime:showtime_id,cinema_id,movie_id,starts_at,ends_at,screen_type',
                'showtime.cinema:cinema_id,code,name,location',
                'seat:seat_id,seat_code,seat_type,price',
                'movie:movie_id,code,name,title,thumb_url',
                'user:user_id,full_name,email,phone',
                'combos:combo_id,name,price',
                'payment',
            ])->find($id);

            if (! $ticket) {
                return $this->errorResponse(message: 'Không tìm thấy vé', code: 404);
            }

            if (! $this->canAccessCinema($request, $ticket->showtime?->cinema_id)) {
                return $this->errorResponse(message: 'Không có quyền xem vé này', code: 403);
            }

            return $this->successResponse(data: $ticket, message: 'Lấy thông tin vé thành công');
        });
    }

    /**
     * Lấy danh sách vé của người dùng hiện tại.
     */
    public function myTickets(Request $request)
    {
        $userId = $request->user()->user_id;

        return $this->filterAndPaginate(
            query: $this->ticketModel->where('user_id', $userId)->with([
                'showtime:showtime_id,cinema_id,movie_id,starts_at,ends_at,screen_type',
                'showtime.cinema:cinema_id,code,name,location',
                'seat:seat_id,seat_code,seat_type,price',
                'movie:movie_id,code,name,title,thumb_url',
                'combos:combo_id,name,price',
                'payment',
            ]),
            request: $request,
            filterableFields: ['status'],
            sortableFields: ['created_at', 'price', 'status'],
            message: 'Lấy danh sách vé của bạn thành công',
        );
    }

    /**
     * ĐẶT VÉ - Flow chính:
     * 1. Validate ghế (seat) thuộc showtime, chưa SOLD/HOLD
     * 2. Hold ghế (lock HOLD_MINUTES phút)
     * 3. Tạo ticket (status IS_PENDING)
     * 4. Gắn combos (nếu có)
     * 5. Tạo payment (status IS_PENDING)
     * 6. Khi confirm payment → ghế = SOLD
     */
    public function bookTicket(CreateTicketRequest $request)
    {
        return $this->tryCatch(function () use ($request) {
            $data = $request->validated();
            $user = $request->user();

            return DB::transaction(function () use ($data, $user) {
                // Lock seat row to prevent race condition
                $seat = $this->seatModel
                    ->where('seat_id', $data['seat_id'])
                    ->lockForUpdate()
                    ->first();

                if (! $seat) {
                    return $this->errorResponse(message: 'Không tìm thấy ghế', code: 404);
                }

                // Validate seat belongs to the showtime
                if ($seat->showtime_id !== $data['showtime_id']) {
                    return $this->errorResponse(message: 'Ghế không thuộc suất chiếu này');
                }

                // Check showtime has not started yet
                $showtime = $seat->showtime()->with(['movie:movie_id,status', 'cinema:cinema_id,active'])->first();
                if (! $showtime || $showtime->starts_at <= now()) {
                    return $this->errorResponse(message: 'Suất chiếu đã bắt đầu hoặc đã kết thúc, không thể đặt vé');
                }

                // Check seat availability - allow HOLD if it's expired
                if ($seat->active === 'SOLD') {
                    return $this->errorResponse(message: 'Ghế đã được bán');
                }

                if ($seat->active === 'HOLD' && $seat->hold_until > now()) {
                    return $this->errorResponse(message: 'Ghế đang được giữ bởi người khác');
                }

                if ($seat->active === 'UN_ACTIVE') {
                    return $this->errorResponse(message: 'Ghế không khả dụng');
                }

                // Derive movie_id from showtime
                $movieId = $showtime->movie_id;

                // Validate: phim phải đang hoạt động
                $movie = $showtime->movie;
                if (! $movie || $movie->status !== 'IN_ACTIVE') {
                    return $this->errorResponse(message: 'Phim không khả dụng hoặc chưa được kích hoạt');
                }

                // Validate: rạp phải đang hoạt động
                $cinema = $showtime->cinema;
                if (! $cinema || $cinema->active !== 'IN_ACTIVE') {
                    return $this->errorResponse(message: 'Rạp chiếu phim không khả dụng');
                }

                // Calculate total price
                $ticketPrice = $seat->price;
                $comboTotal = 0;
                $combos = collect();

                if (! empty($data['combos'])) {
                    $comboIds = collect($data['combos'])->pluck('combo_id');
                    // Lock combo rows to prevent stock race condition
                    $combos = Combo::whereIn('combo_id', $comboIds)
                        ->lockForUpdate()
                        ->get()
                        ->keyBy('combo_id');

                    foreach ($data['combos'] as $comboItem) {
                        $combo = $combos->get($comboItem['combo_id']);
                        if (! $combo) {
                            return $this->errorResponse(message: "Combo không tồn tại: {$comboItem['combo_id']}");
                        }

                        // Check stock
                        if ($combo->stock !== null && $combo->stock < $comboItem['qty']) {
                            return $this->errorResponse(
                                message: "Combo '{$combo->name}' chỉ còn {$combo->stock} sản phẩm",
                            );
                        }

                        $comboTotal += ($combo->price ?? 0) * $comboItem['qty'];
                    }
                }

                $totalPrice = $ticketPrice + $comboTotal;

                // Hold seat (chưa SOLD - chờ confirm payment mới SOLD)
                $seat->update([
                    'active' => 'HOLD',
                    'hold_until' => now()->addMinutes(self::HOLD_MINUTES),
                ]);

                // Create ticket
                $ticket = $this->ticketModel->create([
                    'showtime_id' => $data['showtime_id'],
                    'seat_id' => $data['seat_id'],
                    'user_id' => $user->user_id,
                    'movie_id' => $movieId,
                    'code' => $this->generateCode(),
                    'price' => $totalPrice,
                    'status' => 'IS_PENDING',
                ]);

                // Attach combos and reduce stock
                if (! empty($data['combos'])) {
                    foreach ($data['combos'] as $comboItem) {
                        $ticket->combos()->attach($comboItem['combo_id'], [
                            'qty' => $comboItem['qty'],
                        ]);

                        $combo = $combos->get($comboItem['combo_id']);
                        if ($combo && $combo->stock !== null) {
                            $combo->decrement('stock', $comboItem['qty']);
                        }
                    }
                }

                // Create payment record
                Payment::create([
                    'ticket_id' => $ticket->ticket_id,
                    'method' => $data['payment_method'],
                    'amount' => $totalPrice,
                    'status' => 'IS_PENDING',
                ]);

                return $this->successResponse(
                    data: $ticket->load([
                        'seat:seat_id,seat_code,seat_type,price',
                        'showtime:showtime_id,cinema_id,movie_id,starts_at,ends_at,screen_type',
                        'showtime.cinema:cinema_id,code,name,location',
                        'movie:movie_id,code,name,title,thumb_url',
                        'combos:combo_id,name,price',
                        'payment',
                    ]),
                    message: 'Đặt vé thành công. Vui lòng thanh toán trong '.self::HOLD_MINUTES.' phút',
                );
            });
        });
    }

    /**
     * Xác nhận thanh toán (admin/manager/employee).
     * Khi confirm: ticket → IN_ACTIVE, payment → IN_ACTIVE, seat → SOLD.
     */
    public function confirmPayment(Request $request, string $ticketId)
    {
        return $this->tryCatch(function () use ($request, $ticketId) {
            return DB::transaction(function () use ($request, $ticketId) {
                $ticket = $this->ticketModel
                    ->with('showtime:showtime_id,cinema_id')
                    ->where('ticket_id', $ticketId)
                    ->lockForUpdate()
                    ->first();

                if (! $ticket) {
                    return $this->errorResponse(message: 'Không tìm thấy vé', code: 404);
                }

                if (! $this->canAccessCinema($request, $ticket->showtime?->cinema_id)) {
                    return $this->errorResponse(message: 'Không có quyền xác nhận thanh toán vé này', code: 403);
                }

                if ($ticket->status !== 'IS_PENDING') {
                    return $this->errorResponse(message: 'Vé không ở trạng thái chờ thanh toán');
                }

                $ticket->update(['status' => 'IN_ACTIVE']);

                $payment = $ticket->payment;
                if ($payment) {
                    $payment->update(['status' => 'IN_ACTIVE']);
                }

                // Mark seat as SOLD (chính thức bán)
                $seat = Seat::where('seat_id', $ticket->seat_id)->lockForUpdate()->first();
                if ($seat) {
                    $seat->update([
                        'active' => 'SOLD',
                        'hold_until' => null,
                    ]);
                }

                return $this->successResponse(
                    data: $ticket->load(['seat', 'payment', 'combos']),
                    message: 'Xác nhận thanh toán thành công',
                );
            });
        });
    }

    /**
     * Huỷ vé.
     */
    public function cancelTicket(string $ticketId, Request $request)
    {
        return $this->tryCatch(function () use ($ticketId, $request) {
            return DB::transaction(function () use ($ticketId, $request) {
                $ticket = $this->ticketModel
                    ->with('showtime:showtime_id,cinema_id,starts_at')
                    ->where('ticket_id', $ticketId)
                    ->lockForUpdate()
                    ->first();

                if (! $ticket) {
                    return $this->errorResponse(message: 'Không tìm thấy vé', code: 404);
                }

                // Customer can only cancel their own ticket
                $user = $request->user();
                $role = $user->role?->name;

                if ($role === 'admin') {
                    // Admin toàn quyền
                } elseif ($role === 'manager') {
                    if (! $this->canAccessCinema($request, $ticket->showtime?->cinema_id)) {
                        return $this->errorResponse(message: 'Không có quyền huỷ vé này', code: 403);
                    }
                } elseif ($ticket->user_id !== $user->user_id) {
                    return $this->errorResponse(message: 'Bạn không có quyền huỷ vé này', code: 403);
                }

                if ($ticket->status === 'UN_ACTIVE') {
                    return $this->errorResponse(message: 'Vé đã bị huỷ trước đó');
                }

                // Không cho huỷ vé khi suất chiếu đã bắt đầu
                if ($ticket->showtime && $ticket->showtime->starts_at <= now()) {
                    return $this->errorResponse(message: 'Không thể huỷ vé khi suất chiếu đã bắt đầu');
                }

                // Customer chỉ có thể huỷ vé khi đang IS_PENDING
                if ($role === 'customer' && $ticket->status !== 'IS_PENDING') {
                    return $this->errorResponse(message: 'Bạn chỉ có thể huỷ vé đang chờ thanh toán. Vui lòng liên hệ nhân viên để được hỗ trợ');
                }

                // Release seat
                $seat = Seat::where('seat_id', $ticket->seat_id)->lockForUpdate()->first();
                if ($seat) {
                    $seat->update([
                        'active' => 'IN_ACTIVE',
                        'hold_until' => null,
                    ]);
                }

                // Restore combo stock
                foreach ($ticket->combos as $combo) {
                    if ($combo->stock !== null) {
                        $combo->increment('stock', $combo->pivot->qty);
                    }
                }

                // Cancel ticket and payment
                $ticket->update(['status' => 'UN_ACTIVE']);

                $payment = $ticket->payment;
                if ($payment) {
                    $payment->update(['status' => 'UN_ACTIVE']);
                }

                return $this->successResponse(
                    data: $ticket->load(['seat', 'payment']),
                    message: 'Huỷ vé thành công',
                );
            });
        });
    }

    /**
     * Tự sinh mã vé theo format: TK-YYYYMMDD-XXXX-RRRR
     * Sử dụng random suffix để tránh race condition.
     */
    private function generateCode(): string
    {
        $prefix = 'TK';
        $date = now()->format('Ymd');
        $maxAttempts = 10;

        for ($i = 0; $i < $maxAttempts; $i++) {
            $random = strtoupper(Str::random(4));
            $code = sprintf('%s-%s-%s', $prefix, $date, $random);

            if (! $this->ticketModel->withTrashed()->where('code', $code)->exists()) {
                return $code;
            }
        }

        // Fallback: dùng UUID ngắn
        return sprintf('%s-%s-%s', $prefix, $date, strtoupper(substr(Str::uuid()->toString(), 0, 8)));
    }

    /**
     * Tự động huỷ vé IS_PENDING quá hạn hold.
     * Được gọi bởi scheduled command.
     */
    public function cancelExpiredPendingTickets(): int
    {
        $expiredTickets = $this->ticketModel
            ->where('status', 'IS_PENDING')
            ->where('created_at', '<', now()->subMinutes(self::HOLD_MINUTES))
            ->get();

        $cancelled = 0;

        foreach ($expiredTickets as $ticket) {
            DB::transaction(function () use ($ticket, &$cancelled) {
                // Re-lock to avoid race
                $fresh = $this->ticketModel
                    ->where('ticket_id', $ticket->ticket_id)
                    ->where('status', 'IS_PENDING')
                    ->lockForUpdate()
                    ->first();

                if (! $fresh) {
                    return;
                }

                // Release seat
                $seat = Seat::where('seat_id', $fresh->seat_id)->lockForUpdate()->first();
                if ($seat && in_array($seat->active, ['HOLD', 'SOLD'])) {
                    $seat->update([
                        'active' => 'IN_ACTIVE',
                        'hold_until' => null,
                    ]);
                }

                // Restore combo stock
                foreach ($fresh->combos as $combo) {
                    if ($combo->stock !== null) {
                        $combo->increment('stock', $combo->pivot->qty);
                    }
                }

                // Cancel ticket & payment
                $fresh->update(['status' => 'UN_ACTIVE']);

                $payment = $fresh->payment;
                if ($payment) {
                    $payment->update(['status' => 'UN_ACTIVE']);
                }

                $cancelled++;
            });
        }

        return $cancelled;
    }
}
