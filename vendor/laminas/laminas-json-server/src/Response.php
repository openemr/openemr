<?php

/**
 * @see       https://github.com/laminas/laminas-json-server for the canonical source repository
 * @copyright https://github.com/laminas/laminas-json-server/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-json-server/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Json\Server;

use Laminas\Json\Exception\RuntimeException;
use Laminas\Json\Json;

class Response
{
    /**
     * Response error
     *
     * @var null|Error
     */
    protected $error;

    /**
     * Request ID
     *
     * @var string
     */
    protected $id;

    /**
     * Result
     *
     * @var mixed
     */
    protected $result;

    /**
     * Service map
     *
     * @var Smd
     */
    protected $serviceMap;

    /**
     * JSON-RPC version
     *
     * @var null|string
     */
    protected $version;

    /**
     * @var mixed
     */
    protected $args;

    /**
     * Set response state.
     *
     * @param  array $options
     * @return self
     */
    public function setOptions(array $options)
    {
        // re-produce error state
        if (isset($options['error']) && is_array($options['error'])) {
            $error = $options['error'];
            $errorData = isset($error['data']) ? $error['data'] : null;
            $options['error'] = new Error($error['message'], $error['code'], $errorData);
        }

        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
                continue;
            }

            if ('jsonrpc' === $key) {
                $this->setVersion($value);
                continue;
            }
        }
        return $this;
    }

    /**
     * Set response state based on JSON.
     *
     * @param  string $json
     * @return void
     * @throws Exception\RuntimeException
     */
    public function loadJson($json)
    {
        try {
            $options = Json::decode($json, Json::TYPE_ARRAY);
        } catch (RuntimeException $e) {
            throw new Exception\RuntimeException(
                'json is not a valid response; array expected',
                $e->getCode(),
                $e
            );
        }

        if (! is_array($options)) {
            throw new Exception\RuntimeException('json is not a valid response; array expected');
        }

        $this->setOptions($options);
    }

    /**
     * Set result.
     *
     * @param  mixed $value
     * @return self
     */
    public function setResult($value)
    {
        $this->result = $value;
        return $this;
    }

    /**
     * Get result.
     *
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Set result error
     *
     * RPC error, if response results in fault.
     *
     * @param  mixed $error
     * @return self
     */
    public function setError(Error $error = null)
    {
        $this->error = $error;
        return $this;
    }

    /**
     * Get response error
     *
     * @return null|Error
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Is the response an error?
     *
     * @return bool
     */
    public function isError()
    {
        return $this->getError() instanceof Error;
    }

    /**
     * Set request ID
     *
     * @param  mixed $name
     * @return self
     */
    public function setId($name)
    {
        $this->id = $name;
        return $this;
    }

    /**
     * Get request ID.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set JSON-RPC version.
     *
     * @param  string $version
     * @return self
     */
    public function setVersion($version)
    {
        $version = (string) $version;
        if ('2.0' == $version) {
            $this->version = '2.0';
            return $this;
        }

        $this->version = null;
        return $this;
    }

    /**
     * Retrieve JSON-RPC version
     *
     * @return null|string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Cast to JSON
     *
     * @return string
     */
    public function toJson()
    {
        $response = ['id' => $this->getId()];

        if ($this->isError()) {
            $response['error'] = $this->getError()->toArray();
        } else {
            $response['result'] = $this->getResult();
        }

        if (null !== ($version = $this->getVersion())) {
            $response['jsonrpc'] = $version;
        }

        return Json::encode($response);
    }

    /**
     * Retrieve args.
     *
     * @return mixed
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * Set args.
     *
     * @param mixed $args
     * @return self
     */
    public function setArgs($args)
    {
        $this->args = $args;
        return $this;
    }

    /**
     * Set service map object.
     *
     * @param  Smd $serviceMap
     * @return self
     */
    public function setServiceMap($serviceMap)
    {
        $this->serviceMap = $serviceMap;
        return $this;
    }

    /**
     * Retrieve service map.
     *
     * @return Smd|null
     */
    public function getServiceMap()
    {
        return $this->serviceMap;
    }

    /**
     * Cast to string (JSON).
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }
}
