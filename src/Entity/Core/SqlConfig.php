<?php

/**
 * Standard SqlConfig entity for database credentials.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2022-2023 Robert Down <robertdown@live.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Entity\Core;

use OpenEMR\Events\Core\Interfaces\SqlConfigInterface;

class SqlConfig implements SqlConfigInterface
{
    /**
     * {@inheritDoc}
     */
    private $host;

    /**
     * {@inheritDoc}
     */
    private $port;

    /**
     * {@inheritDoc}
     */
    private $user;

    /**
     * {@inheritDoc}
     */
    private $pass;

    /**
     * {@inheritDoc}
     */
    private $databaseName;

    /**
     * {@inheritDoc}
     */
    private $encoding;

    /**
     * {@inheritDoc}
     */
    private $config;

    /**
     * {@inheritDoc}
     */
    private $disableUTF8;

    /**
     * Constructor can accept an associative array of key/value pairs to help auto-populate object properties. Key name
     * must match property name.
     *
     * @param array $opts
     */
    public function __construct(array $opts)
    {
        if (array_key_exists('host', $opts)) {
            $this->host = $opts['host'];
        }

        if (array_key_exists('port', $opts)) {
            $this->port = $opts['port'];
        }

        if (array_key_exists('user', $opts)) {
            $this->user = $opts['user'];
        }

        if (array_key_exists('pass', $opts)) {
            $this->pass = $opts['pass'];
        }

        if (array_key_exists('databaseName', $opts)) {
            $this->databaseName = $opts['databaseName'];
        }

        if (array_key_exists('encoding', $opts)) {
            $this->encoding = $opts['encoding'];
        }

        if (array_key_exists('config', $opts)) {
            $this->config = $opts['config'];
        }

        if (array_key_exists('disableUTF8', $opts)) {
            $this->disableUTF8 = $opts['disableUTF8'];
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * {@inheritDoc}
     */
    public function getPort(): string
    {
        return $this->port;
    }

    /**
     * {@inheritDoc}
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * {@inheritDoc}
     */
    public function getPass(): string
    {
        return $this->pass;
    }

    /**
     * {@inheritDoc}
     */
    public function getDatabaseName(): string
    {
        return $this->databaseName;
    }

    /**
     * {@inheritDoc}
     */
    public function getEncoding(): string
    {
        return $this->encoding;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig(): int
    {
        return $this->config;
    }

    /**
     * {@inheritDoc}
     */
    public function getDisableUTF8(): int
    {
        return $this->disableUTF8;
    }
}
