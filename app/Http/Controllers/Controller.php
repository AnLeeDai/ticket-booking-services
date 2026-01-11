<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;


abstract class Controller
{
    protected function responseSuccess($data, $message = 'Đã thành công', $code = 200): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function responsePaginated($data, $pagination, $message = 'Đã thành công', $code = 200): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
            'pagination' => $pagination,
        ], $code);
    }

    protected function responseError($message = 'Đã xảy ra lỗi', $code = 400): JsonResponse
    {
        return response()->json([
            'message' => $message,
        ], $code);
    }

    protected function responseNotFound($message = 'Không tìm thấy tài nguyên', $code = 404): JsonResponse
    {
        return response()->json([
            'message' => $message,
        ], $code);
    }

    protected function responseUnauthorized($message = 'Chưa được xác thực', $code = 401): JsonResponse
    {
        return response()->json([
            'message' => $message,
        ], $code);
    }

    protected function responseForbidden($message = 'Không có quyền truy cập', $code = 403): JsonResponse
    {
        return response()->json([
            'message' => $message,
        ], $code);
    }

    protected function responseValidationError($errors = [], $message = 'Lỗi xác thực dữ liệu', $code = 422): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }

    protected function responseServerError($message = 'Lỗi máy chủ nội bộ', $code = 500): JsonResponse
    {
        return response()->json([
            'message' => $message,
        ], $code);
    }

    protected function responseCreated($data, $message = 'Đã tạo thành công', $code = 201): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function responseNoContent($message = 'Không có nội dung', $code = 204)
    {
        return response()->json([
            'message' => $message,
        ], $code);
    }
}
