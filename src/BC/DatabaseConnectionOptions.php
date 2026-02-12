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
use InvalidArgumentException;
use LogicException;
use PDO;
use RuntimeException;
use SensitiveParameter;

/**
 * Represents database connection options for Doctrine DBAL.
 *
 * This class provides a type-safe way to configure database connections while
 * ensuring that sensitive values (password) are protected from leaking in
 * stack traces, var_dump(), and error logs.
 *
 * @phpstan-import-type Params from DriverManager
 * @phpstan-type SqlConf array{
 *   dbase: string,
 *   login: string,
 *   pass: string,
 *   host?: string,
 *   port?: int|string,
 *   socket?: string,
 *   db_encoding?: string,
 * }
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
        $hasHost = $host !== null;
        $hasPort = $port !== null;
        $hasSocket = $unixSocket !== null;

        $hasTcp = $hasHost && $hasPort;
        $hasPartialTcp = $hasHost !== $hasPort;

        if ($hasPartialTcp) {
            throw new InvalidArgumentException(
                'host and port must both be provided together'
            );
        }
        if ($hasTcp && $hasSocket) {
            throw new InvalidArgumentException(
                'Cannot specify both host/port and unixSocket'
            );
        }
        if (!$hasTcp && !$hasSocket) {
            throw new InvalidArgumentException(
                'Must specify either host/port or unixSocket'
            );
        }
    }

    /**
     * Creates options for a site by loading its sqlconf.php file.
     *
     * @param string $siteName Site identifier (e.g., 'default')
     * @param string|null $sitesBasePath Override path to sites directory
     */
    public static function forSite(
        string $siteName,
        ?string $sitesBasePath = null,
    ): self {
        $sitesBasePath ??= dirname(__DIR__, 2) . '/sites';
        $siteDir = $sitesBasePath . '/' . $siteName;

        $sqlconf = self::loadSqlconf($siteDir);
        $driverOptions = self::inferSslOptions($siteDir);

        return self::fromSqlconf($sqlconf, $driverOptions);
    }

    /**
     * Creates options from a pre-parsed sqlconf array.
     *
     * @param SqlConf $sqlconf
     * @param array<int, mixed> $driverOptions
     */
    public static function fromSqlconf(array $sqlconf, array $driverOptions = []): self
    {
        $host = $sqlconf['host'] ?? null;
        $port = isset($sqlconf['port']) ? (int) $sqlconf['port'] : null;
        $unixSocket = $sqlconf['socket'] ?? null;

        return new self(
            dbname: $sqlconf['dbase'],
            user: $sqlconf['login'],
            password: $sqlconf['pass'],
            host: $host,
            port: $port,
            unixSocket: $unixSocket,
            charset: $sqlconf['db_encoding'] ?? 'utf8mb4',
            driverOptions: $driverOptions,
        );
    }

    /**
     * Detects MySQL SSL certificate files in site directory.
     *
     * @return array<int, string>
     */
    public static function inferSslOptions(string $siteDir): array
    {
        $options = [];

        $certDir = sprintf('%s/documents/certificates', $siteDir);
        $caFile = sprintf('%s/mysql-ca', $certDir);
        $cert = sprintf('%s/mysql-cert', $certDir);
        $key = sprintf('%s/mysql-key', $certDir);

        if (file_exists($caFile)) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = $caFile;
        }
        if (file_exists($cert) || file_exists($key)) {
            if (!file_exists($cert) || !file_exists($key)) {
                throw new LogicException(
                    'MySQL cert or key file missing. You need both or neither.'
                );
            }
            $options[PDO::MYSQL_ATTR_SSL_CERT] = $cert;
            $options[PDO::MYSQL_ATTR_SSL_KEY] = $key;
        }

        return $options;
    }

    /**
     * @return SqlConf
     */
    private static function loadSqlconf(string $siteDir): array
    {
        $sqlconfPath = $siteDir . '/sqlconf.php';
        if (!file_exists($sqlconfPath)) {
            throw new RuntimeException("sqlconf.php not found at: $sqlconfPath");
        }

        $loader = static function (string $path): array {
            require $path;
            if (!isset($sqlconf) || !is_array($sqlconf)) {
                throw new RuntimeException('sqlconf.php did not define $sqlconf array');
            }
            return $sqlconf;
        };

        /** @var SqlConf $result */
        $result = $loader($sqlconfPath);
        return $result;
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
