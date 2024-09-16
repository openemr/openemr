<?php

namespace RingCentral\SDK\WebSocket\Events;

use Symfony\Contracts\EventDispatcher\Event;

class NotificationEvent extends Event
{
    /** @var array */
    protected $payload = [];

    /**
     * NotificationEvent constructor.
     *
     * @param array $payload
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    /**
     * @return array
     */
    public function payload()
    {
        return $this->payload;
    }

}