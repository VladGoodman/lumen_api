<?php

namespace App\Http\Helper;

class ResponseHelper
{
    public static function form($message, $status, $data = null)
    {
        return response()->json([
            "data" => $data,
            "message" => $message
        ], $status);
    }
}
