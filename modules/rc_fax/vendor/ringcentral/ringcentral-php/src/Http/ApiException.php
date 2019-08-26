<?php

namespace RingCentral\SDK\Http;

use Exception;

class ApiException extends Exception
{

    /** @var ApiResponse */
    private $_apiResponse;

    public function __construct(
        ApiResponse $apiResponse = null,
        Exception $previous = null
    ) {

        $this->_apiResponse = $apiResponse;

        $message = $previous ? $previous->getMessage() : 'Unknown error';
        $status = $previous ? $previous->getCode() : 0;

        if ($apiResponse) {

            if ($error = $apiResponse->error()) {
                $message = $error;
            }

            if ($apiResponse->response() && $statusCode = $apiResponse->response()->getStatusCode()) {
                $status = $statusCode;
            }

        }

        parent::__construct($message, $status, $previous);

    }

    /**
     * @return ApiResponse
     */
    public function apiResponse()
    {
        return $this->_apiResponse;
    }

}