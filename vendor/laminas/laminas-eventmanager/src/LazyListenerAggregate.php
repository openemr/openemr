<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\EventManager;

use Interop\Container\ContainerInterface;

use function get_class;
use function gettype;
use function is_array;
use function is_object;
use function sprintf;

/**
 * Aggregate listener for attaching lazy listeners.
 *
 * Lazy listeners are listeners where creation is deferred until they are
 * triggered; this removes the most costly mechanism of pulling a listener
 * from a container unless the listener is actually invoked.
 *
 * Usage is:
 *
 * <code>
 * $aggregate = new LazyListenerAggregate(
 *     $lazyEventListenersOrDefinitions,
 *     $container
 * );
 * $aggregate->attach($events);
 * </code>
 */
class LazyListenerAggregate implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    /**
     * @var ContainerInterface Container from which to pull lazy listeners.
     */
    private $container;

    /**
     * @var array Additional environment/option variables to use when creating listener.
     */
    private $env;

    /**
     * Generated LazyEventListener instances.
     *
     * @var LazyEventListener[]
     */
    private $lazyListeners = [];

    /**
     * Constructor
     *
     * Accepts the composed $listeners, as well as the $container and $env in
     * order to create a listener aggregate that defers listener creation until
     * the listener is triggered.
     *
     * Listeners may be either LazyEventListener instances, or lazy event
     * listener definitions that can be provided to a LazyEventListener
     * constructor in order to create a new instance; in the latter case, the
     * $container and $env will be passed at instantiation as well.
     *
     * @var array $listeners LazyEventListener instances or array definitions
     *     to pass to the LazyEventListener constructor.
     * @var ContainerInterface $container
     * @var array $env
     * @throws Exception\InvalidArgumentException for invalid listener items.
     */
    public function __construct(array $listeners, ContainerInterface $container, array $env = [])
    {
        $this->container = $container;
        $this->env       = $env;

        // This would raise an exception for invalid structs
        foreach ($listeners as $listener) {
            if (is_array($listener)) {
                $listener = new LazyEventListener($listener, $container, $env);
            }

            if (! $listener instanceof LazyEventListener) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'All listeners must be LazyEventListener instances or definitions; received %s',
                    (is_object($listener) ? get_class($listener) : gettype($listener))
                ));
            }

            $this->lazyListeners[] = $listener;
        }
    }

    /**
     * Attach the aggregate to the event manager.
     *
     * Loops through all composed lazy listeners, and attaches them to the
     * event manager.
     *
     * @var EventManagerInterface $events
     * @var int $priority
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        foreach ($this->lazyListeners as $lazyListener) {
            $this->listeners[] = $events->attach(
                $lazyListener->getEvent(),
                $lazyListener,
                $lazyListener->getPriority($priority)
            );
        }
    }
}
