<?php

namespace RingCentral\SDK\WebSocket\Events;

use RingCentral\SDK\WebSocket\ApiResponse;
use Symfony\Contracts\EventDispatcher\Event;

class SuccessEvent extends Event
{
    /** @var ApiResponse */
    protected $_response;

    /**
     * SuccessEvent constructor.
     *
     * @param ApiResponse $response
     */
    public function __construct(ApiResponse $response)
    {
        $this->_response = $response;
    }

    /**
     * @return ApiResponse
     */
    public function apiResponse()
    {
        return $this->_response;
    }

}
