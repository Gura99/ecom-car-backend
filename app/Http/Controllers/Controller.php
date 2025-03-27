<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

abstract class Controller
{
    use ValidatesRequests;
    protected function successResponse($data, $message = "Success", $statusCode = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'result'            => true,
            'result_code'       => $statusCode,
            'result_message'    => $message,
            'body'              => $data,
        ], 200);
    }

    protected function errorResponse($message = "Error", $statusCode = 422): JsonResponse
    {
        return response()->json([
            'result'            => false,
            'result_code'       => $statusCode,
            'result_message'    => $message,
            'body'              => "fail",
        ], $statusCode);
    }

    public function registerRules()
    {
        return [
            'username'      => 'required|string',
            'email'         => 'required|string|email|unique:users',
            'password'      => 'required|string ',
        ];
    }
}
