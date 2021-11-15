<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\View\Helper;

use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\Helper\Placeholder\Container;

/**
 * Helper for passing data between otherwise segregated Views. It's called
 * Placeholder to make its typical usage obvious, but can be used just as easily
 * for non-Placeholder things. That said, the support for this is only
 * guaranteed to effect subsequently rendered templates, and of course Layouts.
 */
class Placeholder extends AbstractHelper
{
    /**
     * Placeholder items
     *
     * @var Container\AbstractContainer[]
     */
    protected $items = [];

    /**
     * Default container class
     * @var string
     */
    protected $containerClass = 'Laminas\View\Helper\Placeholder\Container';

    /**
     * Placeholder helper
     *
     * @param  string $name
     * @throws InvalidArgumentException
     * @return Placeholder\Container\AbstractContainer
     */
    public function __invoke($name = null)
    {
        if ($name === null) {
            throw new InvalidArgumentException(
                'Placeholder: missing argument. $name is required by placeholder($name)'
            );
        }

        $name = (string) $name;
        return $this->getContainer($name);
    }

    /**
     * createContainer
     *
     * @param  string $key
     * @param  array $value
     * @return Container\AbstractContainer
     */
    public function createContainer($key, array $value = [])
    {
        $key = (string) $key;

        $this->items[$key] = new $this->containerClass($value);
        return $this->items[$key];
    }

    /**
     * Retrieve a placeholder container
     *
     * @param  string $key
     * @return Container\AbstractContainer
     */
    public function getContainer($key)
    {
        $key = (string) $key;
        if (isset($this->items[$key])) {
            return $this->items[$key];
        }

        $container = $this->createContainer($key);

        return $container;
    }

    /**
     * Does a particular container exist?
     *
     * @param  string $key
     * @return bool
     */
    public function containerExists($key)
    {
        $key = (string) $key;
        $return = array_key_exists($key, $this->items);
        return $return;
    }

    /**
     * Delete a specific container by name
     *
     * @param  string $key
     * @return void
     */
    public function deleteContainer($key)
    {
        $key = (string) $key;
        unset($this->items[$key]);
    }

    /**
     * Remove all containers
     *
     * @return void
     */
    public function clearContainers()
    {
        $this->items = [];
    }
}
