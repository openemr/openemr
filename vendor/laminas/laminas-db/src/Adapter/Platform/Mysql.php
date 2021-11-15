<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Adapter\Platform;

use Laminas\Db\Adapter\Driver\DriverInterface;
use Laminas\Db\Adapter\Driver\Mysqli;
use Laminas\Db\Adapter\Driver\Pdo;
use Laminas\Db\Adapter\Exception;

class Mysql extends AbstractPlatform
{
    /**
     * {@inheritDoc}
     */
    protected $quoteIdentifier = ['`', '`'];

    /**
     * {@inheritDoc}
     */
    protected $quoteIdentifierTo = '``';

    /**
     * @var \mysqli|\PDO|Pdo\Pdo|Mysqli\Mysqli
     */
    protected $driver = null;

    /**
     * NOTE: Include dashes for MySQL only, need tests for others platforms
     *
     * @var string
     */
    protected $quoteIdentifierFragmentPattern = '/([^0-9,a-z,A-Z$_\-:])/i';

    /**
     * @param null|\Laminas\Db\Adapter\Driver\Mysqli\Mysqli|\Laminas\Db\Adapter\Driver\Pdo\Pdo|\mysqli|\PDO $driver
     */
    public function __construct($driver = null)
    {
        if ($driver) {
            $this->setDriver($driver);
        }
    }

    /**
     * @param \Laminas\Db\Adapter\Driver\Mysqli\Mysqli|\Laminas\Db\Adapter\Driver\Pdo\Pdo|\mysqli|\PDO $driver
     * @return self Provides a fluent interface
     * @throws \Laminas\Db\Adapter\Exception\InvalidArgumentException
     */
    public function setDriver($driver)
    {
        // handle Laminas\Db drivers
        if ($driver instanceof Mysqli\Mysqli
            || ($driver instanceof Pdo\Pdo && $driver->getDatabasePlatformName() == 'Mysql')
            || ($driver instanceof \mysqli)
            || ($driver instanceof \PDO && $driver->getAttribute(\PDO::ATTR_DRIVER_NAME) == 'mysql')
        ) {
            $this->driver = $driver;
            return $this;
        }

        throw new Exception\InvalidArgumentException(
            '$driver must be a Mysqli or Mysql PDO Laminas\Db\Adapter\Driver, Mysqli instance or MySQL PDO instance'
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'MySQL';
    }

    /**
     * {@inheritDoc}
     */
    public function quoteIdentifierChain($identifierChain)
    {
        return '`' . implode('`.`', (array) str_replace('`', '``', $identifierChain)) . '`';
    }

    /**
     * {@inheritDoc}
     */
    public function quoteValue($value)
    {
        $quotedViaDriverValue = $this->quoteViaDriver($value);

        return $quotedViaDriverValue !== null ? $quotedViaDriverValue : parent::quoteValue($value);
    }

    /**
     * {@inheritDoc}
     */
    public function quoteTrustedValue($value)
    {
        $quotedViaDriverValue = $this->quoteViaDriver($value);

        return $quotedViaDriverValue !== null ? $quotedViaDriverValue : parent::quoteTrustedValue($value);
    }

    /**
     * @param  string $value
     * @return string|null
     */
    protected function quoteViaDriver($value)
    {
        if ($this->driver instanceof DriverInterface) {
            $resource = $this->driver->getConnection()->getResource();
        } else {
            $resource = $this->driver;
        }

        if ($resource instanceof \mysqli) {
            return '\'' . $resource->real_escape_string($value) . '\'';
        }
        if ($resource instanceof \PDO) {
            return $resource->quote($value);
        }

        return null;
    }
}
