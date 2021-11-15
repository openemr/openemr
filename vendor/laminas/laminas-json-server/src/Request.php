<?php

/**
 * @see       https://github.com/laminas/laminas-json-server for the canonical source repository
 * @copyright https://github.com/laminas/laminas-json-server/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-json-server/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Json\Server;

use Exception;
use Laminas\Json\Json;

/**
 * @todo Revised method regex to allow NS; however, should SMD be revised to
 *     strip PHP NS instead when attaching functions?
 */
class Request
{
    /**
     * Request ID
     *
     * @var mixed
     */
    protected $id;

    /**
     * Flag indicating if method represents an error.
     *
     * @var bool
     */
    protected $isMethodError = false;

    /**
     * Flag indicating a parse error.
     *
     * @var bool
     */
    protected $isParseError = false;

    /**
     * Requested method.
     *
     * @var string
     */
    protected $method;

    /**
     * Regex for method.
     *
     * @var string
     */
    protected $methodRegex = '/^[a-z][a-z0-9\\\\_.]*$/i';

    /**
     * Request parameters.
     *
     * @var array
     */
    protected $params = [];

    /**
     * JSON-RPC version of request.
     *
     * @var string
     */
    protected $version = '1.0';

    /**
     * Set request state.
     *
     * @param  array $options
     * @return self
     */
    public function setOptions(array $options)
    {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
                continue;
            }

            if ($key == 'jsonrpc') {
                $this->setVersion($value);
                continue;
            }
        }
        return $this;
    }

    /**
     * Add a parameter to the request.
     *
     * @param  mixed $value
     * @param  string $key
     * @return self
     */
    public function addParam($value, $key = null)
    {
        if ((null === $key) || ! is_string($key)) {
            $index = count($this->params);
            $this->params[$index] = $value;
            return $this;
        }

        $this->params[$key] = $value;
        return $this;
    }

    /**
     * Add many params.
     *
     * @param  array $params
     * @return self
     */
    public function addParams(array $params)
    {
        foreach ($params as $key => $value) {
            $this->addParam($value, $key);
        }
        return $this;
    }

    /**
     * Overwrite params.
     *
     * @param  array $params
     * @return \Laminas\Json\Server\Request
     */
    public function setParams(array $params)
    {
        $this->params = [];
        return $this->addParams($params);
    }

    /**
     * Retrieve param by index or key.
     *
     * @param  int|string $index
     * @return mixed|null Null when not found
     */
    public function getParam($index)
    {
        if (! array_key_exists($index, $this->params)) {
            return null;
        }

        return $this->params[$index];
    }

    /**
     * Retrieve parameters.
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set request method.
     *
     * @param  string $name
     * @return self
     */
    public function setMethod($name)
    {
        if (! preg_match($this->methodRegex, $name)) {
            $this->isMethodError = true;
            return $this;
        }

        $this->method = $name;
        return $this;
    }

    /**
     * Get request method name.
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Was a bad method provided?
     *
     * @return bool
     */
    public function isMethodError()
    {
        return $this->isMethodError;
    }

    /**
     * Was a malformed JSON provided?
     *
     * @return bool
     */
    public function isParseError()
    {
        return $this->isParseError;
    }

    /**
     * Set request identifier
     *
     * @param  mixed $name
     * @return self
     */
    public function setId($name)
    {
        $this->id = (string) $name;
        return $this;
    }

    /**
     * Retrieve request identifier.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set JSON-RPC version
     *
     * @param  string $version
     * @return self
     */
    public function setVersion($version)
    {
        if ('2.0' == $version) {
            $this->version = '2.0';
            return $this;
        }

        $this->version = '1.0';
        return $this;
    }

    /**
     * Retrieve JSON-RPC version.
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set request state based on JSON.
     *
     * @param  string $json
     * @return void
     */
    public function loadJson($json)
    {
        try {
            $options = Json::decode($json, Json::TYPE_ARRAY);
            $this->setOptions($options);
        } catch (Exception $e) {
            $this->isParseError = true;
        }
    }

    /**
     * Cast request to JSON.
     *
     * @return string
     */
    public function toJson()
    {
        $jsonArray = [
            'method' => $this->getMethod()
        ];

        if (null !== ($id = $this->getId())) {
            $jsonArray['id'] = $id;
        }

        $params = $this->getParams();
        if (! empty($params)) {
            $jsonArray['params'] = $params;
        }

        if ('2.0' == $this->getVersion()) {
            $jsonArray['jsonrpc'] = '2.0';
        }

        return Json::encode($jsonArray);
    }

    /**
     * Cast request to string (JSON)
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }
}
