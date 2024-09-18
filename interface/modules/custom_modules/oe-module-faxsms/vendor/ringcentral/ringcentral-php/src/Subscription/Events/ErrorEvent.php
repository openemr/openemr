<?php

namespace RingCentral\SDK\Subscription\Events;

use Symfony\Contracts\EventDispatcher\Event;

class ErrorEvent extends Event
{
    /** @var \Exception */
    protected $exception;

    /**
     * ErrorEvent constructor.
     *
     * @param \Exception $exception
     */
    public function __construct(\Exception $exception)
    {
        $this->exception = $exception;
    }

    /**
     * @return \Exception
     */
    public function exception()
    {
        return $this->exception;
    }

}