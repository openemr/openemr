<?php

/**
 * Database Credentials Value Object
 *
 * Immutable value object representing database connection credentials.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Admin\ValueObjects;

use OpenEMR\Admin\Exceptions\SiteConfigException;

class DatabaseCredentials
{
    private readonly string $host;
    private readonly string $login;
    private readonly string $pass;
    private readonly string $dbase;

    public function __construct(
        ?string $host,
        ?string $login,
        ?string $pass,
        ?string $dbase,
        private readonly int $port = 3306
    ) {
        if ($host === null || $login === null || $pass === null || $dbase === null) {
            throw new SiteConfigException('Database credentials are incomplete');
        }

        $this->host = $host;
        $this->login = $login;
        $this->pass = $pass;
        $this->dbase = $dbase;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getPass(): string
    {
        return $this->pass;
    }

    public function getDbase(): string
    {
        return $this->dbase;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * Get pool key for connection pooling
     */
    public function getPoolKey(): string
    {
        return "{$this->host}:{$this->port}/{$this->dbase}";
    }

    /**
     * Get sanitized credentials for logging (password masked)
     */
    public function toSanitizedArray(): array
    {
        return [
            'host' => $this->host,
            'login' => $this->login,
            'pass' => '***',
            'dbase' => $this->dbase,
            'port' => $this->port,
        ];
    }
}
