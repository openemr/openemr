<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Stdlib\Hydrator\Aggregate;

use Zend\Hydrator\Aggregate\AggregateHydrator as BaseAggregateHydrator;
use Zend\Stdlib\Hydrator\HydratorInterface;

/**
 * Aggregate hydrator that composes multiple hydrators via events
 *
 * @deprecated Use Zend\Hydrator\Aggregate\AggregateHydrator from zendframework/zend-hydrator instead.
 */
class AggregateHydrator extends BaseAggregateHydrator implements HydratorInterface
{
    /**
     * {@inheritDoc}
     */
    public function extract($object)
    {
        $event = new ExtractEvent($this, $object);

        $this->getEventManager()->triggerEvent($event);

        return $event->getExtractedData();
    }

    /**
     * {@inheritDoc}
     */
    public function hydrate(array $data, $object)
    {
        $event = new HydrateEvent($this, $object, $data);

        $this->getEventManager()->triggerEvent($event);

        return $event->getHydratedObject();
    }
}
