<?php

/**
 * @see       https://github.com/laminas/laminas-server for the canonical source repository
 * @copyright https://github.com/laminas/laminas-server/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-server/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Server\Reflection;

/**
 * Parameter Reflection
 *
 * Decorates a ReflectionParameter to allow setting the parameter type
 */
class ReflectionParameter
{
    /**
     * @var \ReflectionParameter
     */
    protected $reflection;

    /**
     * Parameter position
     * @var int
     */
    protected $position;

    /**
     * Parameter type
     * @var string
     */
    protected $type;

    /**
     * Parameter description
     * @var string
     */
    protected $description;

    /**
     * Parameter name (needed for serialization)
     * @var string
     */
    protected $name;

    /**
     * Declaring function name (needed for serialization)
     * @var string
     */
    protected $functionName;

    /**
     * Constructor
     *
     * @param \ReflectionParameter $r
     * @param string $type Parameter type
     * @param string $description Parameter description
     */
    public function __construct(\ReflectionParameter $r, $type = 'mixed', $description = '')
    {
        $this->reflection = $r;

        // Store parameters needed for (un)serialization
        $this->name = $r->getName();
        $this->functionName = $r->getDeclaringClass()
            ? [$r->getDeclaringClass()->getName(), $r->getDeclaringFunction()->getName()]
            : $r->getDeclaringFunction()->getName();

        $this->setType($type);
        $this->setDescription($description);
    }

    /**
     * Proxy reflection calls
     *
     * @param string $method
     * @param array $args
     * @throws Exception\BadMethodCallException
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (method_exists($this->reflection, $method)) {
            return call_user_func_array([$this->reflection, $method], $args);
        }

        throw new Exception\BadMethodCallException('Invalid reflection method');
    }

    /**
     * Retrieve parameter type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set parameter type
     *
     * @param string|null $type
     * @throws Exception\InvalidArgumentException
     * @return void
     */
    public function setType($type)
    {
        if (! is_string($type) && (null !== $type)) {
            throw new Exception\InvalidArgumentException('Invalid parameter type');
        }

        $this->type = $type;
    }

    /**
     * Retrieve parameter description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set parameter description
     *
     * @param string|null $description
     * @throws Exception\InvalidArgumentException
     * @return void
     */
    public function setDescription($description)
    {
        if (! is_string($description) && (null !== $description)) {
            throw new Exception\InvalidArgumentException('Invalid parameter description');
        }

        $this->description = $description;
    }

    /**
     * Set parameter position
     *
     * @param int $index
     * @return void
     */
    public function setPosition($index)
    {
        $this->position = $index;
    }

    /**
     * Return parameter position
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return string[]
     */
    public function __sleep()
    {
        return ['position', 'type', 'description', 'name', 'functionName'];
    }

    public function __wakeup()
    {
        $this->reflection = new \ReflectionParameter($this->functionName, $this->name);
    }
}
