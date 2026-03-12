<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentServices extends Services
{
    public function __construct(
        protected Payment $paymentModel
    ) {}

    /**
     * Lấy danh sách thanh toán.
     */
    public function getAll(Request $request)
    {
        $query = $this->paymentModel->with([
            'ticket:ticket_id,code,price,status,user_id,showtime_id',
            'ticket.user:user_id,full_name,email,phone',
        ]);

        $cinemaIds = $this->getManagedCinemaIds($request);
        if ($cinemaIds !== null) {
            $query->whereHas('ticket.showtime', fn ($q) => $q->whereIn('cinema_id', $cinemaIds));
        }

        return $this->filterAndPaginate(
            query: $query,
            request: $request,
            filterableFields: ['method', 'status'],
            sortableFields: ['method', 'status', 'created_at'],
            message: 'Lấy danh sách thanh toán thành công',
        );
    }

    /**
     * Lấy chi tiết thanh toán theo ID.
     */
    public function getById(Request $request, string $id)
    {
        return $this->tryCatch(function () use ($request, $id) {
            $payment = $this->paymentModel->with([
                'ticket:ticket_id,code,price,status,user_id,showtime_id,seat_id,movie_id',
                'ticket.user:user_id,full_name,email,phone',
                'ticket.seat:seat_id,seat_code,seat_type,price',
                'ticket.movie:movie_id,code,name,title',
                'ticket.showtime:showtime_id,cinema_id,starts_at,ends_at,screen_type',
                'ticket.showtime.cinema:cinema_id,code,name,location',
            ])->find($id);

            if (! $payment) {
                return $this->errorResponse(message: 'Không tìm thấy thanh toán', code: 404);
            }

            if (! $this->canAccessCinema($request, $payment->ticket?->showtime?->cinema_id)) {
                return $this->errorResponse(message: 'Không có quyền xem thanh toán này', code: 403);
            }

            return $this->successResponse(data: $payment, message: 'Lấy thông tin thanh toán thành công');
        });
    }
}
