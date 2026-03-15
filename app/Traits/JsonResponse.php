<?php

namespace App\Traits;

trait JsonResponse
{
    protected function successResponse(
        $data,
        $message = '',
        $code = 200)
    {
        return response()->json(
            [
                'success' => true,
                'message' => $message,
                'data' => $data,
            ],
            $code
        );
    }

    protected function errorResponse(
        $message = '',
        $code = 400)
    {
        return response()->json(
            [
                'success' => false,
                'message' => $message,
            ],
            $code
        );
    }

    protected function serverErrorResponse(
        $message = 'Lỗi hệ thống',
        $description = '',
        $code = 500)
    {
        return response()->json(
            [
                'success' => false,
                'message' => $message,
                'description' => $description,
            ],
            $code
        );
    }
}
