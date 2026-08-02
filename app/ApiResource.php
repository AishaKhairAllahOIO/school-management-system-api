<?php

namespace App;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;


trait ApiResource
{
    protected function successResponse($data, string $message, int $code = 200)
    {
        $response = [
            'status' => true,
            'message' => $message,
        ];

        if (!is_null($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

public function errorResponse($message = null, $code = 422, $errors = null)
{
    $response = [
        'status'  => false,
        'message' => $message,
    ];

    if ($errors !== null) {
        $response['errors'] = $errors;
    }

    return response()->json($response, $code);
}

protected function paginatedResponse(
        LengthAwarePaginator $paginator,
        ?string $message = null,
        int $code = 200
    ) {
        return response()->json([
            'status' => true,
            'message' => $message,

            'data' => $paginator->items(),

            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'previous' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],

            'meta' => [
                'current_page' => $paginator->currentPage(),
                'from' => $paginator->firstItem(),
                'last_page' => $paginator->lastPage(),
                'path' => $paginator->path(),
                'per_page' => $paginator->perPage(),
                'to' => $paginator->lastItem(),
                'total' => $paginator->total(),
            ],
        ], $code);
    }
}
