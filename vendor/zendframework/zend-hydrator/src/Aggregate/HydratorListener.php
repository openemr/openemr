<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Hydrator\Aggregate;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Hydrator\HydratorInterface;

/**
 * Aggregate listener wrapping around a hydrator.
 *
 * Listens to {@see HydrateEvent::EVENT_HYDRATE} and {@see ExtractEvent::EVENT_EXTRACT}
 */
class HydratorListener extends AbstractListenerAggregate
{
    /**
     * @var HydratorInterface
     */
    protected $hydrator;

    /**
     * @param HydratorInterface $hydrator
     */
    public function __construct(HydratorInterface $hydrator)
    {
        $this->hydrator = $hydrator;
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(HydrateEvent::EVENT_HYDRATE, [$this, 'onHydrate'], $priority);
        $this->listeners[] = $events->attach(ExtractEvent::EVENT_EXTRACT, [$this, 'onExtract'], $priority);
    }

    /**
     * Callback to be used when {@see HydrateEvent::EVENT_HYDRATE} is triggered
     *
     * @param HydrateEvent $event
     * @return object
     * @internal
     */
    public function onHydrate(HydrateEvent $event)
    {
        $object = $this->hydrator->hydrate($event->getHydrationData(), $event->getHydratedObject());
        $event->setHydratedObject($object);
        return $object;
    }

    /**
     * Callback to be used when {@see ExtractEvent::EVENT_EXTRACT} is triggered
     *
     * @param ExtractEvent $event
     * @return array
     * @internal
     */
    public function onExtract(ExtractEvent $event)
    {
        $data = $this->hydrator->extract($event->getExtractionObject());
        $event->mergeExtractedData($data);
        return $data;
    }
}
