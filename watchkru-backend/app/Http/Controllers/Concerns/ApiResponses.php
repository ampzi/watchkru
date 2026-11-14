<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Keeps every controller's JSON shape consistent with the API contract
 * documented in the README ({success, message, data, meta?}).
 */
trait ApiResponses
{
    protected function ok($data = [], string $message = 'عملیات با موفقیت انجام شد.', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    protected function paginated(LengthAwarePaginator $paginator, callable|string $resource, string $message = 'دریافت اطلاعات موفقیت‌آمیز بود.'): JsonResponse
    {
        $data = is_string($resource)
            ? $resource::collection($paginator->items())
            : $resource($paginator->items());

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    protected function fail(string $message, int $status = 422, array $errors = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}
