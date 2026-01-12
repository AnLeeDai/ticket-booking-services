<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;

trait ResponseTrait
{
    public function responseSuccess($data, $message = 'Đã thành công', $code = 200): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    public function responsePaginated($data, $pagination, $message = 'Đã thành công', $code = 200): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
            'pagination' => $pagination,
        ], $code);
    }

    public function responseError($message = 'Đã xảy ra lỗi', $code = 400): JsonResponse
    {
        return response()->json([
            'message' => $message,
        ], $code);
    }

    public function responseNotFound($message = 'Không tìm thấy tài nguyên', $code = 404): JsonResponse
    {
        return response()->json([
            'message' => $message,
        ], $code);
    }

    public function responseUnauthorized($message = 'Chưa được xác thực', $code = 401): JsonResponse
    {
        return response()->json([
            'message' => $message,
        ], $code);
    }

    public function responseForbidden($message = 'Không có quyền truy cập', $code = 403): JsonResponse
    {
        return response()->json([
            'message' => $message,
        ], $code);
    }

    public function responseValidationError($errors = [], $message = 'Lỗi xác thực dữ liệu', $code = 422): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }

    public function responseServerError($message = 'Lỗi máy chủ nội bộ', $code = 500): JsonResponse
    {
        return response()->json([
            'message' => $message,
        ], $code);
    }

    public function responseCreated($data, $message = 'Đã tạo thành công', $code = 201): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    public function responseNoContent($message = 'Không có nội dung', $code = 204)
    {
        return response()->json([
            'message' => $message,
        ], $code);
    }

    public function responseConflict($message = 'Xung đột tài nguyên', $code = 409): JsonResponse
    {
        return response()->json([
            'message' => $message,
        ], $code);
    }
}
