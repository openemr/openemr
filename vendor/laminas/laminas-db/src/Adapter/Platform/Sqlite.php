<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Adapter\Platform;

use Laminas\Db\Adapter\Driver\DriverInterface;
use Laminas\Db\Adapter\Driver\Pdo;
use Laminas\Db\Adapter\Exception;

class Sqlite extends AbstractPlatform
{
    /**
     * {@inheritDoc}
     */
    protected $quoteIdentifier = ['"','"'];

    /**
     * {@inheritDoc}
     */
    protected $quoteIdentifierTo = '\'';

    /**
     * @var \PDO
     */
    protected $resource = null;

    /**
     * @param null|\Laminas\Db\Adapter\Driver\Pdo\Pdo||\PDO $driver
     */
    public function __construct($driver = null)
    {
        if ($driver) {
            $this->setDriver($driver);
        }
    }

    /**
     * @param \Laminas\Db\Adapter\Driver\Pdo\Pdo|\PDO $driver
     * @return self Provides a fluent interface
     * @throws \Laminas\Db\Adapter\Exception\InvalidArgumentException
     */
    public function setDriver($driver)
    {
        if (($driver instanceof \PDO && $driver->getAttribute(\PDO::ATTR_DRIVER_NAME) == 'sqlite')
            || ($driver instanceof Pdo\Pdo && $driver->getDatabasePlatformName() == 'Sqlite')
        ) {
            $this->resource = $driver;
            return $this;
        }

        throw new Exception\InvalidArgumentException(
            '$driver must be a Sqlite PDO Laminas\Db\Adapter\Driver, Sqlite PDO instance'
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'SQLite';
    }

    /**
     * {@inheritDoc}
     */
    public function quoteValue($value)
    {
        $resource = $this->resource;

        if ($resource instanceof DriverInterface) {
            $resource = $resource->getConnection()->getResource();
        }

        if ($resource instanceof \PDO) {
            return $resource->quote($value);
        }

        return parent::quoteValue($value);
    }

    /**
     * {@inheritDoc}
     */
    public function quoteTrustedValue($value)
    {
        $resource = $this->resource;

        if ($resource instanceof DriverInterface) {
            $resource = $resource->getConnection()->getResource();
        }

        if ($resource instanceof \PDO) {
            return $resource->quote($value);
        }

        return parent::quoteTrustedValue($value);
    }
}
