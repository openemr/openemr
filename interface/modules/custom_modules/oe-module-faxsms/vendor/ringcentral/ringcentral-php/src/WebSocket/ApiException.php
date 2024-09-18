<?php

namespace RingCentral\SDK\WebSocket;

use Exception;
use RingCentral\SDK\WebSocket\ApiResponse;

class ApiException extends Exception
{

    /** @var array */
    private $_apiResponse;

    public function __construct(ApiResponse $apiRespponse)
    {

        $this->_apiResponse = $apiRespponse;

        $message = $apiRespponse->error();
        $status = $apiRespponse->status();
        parent::__construct($message, $status);
    }

    /**
     * @return ApiResponse
     */
    public function apiResponse()
    {
        return $this->_apiResponse;
    }

}
