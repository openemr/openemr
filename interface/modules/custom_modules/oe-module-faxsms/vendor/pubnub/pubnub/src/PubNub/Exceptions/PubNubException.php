<?php

namespace PubNub\Exceptions;

use PubNub\Models\ResponseHelpers\PNStatus;

/**
 * Class PubNubException
 * @package PubNub\Exceptions
 *
 * Should be extended by following exception types:
 *
 * - PubNubValidationException (like 'channel missing', 'subscribe key missing')
 * - PubNubRequestException (like 'network error', 'request timeout')
 * - PubNubServerException (like 400, 403, 500, etc.)
 */
abstract class PubNubException extends \Exception
{
    /** @var  PNStatus */

    private $status;
    /**
     * @return PNStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param PNStatus $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }
}
