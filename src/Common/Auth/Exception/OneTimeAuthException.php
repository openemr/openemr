<?php

namespace OpenEMR\Common\Auth\Exception;

class OneTimeAuthException extends \Exception
{
    /**
     * @var int|null The pid of the patient that we attempting to run the onetime on
     */
    private $pid;

    public function __construct(string $message = "", $pid = null, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->pid = $pid ?? null;
    }

    public function getPid(): ?int
    {
        return $this->pid;
    }
}
