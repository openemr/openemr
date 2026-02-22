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
 * This class provides a type-safe way to configure database connections while
 * ensuring that sensitive values (password) are protected from leaking in
 * stack traces, var_dump(), and error logs.
 *
 * @internal
 *
 * @phpstan-import-type Params from DriverManager
 * @phpstan-type SqlConf array{
 *   dbase: string,
 *   login: string,
 *   pass: string,
 *   host?: string,
 *   port?: int|string,
 *   socket?: string,
 * }
 * @phpstan-type ClientCert array{
 *   cert: string,
 *   key: string,
 * }
 * @phpstan-type SslConfig array{
 *   ca?: string,
 *   clientCert?: ClientCert,
 * }
 */
final readonly class DatabaseConnectionOptions
{
    private const REDACTED = '[REDACTED]';

    public string $charset;

    /**
     * @param ClientCert|null $sslClientCert Client cert/key pair for mTLS
     */
    public function __construct(
        public string $dbname,
        public string $user,
        #[SensitiveParameter]
        public string $password,
        public ?string $host = null,
        public ?int $port = null,
        public ?string $unixSocket = null,
        public ?string $sslCaPath = null,
        public ?array $sslClientCert = null,
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

        $this->charset = 'utf8mb4';
    }

    /**
     * Creates options for a site by loading its sqlconf.php file.
     *
     * @param string $siteDir Site directory path (e.g., OE_SITE_DIR)
     * @param literal-string $configFile Config filename to load
     */
    public static function forSite(
        string $siteDir,
        string $configFile = 'sqlconf.php',
    ): self {
        $sqlconf = self::loadSqlconf($siteDir, $configFile);
        $sslPaths = self::inferSslPaths($siteDir);

        return self::fromSqlconf($sqlconf, $sslPaths);
    }

    /**
     * Creates options from a pre-parsed sqlconf array.
     *
     * @param SqlConf $sqlconf
     * @param SslConfig $ssl
     */
    public static function fromSqlconf(array $sqlconf, array $ssl = []): self
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
            sslCaPath: $ssl['ca'] ?? null,
            sslClientCert: $ssl['clientCert'] ?? null,
        );
    }

    /**
     * Detects MySQL SSL certificate files in site directory.
     *
     * @return SslConfig
     */
    private static function inferSslPaths(string $siteDir): array
    {
        $config = [];

        $certDir = sprintf('%s/documents/certificates', $siteDir);
        $caFile = sprintf('%s/mysql-ca', $certDir);
        $cert = sprintf('%s/mysql-cert', $certDir);
        $key = sprintf('%s/mysql-key', $certDir);

        if (file_exists($caFile)) {
            $config['ca'] = $caFile;
        }
        if (file_exists($cert) || file_exists($key)) {
            if (!file_exists($cert) || !file_exists($key)) {
                throw new LogicException(
                    'MySQL cert or key file missing. You need both or neither.'
                );
            }
            $config['clientCert'] = [
                'cert' => $cert,
                'key' => $key,
            ];
        }

        return $config;
    }

    /**
     * @param literal-string $configFile
     * @return SqlConf
     */
    private static function loadSqlconf(string $siteDir, string $configFile): array
    {
        $sqlconfPath = $siteDir . '/' . $configFile;
        if (!file_exists($sqlconfPath)) {
            throw new RuntimeException(sprintf(
                '%s not found in %s. Is the site configured?',
                $configFile,
                $siteDir,
            ));
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

        $params['charset'] = $this->charset;

        $driverOptions = [];
        if ($this->sslCaPath !== null) {
            $driverOptions[PDO::MYSQL_ATTR_SSL_CA] = $this->sslCaPath;
        }
        if ($this->sslClientCert !== null) {
            $driverOptions[PDO::MYSQL_ATTR_SSL_CERT] = $this->sslClientCert['cert'];
            $driverOptions[PDO::MYSQL_ATTR_SSL_KEY] = $this->sslClientCert['key'];
        }
        if ($driverOptions !== []) {
            $params['driverOptions'] = $driverOptions;
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
            'sslCaPath' => $this->sslCaPath,
            'sslClientCert' => $this->sslClientCert,
        ];
    }
}
