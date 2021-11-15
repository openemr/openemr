<?php

/**
 * @see       https://github.com/laminas/laminas-http for the canonical source repository
 * @copyright https://github.com/laminas/laminas-http/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-http/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Http\Client\Adapter;

use Laminas\Http\Response;
use Laminas\Stdlib\ArrayUtils;
use Traversable;

/**
 * A testing-purposes adapter.
 *
 * Should be used to test all components that rely on Laminas\Http\Client,
 * without actually performing an HTTP request. You should instantiate this
 * object manually, and then set it as the client's adapter. Then, you can
 * set the expected response using the setResponse() method.
 */
class Test implements AdapterInterface
{
    /**
     * Parameters array
     *
     * @var array
     */
    protected $config = [];

    /**
     * Buffer of responses to be returned by the read() method.  Can be
     * set using setResponse() and addResponse().
     *
     * @var array
     */
    protected $responses = ["HTTP/1.1 400 Bad Request\r\n\r\n"];

    /**
     * Current position in the response buffer
     *
     * @var int
     */
    protected $responseIndex = 0;

    /**
     * Whether or not the next request will fail with an exception
     *
     * @var bool
     */
    protected $nextRequestWillFail = false;

    /**
     * Adapter constructor, currently empty. Config is set using setOptions()
     */
    public function __construct()
    {
    }

    /**
     * Set the nextRequestWillFail flag
     *
     * @param  bool $flag
     * @return \Laminas\Http\Client\Adapter\Test
     */
    public function setNextRequestWillFail($flag)
    {
        $this->nextRequestWillFail = (bool) $flag;

        return $this;
    }

    /**
     * Set the configuration array for the adapter
     *
     * @param  array|Traversable $options
     * @throws Exception\InvalidArgumentException
     */
    public function setOptions($options = [])
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        if (! is_array($options)) {
            throw new Exception\InvalidArgumentException(
                'Array or Traversable object expected, got ' . gettype($options)
            );
        }

        foreach ($options as $k => $v) {
            $this->config[strtolower($k)] = $v;
        }
    }


    /**
     * Connect to the remote server
     *
     * @param  string $host
     * @param  int    $port
     * @param  bool   $secure
     * @throws Exception\RuntimeException
     */
    public function connect($host, $port = 80, $secure = false)
    {
        if ($this->nextRequestWillFail) {
            $this->nextRequestWillFail = false;
            throw new Exception\RuntimeException('Request failed');
        }
    }

    /**
     * Send request to the remote server
     *
     * @param string        $method
     * @param \Laminas\Uri\Uri $uri
     * @param string        $httpVer
     * @param array         $headers
     * @param string        $body
     * @return string Request as string
     */
    public function write($method, $uri, $httpVer = '1.1', $headers = [], $body = '')
    {
        // Build request headers
        $path = $uri->getPath();
        if (empty($path)) {
            $path = '/';
        }
        $query = $uri->getQuery();
        $path .= $query ? '?' . $query : '';
        $request = $method . ' ' . $path . ' HTTP/' . $httpVer . "\r\n";
        foreach ($headers as $k => $v) {
            if (is_string($k)) {
                $v = $k . ': ' . $v;
            }
            $request .= $v . "\r\n";
        }

        // Add the request body
        $request .= "\r\n" . $body;

        // Do nothing - just return the request as string

        return $request;
    }

    /**
     * Return the response set in $this->setResponse()
     *
     * @return string
     */
    public function read()
    {
        if ($this->responseIndex >= count($this->responses)) {
            $this->responseIndex = 0;
        }
        return $this->responses[$this->responseIndex++];
    }

    /**
     * Close the connection (dummy)
     *
     */
    public function close()
    {
    }

    /**
     * Set the HTTP response(s) to be returned by this adapter
     *
     * @param \Laminas\Http\Response|array|string $response
     */
    public function setResponse($response)
    {
        if ($response instanceof Response) {
            $response = $response->toString();
        }

        $this->responses = (array) $response;
        $this->responseIndex = 0;
    }

    /**
     * Add another response to the response buffer.
     *
     * @param string|Response $response
     */
    public function addResponse($response)
    {
        if ($response instanceof Response) {
            $response = $response->toString();
        }

        $this->responses[] = $response;
    }

    /**
     * Sets the position of the response buffer.  Selects which
     * response will be returned on the next call to read().
     *
     * @param int $index
     * @throws Exception\OutOfRangeException
     */
    public function setResponseIndex($index)
    {
        if ($index < 0 || $index >= count($this->responses)) {
            throw new Exception\OutOfRangeException(
                'Index out of range of response buffer size'
            );
        }
        $this->responseIndex = $index;
    }
}
