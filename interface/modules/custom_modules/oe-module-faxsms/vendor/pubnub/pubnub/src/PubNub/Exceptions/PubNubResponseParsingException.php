<?php

namespace PubNub\Exceptions;


class PubNubResponseParsingException extends PubNubException
{
    const MESSAGE = "Unable to parse server response";

    /** @var  string */
    protected $message;

    /** @var  string */
    protected $responseString;

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->message = static::MESSAGE . ": " . $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getResponseString()
    {
        return $this->responseString;
    }

    /**
     * @param string $responseString
     * @return $this
     */
    public function setResponseString($responseString)
    {
        $this->responseString = $responseString;

        return $this;
    }
}
