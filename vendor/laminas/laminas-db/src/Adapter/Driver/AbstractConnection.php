<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Adapter\Driver;

use Laminas\Db\Adapter\Profiler\ProfilerAwareInterface;
use Laminas\Db\Adapter\Profiler\ProfilerInterface;

abstract class AbstractConnection implements ConnectionInterface, ProfilerAwareInterface
{
    /**
     * @var array
     */
    protected $connectionParameters = [];

    /**
     * @var string|null
     */
    protected $driverName;

    /**
     * @var boolean
     */
    protected $inTransaction = false;

    /**
     * Nested transactions count.
     *
     * @var integer
     */
    protected $nestedTransactionsCount = 0;

    /**
     * @var ProfilerInterface|null
     */
    protected $profiler;

    /**
     * @var resource|null
     */
    protected $resource;

    /**
     * {@inheritDoc}
     */
    public function disconnect()
    {
        if ($this->isConnected()) {
            $this->resource = null;
        }

        return $this;
    }

    /**
     * Get connection parameters
     *
     * @return array
     */
    public function getConnectionParameters()
    {
        return $this->connectionParameters;
    }

    /**
     * Get driver name
     *
     * @return null|string
     */
    public function getDriverName()
    {
        return $this->driverName;
    }

    /**
     * @return null|ProfilerInterface
     */
    public function getProfiler()
    {
        return $this->profiler;
    }

    /**
     * {@inheritDoc}
     *
     * @return resource
     */
    public function getResource()
    {
        if (! $this->isConnected()) {
            $this->connect();
        }

        return $this->resource;
    }

    /**
     * Checks whether the connection is in transaction state.
     *
     * @return boolean
     */
    public function inTransaction()
    {
        return $this->inTransaction;
    }

    /**
     * @param  array $connectionParameters
     * @return self Provides a fluent interface
     */
    public function setConnectionParameters(array $connectionParameters)
    {
        $this->connectionParameters = $connectionParameters;

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @return self Provides a fluent interface
     */
    public function setProfiler(ProfilerInterface $profiler)
    {
        $this->profiler = $profiler;

        return $this;
    }
}
