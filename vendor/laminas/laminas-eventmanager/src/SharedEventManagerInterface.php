<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\EventManager;

/**
 * Interface for shared event listener collections
 */
interface SharedEventManagerInterface
{
    /**
     * Attach a listener to an event emitted by components with specific identifiers.
     *
     * @param  string $identifier Identifier for event emitting component
     * @param  string $eventName
     * @param  callable $listener Listener that will handle the event.
     * @param  int $priority Priority at which listener should execute
     */
    public function attach($identifier, $eventName, callable $listener, $priority = 1);

    /**
     * Detach a shared listener.
     *
     * Allows detaching a listener from one or more events to which it may be
     * attached.
     *
     * @param  callable $listener Listener to detach.
     * @param  null|string $identifier Identifier from which to detach; null indicates
     *      all registered identifiers.
     * @param  null|string $eventName Event from which to detach; null indicates
     *      all registered events.
     * @throws Exception\InvalidArgumentException for invalid identifier arguments.
     * @throws Exception\InvalidArgumentException for invalid event arguments.
     */
    public function detach(callable $listener, $identifier = null, $eventName = null);

    /**
     * Retrieve all listeners for given identifiers
     *
     * @param  array $identifiers
     * @param  string $eventName
     * @return array
     */
    public function getListeners(array $identifiers, $eventName);

    /**
     * Clear all listeners for a given identifier, optionally for a specific event
     *
     * @param  string $identifier
     * @param  null|string $eventName
     */
    public function clearListeners($identifier, $eventName = null);
}
