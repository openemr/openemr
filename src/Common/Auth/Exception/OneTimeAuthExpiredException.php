<?php

namespace OpenEMR\Common\Auth\Exception;

class OneTimeAuthExpiredException extends OneTimeAuthException
{
    public function __construct(string $message = "", $pid = null, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $pid, $code, $previous);
    }
}
