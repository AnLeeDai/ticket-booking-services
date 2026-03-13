<?php

namespace App\Services;

use App\Http\Requests\CreateSeatRequest;
use App\Http\Requests\UpdateSeatRequest;
use App\Models\Seat;
use App\Models\Showtime;
use Illuminate\Http\Request;

class SeatServices extends Services
{
    public function __construct(
        protected Seat $seatModel
    ) {}

    public function getAll(Request $request)
    {
        return $this->filterAndPaginate(
            query: $this->seatModel->with([
                'showtime:showtime_id,cinema_id,movie_id,starts_at,ends_at,screen_type',
            ]),
            request: $request,
            searchableFields: ['seat_code'],
            filterableFields: ['showtime_id', 'seat_type', 'active'],
            sortableFields: ['seat_code', 'seat_type', 'price', 'active', 'created_at'],
            message: 'Lấy danh sách ghế thành công',
        );
    }

    public function getById(string $id)
    {
        return $this->findById(
            model: $this->seatModel,
            id: $id,
            relations: [
                'showtime:showtime_id,cinema_id,movie_id,starts_at,ends_at,screen_type',
                'activeTicket',
            ],
            message: 'Lấy thông tin ghế thành công',
            notFoundMessage: 'Không tìm thấy ghế',
        );
    }

    /**
     * Lấy danh sách ghế theo suất chiếu.
     */
    public function getByShowtime(string $showtimeId, Request $request)
    {
        return $this->tryCatch(function () use ($showtimeId, $request) {
            // Release held seats that have expired
            $this->releaseExpiredHolds();

            $query = $this->seatModel->where('showtime_id', $showtimeId);

            return $this->filterAndPaginate(
                query: $query,
                request: $request,
                filterableFields: ['seat_type', 'active'],
                sortableFields: ['seat_code', 'seat_type', 'price'],
                message: 'Lấy danh sách ghế theo suất chiếu thành công',
            );
        });
    }

    public function store(CreateSeatRequest $request)
    {
        $data = $request->validated();
        $showtime = Showtime::find($data['showtime_id']);

        if ($showtime && ! $this->canAccessCinema($request, $showtime->cinema_id)) {
            return $this->errorResponse(message: 'Không có quyền thêm ghế cho suất chiếu này', code: 403);
        }

        return $this->createRecord(
            model: $this->seatModel,
            data: $data,
            message: 'Tạo ghế thành công',
            failMessage: 'Tạo ghế thất bại',
        );
    }

    /**
     * Tạo hàng loạt ghế cho suất chiếu.
     */
    public function storeBulk(Request $request)
    {
        return $this->tryCatch(function () use ($request) {
            $validated = $request->validate([
                'showtime_id' => 'required|uuid|exists:showtimes,showtime_id',
                'seats' => 'required|array|min:1',
                'seats.*.seat_code' => 'required|string',
                'seats.*.seat_type' => 'required|in:VIP,COUPLE,NORMAL',
                'seats.*.price' => 'required|numeric|min:0',
            ], [
                'showtime_id.required' => 'Suất chiếu không được để trống',
                'showtime_id.exists' => 'Suất chiếu không tồn tại',
                'seats.required' => 'Danh sách ghế không được để trống',
                'seats.*.seat_code.required' => 'Mã ghế không được để trống',
                'seats.*.seat_type.required' => 'Loại ghế không được để trống',
                'seats.*.seat_type.in' => 'Loại ghế không hợp lệ. Chọn: VIP, COUPLE, NORMAL',
                'seats.*.price.required' => 'Giá ghế không được để trống',
            ]);

            $showtimeId = $validated['showtime_id'];

            $showtime = Showtime::find($showtimeId);
            if ($showtime && ! $this->canAccessCinema($request, $showtime->cinema_id)) {
                return $this->errorResponse(message: 'Không có quyền thêm ghế cho suất chiếu này', code: 403);
            }

            $seats = collect($validated['seats'])->map(fn ($seat) => array_merge($seat, [
                'showtime_id' => $showtimeId,
                'active' => 'IN_ACTIVE',
            ]));

            // Check for duplicate seat_codes within the request
            $codes = $seats->pluck('seat_code');
            if ($codes->count() !== $codes->unique()->count()) {
                return $this->errorResponse(message: 'Mã ghế bị trùng trong danh sách');
            }

            // Check for existing seat_codes in the same showtime
            $existing = $this->seatModel
                ->where('showtime_id', $showtimeId)
                ->whereIn('seat_code', $codes->toArray())
                ->pluck('seat_code');

            if ($existing->isNotEmpty()) {
                return $this->errorResponse(
                    message: 'Mã ghế đã tồn tại: '.$existing->implode(', '),
                );
            }

            $created = [];
            foreach ($seats as $seatData) {
                $created[] = $this->seatModel->create($seatData);
            }

            return $this->successResponse(
                data: $created,
                message: 'Tạo thành công '.count($created).' ghế',
            );
        });
    }

    public function update(UpdateSeatRequest $request, string $id)
    {
        return $this->tryCatch(function () use ($request, $id) {
            $seat = $this->seatModel->with('showtime:showtime_id,cinema_id')->find($id);

            if (! $seat) {
                return $this->errorResponse(message: 'Không tìm thấy ghế', code: 404);
            }

            if (! $this->canAccessCinema($request, $seat->showtime?->cinema_id)) {
                return $this->errorResponse(message: 'Không có quyền cập nhật ghế này', code: 403);
            }

            $data = array_filter($request->validated(), fn ($v) => ! is_null($v));

            // Validate seat_code uniqueness within same showtime
            if (isset($data['seat_code']) && $data['seat_code'] !== $seat->seat_code) {
                $exists = $this->seatModel
                    ->where('showtime_id', $seat->showtime_id)
                    ->where('seat_code', $data['seat_code'])
                    ->where('seat_id', '!=', $id)
                    ->exists();

                if ($exists) {
                    return $this->errorResponse(message: 'Mã ghế đã tồn tại trong suất chiếu này');
                }
            }

            $seat->update($data);

            return $this->successResponse(data: $seat->fresh(), message: 'Cập nhật ghế thành công');
        });
    }

    public function destroy(Request $request, string $id)
    {
        return $this->tryCatch(function () use ($request, $id) {
            $seat = $this->seatModel->with('showtime:showtime_id,cinema_id')->find($id);

            if (! $seat) {
                return $this->errorResponse(message: 'Không tìm thấy ghế', code: 404);
            }

            if (! $this->canAccessCinema($request, $seat->showtime?->cinema_id)) {
                return $this->errorResponse(message: 'Không có quyền xoá ghế này', code: 403);
            }

            if ($seat->active === 'SOLD') {
                return $this->errorResponse(message: 'Không thể xoá ghế đã được bán');
            }

            if ($seat->active === 'HOLD' && $seat->hold_until > now()) {
                return $this->errorResponse(message: 'Không thể xoá ghế đang được giữ');
            }

            $seat->delete();

            return $this->successResponse(data: null, message: 'Xoá ghế thành công');
        });
    }

    /**
     * Giải phóng ghế đã hết thời gian giữ.
     */
    public function releaseExpiredHolds(): void
    {
        $this->seatModel
            ->where('active', 'HOLD')
            ->where('hold_until', '<', now())
            ->update([
                'active' => 'IN_ACTIVE',
                'hold_until' => null,
            ]);
    }
}
