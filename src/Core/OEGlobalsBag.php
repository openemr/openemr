<?php

namespace OpenEMR\Core;

use Symfony\Component\HttpFoundation\ParameterBag;
use Traversable;

class OEGlobalsBag extends ParameterBag implements \IteratorAggregate, \Countable
{
    private static $instance = null;

    /**
     * holds the mutable flag; the property referencing this holder is readonly.
     */
    private readonly \stdClass $compatHolder;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new OEGlobalsBag();
        }
        return self::$instance;
    }

    /**
     * Enables or disables compatibility mode for OEGlobalsBag which will populate the $GLOBALS array for backwards
     * compatibility with legacy code.
     * @param bool $enabled
     * @return void
     */
    public static function setCompatabilityMode(bool $enabled): void
    {
        $instance = self::getInstance();
        $instance->compatHolder->enabled = $enabled;
    }

    public function __construct(array $parameters = [], private readonly bool $compatabilityMode = false)
    {
        parent::__construct($parameters);
        $this->compatHolder = (object) ['enabled' => $this->compatabilityMode];
    }

    public function set(string $key, mixed $value): void
    {
        $this->parameters[$key] = $value;
        if ($this->compatHolder->enabled) {
            // In compatibility mode, also set the value in the global $GLOBALS array
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
