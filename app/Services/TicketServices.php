<?php

namespace App\Services;

use App\Http\Requests\CreateTicketRequest;
use App\Models\Combo;
use App\Models\Payment;
use App\Models\Seat;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketServices extends Services
{
    public function __construct(
        protected Ticket $ticketModel,
        protected Seat $seatModel,
    ) {}

    /**
     * Danh sách vé (admin).
     */
    public function getAll(Request $request)
    {
        return $this->filterAndPaginate(
            query: $this->ticketModel->with([
                'showtime:showtime_id,cinema_id,movie_id,starts_at,ends_at,screen_type',
                'seat:seat_id,seat_code,seat_type,price',
                'movie:movie_id,code,name,title,thumb_url',
                'user:user_id,full_name,email,phone',
                'combos:combo_id,name,price',
                'payment',
            ]),
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
    public function getById(string $id)
    {
        return $this->findById(
            model: $this->ticketModel,
            id: $id,
            relations: [
                'showtime:showtime_id,cinema_id,movie_id,starts_at,ends_at,screen_type',
                'showtime.cinema:cinema_id,code,name,location',
                'seat:seat_id,seat_code,seat_type,price',
                'movie:movie_id,code,name,title,thumb_url',
                'user:user_id,full_name,email,phone',
                'combos:combo_id,name,price',
                'payment',
            ],
            message: 'Lấy thông tin vé thành công',
            notFoundMessage: 'Không tìm thấy vé',
        );
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
     * 2. Hold ghế (lock 10 phút)
     * 3. Tạo ticket (status IS_PENDING)
     * 4. Gắn combos (nếu có)
     * 5. Tạo payment (status IS_PENDING)
     * 6. Mark ghế = SOLD
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

                // Get showtime to derive movie_id
                $showtime = $seat->showtime;
                $movieId = $showtime->movie_id;

                // Calculate total price
                $ticketPrice = $seat->price;
                $comboTotal = 0;

                if (! empty($data['combos'])) {
                    $comboIds = collect($data['combos'])->pluck('combo_id');
                    $combos = Combo::whereIn('combo_id', $comboIds)->get()->keyBy('combo_id');

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

                // Mark seat as SOLD
                $seat->update([
                    'active' => 'SOLD',
                    'hold_until' => null,
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
                    $comboIds = collect($data['combos'])->pluck('combo_id');
                    $combos = Combo::whereIn('combo_id', $comboIds)->get()->keyBy('combo_id');

                    foreach ($data['combos'] as $comboItem) {
                        $ticket->combos()->attach($comboItem['combo_id'], [
                            'qty' => $comboItem['qty'],
                        ]);

                        $combo = $combos->get($comboItem['combo_id']);
                        if ($combo->stock !== null) {
                            $combo->decrement('stock', $comboItem['qty']);
                        }
                    }
                }

                // Create payment record
                Payment::create([
                    'ticket_id' => $ticket->ticket_id,
                    'method' => $data['payment_method'],
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
                    message: 'Đặt vé thành công',
                );
            });
        });
    }

    /**
     * Xác nhận thanh toán (admin/employee).
     */
    public function confirmPayment(string $ticketId)
    {
        return $this->tryCatch(function () use ($ticketId) {
            return DB::transaction(function () use ($ticketId) {
                $ticket = $this->ticketModel
                    ->where('ticket_id', $ticketId)
                    ->lockForUpdate()
                    ->first();

                if (! $ticket) {
                    return $this->errorResponse(message: 'Không tìm thấy vé', code: 404);
                }

                if ($ticket->status !== 'IS_PENDING') {
                    return $this->errorResponse(message: 'Vé không ở trạng thái chờ thanh toán');
                }

                $ticket->update(['status' => 'IN_ACTIVE']);

                $payment = $ticket->payment;
                if ($payment) {
                    $payment->update(['status' => 'IN_ACTIVE']);
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
                    ->where('ticket_id', $ticketId)
                    ->lockForUpdate()
                    ->first();

                if (! $ticket) {
                    return $this->errorResponse(message: 'Không tìm thấy vé', code: 404);
                }

                // Customer can only cancel their own ticket
                $user = $request->user();
                $isAdmin = $user->role?->name === 'admin';

                if (! $isAdmin && $ticket->user_id !== $user->user_id) {
                    return $this->errorResponse(message: 'Bạn không có quyền huỷ vé này', code: 403);
                }

                if ($ticket->status === 'UN_ACTIVE') {
                    return $this->errorResponse(message: 'Vé đã bị huỷ trước đó');
                }

                // Release seat
                $seat = Seat::find($ticket->seat_id);
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
     * Tự sinh mã vé theo format: TK-YYYYMMDD-XXXX
     */
    private function generateCode(): string
    {
        $prefix = 'TK';
        $date = now()->format('Ymd');
        $like = "{$prefix}-{$date}-%";

        $count = $this->ticketModel->where('code', 'like', $like)->count() + 1;
        $code = sprintf('%s-%s-%04d', $prefix, $date, $count);

        while ($this->ticketModel->where('code', $code)->exists()) {
            $count++;
            $code = sprintf('%s-%s-%04d', $prefix, $date, $count);
        }

        return $code;
    }
}
