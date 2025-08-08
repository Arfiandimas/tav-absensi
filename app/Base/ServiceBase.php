<?php

namespace App\Base;

use App\Contracts\ServiceContract;
use App\Responses\ServiceResponse;
use Illuminate\Support\Facades\Log;

abstract class ServiceBase implements ServiceContract {

    /**
     * To return success response of the service
     *
     * @param $result
     * @param string $message
     * @return ServiceResponse
     */
    protected static function success($result, string $message = 'true'): ServiceResponse {
        return new ServiceResponse($result, $message, true, 'success');
    }

    /**
     * To return error response of the service
     *
     * @param $result
     * @param string $message
     * @param int $status
     * @return ServiceResponse
     */
    protected static function error($result, string $message = "error", bool $status = false): ServiceResponse {
        return new ServiceResponse($result, $message, $status, 'error');
    }

    /**
     * To return error response of the service
     *
     * @param $result
     * @param string $message
     * @param int $status
     * @return ServiceResponse
     */
    protected static function warning($result, string $message = "warning", bool $status = false): ServiceResponse {
        return new ServiceResponse($result, $message, $status, 'warning');
    }

    protected static function catchError(\Throwable $th, $result, string $message = "error"):ServiceResponse
    {
        Log::error($th->getMessage(), [
            'file' => $th->getFile(),
            'line' => $th->getLine()
        ]);
        return new ServiceResponse($result, $message, false, 'error');
    }
}
