<?php

namespace OpenEMR\Core;

use Symfony\Component\HttpFoundation\ParameterBag;
use Traversable;

class OEGlobalsBag extends ParameterBag implements \IteratorAggregate, \Countable
{
    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new OEGlobalsBag();
        }
        return self::$instance;
    }

    public function __construct(array $parameters = [], private readonly bool $compatabilityMode = false)
    {
        parent::__construct($parameters);
    }

    public function set(string $key, mixed $value): void
    {
        $this->parameters[$key] = $value;
        if ($this->compatabilityMode) {
            // In compatibility mode, also set the value in the global $_GLOBALS array
            $GLOBALS[$key] = $value;
        }
    }

    /**
     * Returns an iterator for parameters.
     *
     * @return \ArrayIterator<string, mixed>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->parameters);
    }

    /**
     * Returns the number of parameters.
     */
    public function count(): int
    {
        return \count($this->parameters);
    }
}
