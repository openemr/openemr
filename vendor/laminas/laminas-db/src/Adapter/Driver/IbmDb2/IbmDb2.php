<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Adapter\Driver\IbmDb2;

use Laminas\Db\Adapter\Driver\DriverInterface;
use Laminas\Db\Adapter\Exception;
use Laminas\Db\Adapter\Profiler;

class IbmDb2 implements DriverInterface, Profiler\ProfilerAwareInterface
{
    /**
     * @var Connection
     */
    protected $connection;

    /** @var Statement */
    protected $statementPrototype;

    /** @var Result */
    protected $resultPrototype;

    /**
     * @var Profiler\ProfilerInterface
     */
    protected $profiler;

    /**
     * @param array|Connection|resource $connection
     * @param null|Statement            $statementPrototype
     * @param null|Result               $resultPrototype
     */
    public function __construct($connection, Statement $statementPrototype = null, Result $resultPrototype = null)
    {
        if (! $connection instanceof Connection) {
            $connection = new Connection($connection);
        }

        $this->registerConnection($connection);
        $this->registerStatementPrototype(($statementPrototype) ?: new Statement());
        $this->registerResultPrototype(($resultPrototype) ?: new Result());
    }

    /**
     * @param Profiler\ProfilerInterface $profiler
     * @return self Provides a fluent interface
     */
    public function setProfiler(Profiler\ProfilerInterface $profiler)
    {
        $this->profiler = $profiler;
        if ($this->connection instanceof Profiler\ProfilerAwareInterface) {
            $this->connection->setProfiler($profiler);
        }
        if ($this->statementPrototype instanceof Profiler\ProfilerAwareInterface) {
            $this->statementPrototype->setProfiler($profiler);
        }
        return $this;
    }

    /**
     * @return null|Profiler\ProfilerInterface
     */
    public function getProfiler()
    {
        return $this->profiler;
    }

    /**
     * @param  Connection $connection
     * @return self Provides a fluent interface
     */
    public function registerConnection(Connection $connection)
    {
        $this->connection = $connection;
        $this->connection->setDriver($this);
        return $this;
    }

    /**
     * @param  Statement $statementPrototype
     * @return self Provides a fluent interface
     */
    public function registerStatementPrototype(Statement $statementPrototype)
    {
        $this->statementPrototype = $statementPrototype;
        $this->statementPrototype->setDriver($this);
        return $this;
    }

    /**
     * @param  Result $resultPrototype
     * @return self Provides a fluent interface
     */
    public function registerResultPrototype(Result $resultPrototype)
    {
        $this->resultPrototype = $resultPrototype;
        return $this;
    }

    /**
     * Get database platform name
     *
     * @param string $nameFormat
     * @return string
     */
    public function getDatabasePlatformName($nameFormat = self::NAME_FORMAT_CAMELCASE)
    {
        if ($nameFormat == self::NAME_FORMAT_CAMELCASE) {
            return 'IbmDb2';
        } else {
            return 'IBM DB2';
        }
    }

    /**
     * Check environment
     *
     * @return bool
     */
    public function checkEnvironment()
    {
        if (! extension_loaded('ibm_db2')) {
            throw new Exception\RuntimeException('The ibm_db2 extension is required by this driver.');
        }
    }

    /**
     * Get connection
     *
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Create statement
     *
     * @param string|resource $sqlOrResource
     * @return Statement
     */
    public function createStatement($sqlOrResource = null)
    {
        $statement = clone $this->statementPrototype;
        if (is_resource($sqlOrResource) && get_resource_type($sqlOrResource) == 'DB2 Statement') {
            $statement->setResource($sqlOrResource);
        } else {
            if (is_string($sqlOrResource)) {
                $statement->setSql($sqlOrResource);
            } elseif ($sqlOrResource !== null) {
                throw new Exception\InvalidArgumentException(
                    __FUNCTION__ . ' only accepts an SQL string or an ibm_db2 resource'
                );
            }
            if (! $this->connection->isConnected()) {
                $this->connection->connect();
            }
            $statement->initialize($this->connection->getResource());
        }
        return $statement;
    }

    /**
     * Create result
     *
     * @param resource $resource
     * @return Result
     */
    public function createResult($resource)
    {
        $result = clone $this->resultPrototype;
        $result->initialize($resource, $this->connection->getLastGeneratedValue());
        return $result;
    }

    /**
     * @return Result
     */
    public function getResultPrototype()
    {
        return $this->resultPrototype;
    }

    /**
     * Get prepare type
     *
     * @return string
     */
    public function getPrepareType()
    {
        return self::PARAMETERIZATION_POSITIONAL;
    }

    /**
     * Format parameter name
     *
     * @param string $name
     * @param mixed  $type
     * @return string
     */
    public function formatParameterName($name, $type = null)
    {
        return '?';
    }

    /**
     * Get last generated value
     *
     * @return mixed
     */
    public function getLastGeneratedValue()
    {
        return $this->connection->getLastGeneratedValue();
    }
}
