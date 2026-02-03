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

use Doctrine\DBAL\DriverManager;
use SensitiveParameter;

/**
 * Represents database connection options for Doctrine DBAL.
 *
 * This class provides a type-safe way to configure database connections while
 * ensuring that sensitive values (password) are protected from leaking in
 * stack traces, var_dump(), and error logs.
 *
 * @phpstan-import-type Params from DriverManager
 */
final readonly class DatabaseConnectionOptions
{
    private const REDACTED = '[REDACTED]';

    /**
     * @param array<int, mixed> $driverOptions PDO driver options (e.g., SSL certs)
     */
    public function __construct(
        public string $dbname,
        public string $user,
        #[SensitiveParameter]
        private string $password,
        public ?string $host = null,
        public ?int $port = null,
        public ?string $unixSocket = null,
        public ?string $charset = null,
        public array $driverOptions = [],
    ) {
    }

    /**
     * Converts the options to the array format expected by Doctrine DBAL's
     * DriverManager::getConnection().
     *
     * @return Params
     */
    public function toDbalParams(): array
    {
        $params = [
            'driver' => 'pdo_mysql',
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
            'dbname' => $this->dbname,
            'user' => $this->user,
            'password' => self::REDACTED,
            'host' => $this->host,
            'port' => $this->port,
            'unixSocket' => $this->unixSocket,
            'charset' => $this->charset,
            'driverOptions' => $this->driverOptions,
        ];
    }
}
