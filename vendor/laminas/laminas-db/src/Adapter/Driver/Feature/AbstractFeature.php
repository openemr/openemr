<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Adapter\Driver\Feature;

use Laminas\Db\Adapter\Driver\DriverInterface;

abstract class AbstractFeature
{
    /**
     * @var DriverInterface
     */
    protected $driver = null;

    /**
     * Set driver
     *
     * @param DriverInterface $driver
     * @return void
     */
    public function setDriver(DriverInterface $driver)
    {
        $this->driver = $driver;
    }

    /**
     * Get name
     *
     * @return string
     */
    abstract public function getName();
}
