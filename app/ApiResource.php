<?php

namespace App;

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

public function paginatedResponse($resourceCollection, $message = null, $code = 200)
    {
        $response = $resourceCollection->response()->getData(true);

        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $response['data'],
            'links'   => $response['links'] ?? null,
            'meta'    => $response['meta'] ?? null,
        ], $code);
    }
}
