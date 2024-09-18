<?php

namespace PubNub\Exceptions;

class PubNubCryptoException extends PubNubException
{
    /** @var  \Exception */
    protected $originalException;

    /**
     * @return \Exception
     */
    public function getOriginalException()
    {
        return $this->originalException;
    }

    /**
     * @param \Exception $originalException
     * @return $this
     */
    public function setOriginalException($originalException)
    {
        $this->originalException = $originalException;
        $this->message = $originalException->getMessage();

        return $this;
    }
}
