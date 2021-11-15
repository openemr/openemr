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

class SqlServer extends AbstractPlatform
{
    /**
     * {@inheritDoc}
     */
    protected $quoteIdentifier = ['[',']'];

    /**
     * {@inheritDoc}
     */
    protected $quoteIdentifierTo = '\\';

    /**
     * @var resource|\PDO
     */
    protected $resource = null;

    /**
     * @param null|\Laminas\Db\Adapter\Driver\Sqlsrv\Sqlsrv|\Laminas\Db\Adapter\Driver\Pdo\Pdo|resource|\PDO $driver
     */
    public function __construct($driver = null)
    {
        if ($driver) {
            $this->setDriver($driver);
        }
    }

    /**
     * @param \Laminas\Db\Adapter\Driver\Sqlsrv\Sqlsrv|\Laminas\Db\Adapter\Driver\Pdo\Pdo|resource|\PDO $driver
     * @return self Provides a fluent interface
     * @throws \Laminas\Db\Adapter\Exception\InvalidArgumentException
     */
    public function setDriver($driver)
    {
        // handle Laminas\Db drivers
        if (($driver instanceof Pdo\Pdo && in_array($driver->getDatabasePlatformName(), ['SqlServer', 'Dblib']))
            || ($driver instanceof \PDO && in_array($driver->getAttribute(\PDO::ATTR_DRIVER_NAME), ['sqlsrv', 'dblib']))
        ) {
            $this->resource = $driver;
            return $this;
        }

        throw new Exception\InvalidArgumentException(
            '$driver must be a Sqlsrv PDO Laminas\Db\Adapter\Driver or Sqlsrv PDO instance'
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'SQLServer';
    }

    /**
     * {@inheritDoc}
     */
    public function getQuoteIdentifierSymbol()
    {
        return $this->quoteIdentifier;
    }

    /**
     * {@inheritDoc}
     */
    public function quoteIdentifierChain($identifierChain)
    {
        return '[' . implode('].[', (array) $identifierChain) . ']';
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
        trigger_error(
            'Attempting to quote a value in ' . __CLASS__ . ' without extension/driver support '
                . 'can introduce security vulnerabilities in a production environment.'
        );

        return '\'' . str_replace('\'', '\'\'', addcslashes($value, "\000\032")) . '\'';
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

        return '\'' . str_replace('\'', '\'\'', $value) . '\'';
    }
}
