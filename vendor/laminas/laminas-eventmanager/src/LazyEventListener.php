<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\EventManager;

use Interop\Container\ContainerInterface;

use function is_string;

/**
 * Lazy listener instance for use with LazyListenerAggregate.
 *
 * Used as an internal class for the LazyAggregate to allow lazy creation of
 * listeners via a dependency injection container.
 *
 * Lazy event listener definitions add the following members to what the
 * LazyListener accepts:
 *
 * - event: the event name to attach to.
 * - priority: the priority at which to attach the listener, if not the default.
 */
class LazyEventListener extends LazyListener
{
    /**
     * @var string Event name to which to attach.
     */
    private $event;

    /**
     * @var null|int Priority at which to attach.
     */
    private $priority;

    /**
     * @param array $definition
     * @param ContainerInterface $container
     * @param array $env
     */
    public function __construct(array $definition, ContainerInterface $container, array $env = [])
    {
        parent::__construct($definition, $container, $env);

        if ((! isset($definition['event'])
            || ! is_string($definition['event'])
            || empty($definition['event']))
        ) {
            throw new Exception\InvalidArgumentException(
                'Lazy listener definition is missing a valid "event" member; cannot create LazyListener'
            );
        }

        $this->event     = $definition['event'];
        $this->priority  = isset($definition['priority']) ? (int) $definition['priority'] : null;
    }

    /**
     * @return string
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return int
     */
    public function getPriority($default = 1)
    {
        return (null !== $this->priority) ? $this->priority : $default;
    }
}
