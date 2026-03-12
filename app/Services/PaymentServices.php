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
        return $this->filterAndPaginate(
            query: $this->paymentModel->with([
                'ticket:ticket_id,code,price,status,user_id,showtime_id',
                'ticket.user:user_id,full_name,email,phone',
            ]),
            request: $request,
            filterableFields: ['method', 'status'],
            sortableFields: ['method', 'status', 'created_at'],
            message: 'Lấy danh sách thanh toán thành công',
        );
    }

    /**
     * Lấy chi tiết thanh toán theo ID.
     */
    public function getById(string $id)
    {
        return $this->findById(
            model: $this->paymentModel,
            id: $id,
            relations: [
                'ticket:ticket_id,code,price,status,user_id,showtime_id,seat_id,movie_id',
                'ticket.user:user_id,full_name,email,phone',
                'ticket.seat:seat_id,seat_code,seat_type,price',
                'ticket.movie:movie_id,code,name,title',
                'ticket.showtime:showtime_id,cinema_id,starts_at,ends_at,screen_type',
                'ticket.showtime.cinema:cinema_id,code,name,location',
            ],
            message: 'Lấy thông tin thanh toán thành công',
            notFoundMessage: 'Không tìm thấy thanh toán',
        );
    }
}
