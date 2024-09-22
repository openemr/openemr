<?php

namespace RingCentral\SDK\WebSocket;

class ApiResponse
{
    /** @var number */
    protected $_status;

    /** @var array */
    protected $_headers = [];

    /** @var array */
    protected $_body = [];

    /** @var string */
    protected $_requestId;

    /** @var WebSocket */
    protected $_wsc;

    public function __construct(array $response, array $body = null)
    {
        $this->_requestId = $response['messageId'];
        $this->_status = $response['status'];
        $this->_headers = $response['headers'];
        $this->_body = $body ?? [];
        $this->_wsc = $response['wsc'] ?? null;
    }

    /**
     * @return string
     */
    public function requestId()
    {
        return $this->_requestId;
    }

    /**
     * @return number
     */
    public function status()
    {
        return $this->_status;
    }

    /**
     * @return array
     */
    public function headers()
    {
        return $this->_headers;
    }

    /**
     * @return array
     */
    public function body()
    {
        return $this->_body;
    }

     /**
     * @return object
     */
    public function json()
    {
        return (object) $this->_body;
    }

    /**
     * @return array
     */
    public function wsc()
    {
        return $this->_wsc;
    }

    public function ok() {
        return $this->_status >= 200 && $this->_status < 300;
    }

    public function error()
    {
        if (!$this->body()) {
            return null;
        }

        if ($this->ok()) {
            return null;
        }

        $message = 'Status code ' . $this->status() . '';

        if (!empty($this->body()['message'])) {
            $message = $this->body()['message'];
        }

        if (!empty($this->body()['error_description'])) {
            $message = $this->body()['error_description'];
        }

        if (!empty($this->body()['description'])) {
            $message = $this->body()['description'];
        }

        return $message;
    }
}
