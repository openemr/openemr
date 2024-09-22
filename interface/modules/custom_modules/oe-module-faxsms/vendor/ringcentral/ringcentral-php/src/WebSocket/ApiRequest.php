<?php

namespace RingCentral\SDK\WebSocket;

class ApiRequest
{
    /** @var string */
    protected $_method;

    /** @var string */
    protected $_path;

    /** @var array */
    protected $_query = [];

    /** @var array */
    protected $_headers = [];

    /** @var array */
    protected $_body = [];

    /** @var string */
    protected $_requestId;

    /**
     * ApiRequest constructor.
     *
     * @param string $method
     * @param string $path
     * @param array  $query
     * @param array  $headers
     * @param array  $body
     */
    public function __construct(array $options = [])
    {
        $this->_requestId = uniqid();
        $this->_method = $options['method'] ?? 'GET';
        $this->_path = $options['path'];
        $this->_query = $options['query'] ?? [];
        $this->_headers = $options['headers'] ?? [];
        $this->_body = $options['body'] ?? [];
    }

    /**
     * @return string
     */
    public function method()
    {
        return $this->_method;
    }

    /**
     * @return string
     */
    public function path()
    {
        return $this->_path;
    }

    /**
     * @return array
     */
    public function query()
    {
        return $this->_query;
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

    public function requestId()
    {
        return $this->_requestId;
    }

    public function toJson() {
        $request = [
            'type' => 'ClientRequest',
            'messageId' => $this->requestId(),
            'method' => $this->method(),
            'path' => $this->path(),
        ];
        if (!empty($this->query())) {
            $request['query'] = $this->query();
        }
        $requestData = array($request);
        if (!empty($this->body())) {
            array_push($requestData, $this->body());
        }
        return json_encode($requestData);
    }
}
