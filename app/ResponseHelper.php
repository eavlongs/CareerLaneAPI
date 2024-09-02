<?php

namespace App;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;

class ResponseHelper extends Controller
{
    public static function buildSuccessResponse($data = null, $message = "Request Successful")
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], Response::HTTP_OK);
    }

    public static function buildErrorResponse($message = "An error occurred", $status = Response::HTTP_BAD_REQUEST)
    {
        return response()->json([
            'success' => false,
            'message' => $message
        ], $status);
    }
}
