<?php

namespace App\Traits;

trait ApiResponse
{
    protected function successResponse(
        string $message,
        mixed $data = null,
        int $status = 200
    ) {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    protected function errorResponse(
        string $message,
        int $status = 400,
        mixed $data = null
    ) {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    protected function paginationResponse(
        string $message,
        $paginator
    ) {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ]
        ]);
    }
}