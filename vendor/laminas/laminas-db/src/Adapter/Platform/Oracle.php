<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Adapter\Platform;

use \Laminas\Db\Adapter\Exception\InvalidArgumentException;
use Laminas\Db\Adapter\Driver\DriverInterface;
use Laminas\Db\Adapter\Driver\Oci8\Oci8;
use Laminas\Db\Adapter\Driver\Pdo\Pdo;

class Oracle extends AbstractPlatform
{
    /**
     * @var null|Pdo|Oci8|\PDO
     */
    protected $resource = null;

    /**
     * @param array $options
     * @param null|Oci8|Pdo $driver
     */
    public function __construct($options = [], $driver = null)
    {
        if (isset($options['quote_identifiers'])
            && ($options['quote_identifiers'] == false
            || $options['quote_identifiers'] === 'false')
        ) {
            $this->quoteIdentifiers = false;
        }

        if ($driver) {
            $this->setDriver($driver);
        }
    }

    /**
     * @param Pdo|Oci8 $driver
     * @return self Provides a fluent interface
     * @throws InvalidArgumentException
     */
    public function setDriver($driver)
    {
        if ($driver instanceof Oci8
            || ($driver instanceof Pdo && $driver->getDatabasePlatformName() == 'Oracle')
            || ($driver instanceof \oci8)
            || ($driver instanceof \PDO && $driver->getAttribute(\PDO::ATTR_DRIVER_NAME) == 'oci')
        ) {
            $this->resource = $driver;
            return $this;
        }

        throw new InvalidArgumentException(
            '$driver must be a Oci8 or Oracle PDO Laminas\Db\Adapter\Driver, '
            . 'Oci8 instance, or Oci PDO instance'
        );
    }

    /**
     * @return null|Pdo|Oci8
     */
    public function getDriver()
    {
        return $this->resource;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'Oracle';
    }

    /**
     * {@inheritDoc}
     */
    public function quoteIdentifierChain($identifierChain)
    {
        if ($this->quoteIdentifiers === false) {
            return implode('.', (array) $identifierChain);
        }

        return '"' . implode('"."', (array) str_replace('"', '\\"', $identifierChain)) . '"';
    }

    /**
     * {@inheritDoc}
     */
    public function quoteValue($value)
    {
        if ($this->resource instanceof DriverInterface) {
            $resource = $this->resource->getConnection()->getResource();
        } else {
            $resource = $this->resource;
        }

        if ($resource) {
            if ($resource instanceof \PDO) {
                return $resource->quote($value);
            }

            if (get_resource_type($resource) == 'oci8 connection'
                || get_resource_type($resource) == 'oci8 persistent connection'
            ) {
                return "'" . addcslashes(str_replace("'", "''", $value), "\x00\n\r\"\x1a") . "'";
            }
        }

        trigger_error(
            'Attempting to quote a value in ' . __CLASS__ . ' without extension/driver support '
            . 'can introduce security vulnerabilities in a production environment.'
        );

        return "'" . addcslashes(str_replace("'", "''", $value), "\x00\n\r\"\x1a") . "'";
    }

    /**
     * {@inheritDoc}
     */
    public function quoteTrustedValue($value)
    {
        return "'" . addcslashes(str_replace('\'', '\'\'', $value), "\x00\n\r\"\x1a") . "'";
    }
}
