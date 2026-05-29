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
}
