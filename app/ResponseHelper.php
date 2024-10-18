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

    public static function buildErrorResponse($message = "An error occurred", $status = Response::HTTP_BAD_REQUEST, $error = null)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error' => $error
        ], $status);
    }

    public static function buildNotFoundResponse($message = "Not found")
    {
        return self::buildErrorResponse($message, Response::HTTP_NOT_FOUND);
    }

    public static function buildUnauthorizedResponse($message = "Unauthorized")
    {
        return self::buildErrorResponse($message, Response::HTTP_UNAUTHORIZED);
    }

    public static function buildInternalServerErrorResponse($message = "Internal Server Error")
    {
        return self::buildErrorResponse($message, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public static function buildValidationErrorResponse($error = null, $message = "Validation Error")
    {
        return self::buildErrorResponse($message, Response::HTTP_UNPROCESSABLE_ENTITY, $error);
    }

    public static function unsetKeysFromData(object $data, array $keys)
    {
        foreach ($keys as $key) {
            unset($data->$key);
        }
        return $data;
    }
}
