<?php

namespace App\Traits;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

trait ApiTrait
{

    public function ApiResponseMessage($message, $code)
    {
        return response()->json([
            'code'    => $code,
            'message' => $message
        ], $code);
    }

    public function ApiResponseData($data , $status , $code ,$message, $pagination = "", $total = "")
    {
        return response()->json([
            'status'     => $status,
            'code'       => $code,
            'message'    => $message,
            'data'       => $data,
            'pagination' => $pagination,
            'total' => $total,
        ], $code);
    }

}
