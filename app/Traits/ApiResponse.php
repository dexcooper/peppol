<?php

namespace App\Traits;

trait ApiResponse
{
    protected function success($data = [], string $message = '', int $status = 200)
    {
        $response = [
            'success' => true,
        ];

        if ($message != '') $response['message'] = $message;
        if (!empty($data)) $response['data'] = $data;

        return response()->json($response, $status);
    }

    protected function error(string $message, $status = 400, $errors = [])
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) $response['errors'] = $errors;

        return response()->json($response, $status);
    }
}
