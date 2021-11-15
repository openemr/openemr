<?php

/**
 * @see       https://github.com/laminas/laminas-hydrator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-hydrator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-hydrator/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Hydrator\Aggregate;

use Laminas\EventManager\Event;

/**
 * Event triggered when the {@see AggregateHydrator} hydrates
 * data into an object
 */
class HydrateEvent extends Event
{
    public const EVENT_HYDRATE = 'hydrate';

    /**
     * {@inheritDoc}
     */
    protected $name = self::EVENT_HYDRATE;

    /**
     * @var object
     */
    protected $hydratedObject;

    /**
     * @var mixed[] Data being used to hydrate the $hydratedObject
     */
    protected $hydrationData;

    /**
     * @param mixed[] $hydrationData Data being used to hydrate the $hydratedObject
     */
    public function __construct(object $target, object $hydratedObject, array $hydrationData)
    {
        parent::__construct();
        $this->target         = $target;
        $this->hydratedObject = $hydratedObject;
        $this->hydrationData  = $hydrationData;
    }

    /**
     * Retrieves the object that is being hydrated
     */
    public function getHydratedObject() : object
    {
        return $this->hydratedObject;
    }

    public function setHydratedObject(object $hydratedObject) : void
    {
        $this->hydratedObject = $hydratedObject;
    }

    /**
     * Retrieves the data that is being used for hydration
     *
     * @return mixed[]
     */
    public function getHydrationData() : array
    {
        return $this->hydrationData;
    }

    /**
     * @param mixed[] $hydrationData
     */
    public function setHydrationData(array $hydrationData) : void
    {
        $this->hydrationData = $hydrationData;
    }
}
