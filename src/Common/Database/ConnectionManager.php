<?php

/**
 * Database connection manager
 *
 * Manages named database connections with lazy initialization via registered
 * factory callbacks. This allows different connection types to have different
 * configurations and middleware while avoiding circular dependencies.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 Eric Stern
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Database;

use Closure;
use Doctrine\DBAL\Connection;
use InvalidArgumentException;

class ConnectionManager
{
    /** @var array<string, Closure(): Connection> */
    private array $factories = [];

    /** @var array<string, Connection> */
    private array $connections = [];

    /**
     * Register a factory for a connection type.
     *
     * @param Closure(): Connection $factory
     */
    public function register(ConnectionType $type, Closure $factory): void
    {
        $this->factories[$type->name] = $factory;
    }

    /**
     * Get a connection by type. Creates it lazily on first access.
     */
    public function get(ConnectionType $type): Connection
    {
        if (isset($this->connections[$type->name])) {
            return $this->connections[$type->name];
        }

        if (!isset($this->factories[$type->name])) {
            throw new InvalidArgumentException(
                sprintf('No factory registered for connection type "%s"', $type->name)
            );
        }

        return $this->connections[$type->name] = $this->factories[$type->name]();
    }
}
