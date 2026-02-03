<?php

/**
 * @package   openemr
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\BC;

use Doctrine\DBAL\Connection;
use SensitiveParameter;

/**
 * Represents database connection options for Doctrine DBAL.
 *
 * This class provides a type-safe way to configure database connections while
 * ensuring that sensitive values (password) are protected from leaking in
 * stack traces, var_dump(), and error logs.
 *
 * @phpstan-type DriverOptions array<int, mixed>
 * @phpstan-type DbalParams array{
 *   driver: 'pdo_mysql'|'mysqli',
 *   dbname: string,
 *   host?: string,
 *   port?: int,
 *   unix_socket?: string,
 *   user: string,
 *   password: string,
 *   charset?: string,
 *   driverOptions?: DriverOptions,
 *   serverVersion?: string,
 *   wrapperClass?: class-string<Connection>,
 * }
 */
final readonly class DatabaseConnectionOptions
{
    private const REDACTED = '[REDACTED]';

    /**
     * @param 'pdo_mysql'|'mysqli' $driver The database driver
     * @param string $dbname Database name (required)
     * @param string $user Username for authentication (required)
     * @param string $password Password for authentication
     * @param string|null $host Hostname (required if unixSocket not provided)
     * @param int|null $port Port number (default: 3306 for MySQL)
     * @param string|null $unixSocket Unix socket path (alternative to host/port)
     * @param string|null $charset Connection charset (e.g., 'utf8mb4')
     * @param DriverOptions $driverOptions PDO driver options (e.g., SSL settings)
     * @param string|null $serverVersion Server version hint for platform detection
     * @param class-string<Connection>|null $wrapperClass Custom connection wrapper class
     */
    public function __construct(
        public string $driver,
        public string $dbname,
        public string $user,
        #[SensitiveParameter]
        private string $password,
        public ?string $host = null,
        public ?int $port = null,
        public ?string $unixSocket = null,
        public ?string $charset = null,
        public array $driverOptions = [],
        public ?string $serverVersion = null,
        public ?string $wrapperClass = null,
    ) {
    }

    /**
     * Converts the options to the array format expected by Doctrine DBAL's
     * DriverManager::getConnection().
     *
     * @return DbalParams
     */
    public function toDbalParams(): array
    {
        $params = [
            'driver' => $this->driver,
            'dbname' => $this->dbname,
            'user' => $this->user,
            'password' => $this->password,
        ];

        if ($this->host !== null) {
            $params['host'] = $this->host;
        }

        if ($this->port !== null) {
            $params['port'] = $this->port;
        }

        if ($this->unixSocket !== null) {
            $params['unix_socket'] = $this->unixSocket;
        }

        if ($this->charset !== null) {
            $params['charset'] = $this->charset;
        }

        if ($this->driverOptions !== []) {
            $params['driverOptions'] = $this->driverOptions;
        }

        if ($this->serverVersion !== null) {
            $params['serverVersion'] = $this->serverVersion;
        }

        if ($this->wrapperClass !== null) {
            $params['wrapperClass'] = $this->wrapperClass;
        }

        return $params;
    }

    /**
     * Controls what is shown when var_dump() or print_r() is called.
     * Sensitive values are redacted.
     *
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        return [
            'driver' => $this->driver,
            'dbname' => $this->dbname,
            'user' => $this->user,
            'password' => self::REDACTED,
            'host' => $this->host,
            'port' => $this->port,
            'unixSocket' => $this->unixSocket,
            'charset' => $this->charset,
            'driverOptions' => $this->driverOptions,
            'serverVersion' => $this->serverVersion,
            'wrapperClass' => $this->wrapperClass,
        ];
    }
}
