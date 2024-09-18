<?php

namespace RingCentral\SDK\WebSocket\Events;

use Symfony\Contracts\EventDispatcher\Event;

class CloseEvent extends Event
{
    /** @var array */
    protected $_code;
    protected $_reason;

    /**
     * SuccessEvent constructor.
     *
     * @param array $response
     */
    public function __construct(string $code = null, string $reason = null)
    {
        $this->_code = $code;
        $this->_reason = $reason;
    }

    /**
     * @return array
     */
    public function code()
    {
        return $this->_code;
    }

    /**
     * @return array
     */
    public function reason()
    {
        return $this->_reason;
    }
}
