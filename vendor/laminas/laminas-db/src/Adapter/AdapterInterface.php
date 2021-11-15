<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Adapter;

/**
 *
 * @property Driver\DriverInterface $driver
 * @property Platform\PlatformInterface $platform
 */
interface AdapterInterface
{
    /**
     * @return Driver\DriverInterface
     */
    public function getDriver();

    /**
     * @return Platform\PlatformInterface
     */
    public function getPlatform();
}
