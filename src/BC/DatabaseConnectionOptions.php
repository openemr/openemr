<?php

/**
 * @package   openemr
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\BC;

use Doctrine\DBAL\DriverManager;
use FilesystemIterator;
use InvalidArgumentException;
use LogicException;
use PDO;
use RuntimeException;
use SensitiveParameter;
use SplFileInfo;
use UnexpectedValueException;

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
     * The result is kept for the rest of the request. A request
     * asks for it from several places (sql.inc.php, Database, the audit
     * logger, Gacl), and each read is a sqlconf.php load plus a read of the
     * certificates directory, which on a network filesystem are round trips
     * to the server.
     *
     * @param string $siteDir Site directory path (e.g., OE_SITE_DIR)
     */
    public static function forSite(
        string $siteDir,
    ): self {
        /** @var array<string, self> $bySite */
        static $bySite = [];

        return $bySite[$siteDir] ??= self::fromSqlconf(
            self::loadSqlconf($siteDir),
            self::inferSslPaths($siteDir),
        );
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
     * On network storage (NFS, EFS, gcsfuse) each lookup of a missing file
     * is a round trip, so this reads the directory once instead of checking
     * each certificate path.
     *
     * @return SslConfig
     */
    public static function inferSslPaths(string $siteDir): array
    {
        $config = [];

        $certDir = sprintf('%s/documents/certificates', $siteDir);
        $listed = self::listEntryNames($certDir);
        // Check only listed names, so a missing file costs no lookup, and
        // still check each one, so a dangling symlink doesn't count.
        $exists = static fn (string $name): bool =>
            ($listed === null || isset($listed[$name]))
            && file_exists(sprintf('%s/%s', $certDir, $name));

        if ($exists('mysql-ca')) {
            $config['ca'] = sprintf('%s/mysql-ca', $certDir);
        }
        $hasCert = $exists('mysql-cert');
        $hasKey = $exists('mysql-key');
        if ($hasCert !== $hasKey) {
            throw new LogicException(
                'MySQL cert or key file missing. You need both or neither.'
            );
        }
        if ($hasCert) {
            $config['clientCert'] = [
                'cert' => sprintf('%s/mysql-cert', $certDir),
                'key' => sprintf('%s/mysql-key', $certDir),
            ];
        }

        return $config;
    }

    /**
     * @return ?array<string, true> keyed by entry name; empty when the directory is missing,
     *     null when it exists but can't be listed
     */
    private static function listEntryNames(string $dir): ?array
    {
        try {
            $entries = new FilesystemIterator($dir, FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS);
        } catch (UnexpectedValueException) {
            return is_dir($dir) ? null : [];
        }

        $names = [];
        foreach ($entries as $entry) {
            if ($entry instanceof SplFileInfo) {
                $names[$entry->getFilename()] = true;
            }
        }
        return $names;
    }

    /**
     * @return SqlConf
     */
    private static function loadSqlconf(string $siteDir): array
    {
        $sqlconfPath = $siteDir . '/sqlconf.php';
        if (!file_exists($sqlconfPath)) {
            throw new RuntimeException(sprintf(
                '%s not found in %s. Is the site configured?',
                'sqlconf.php',
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
            // PHP 8.5+ moved MySQL-specific PDO attributes to Pdo\Mysql
            $attrSslCa = class_exists(\Pdo\Mysql::class) ? \Pdo\Mysql::ATTR_SSL_CA : PDO::MYSQL_ATTR_SSL_CA;
            $driverOptions[$attrSslCa] = $this->sslCaPath;
        }
        if ($this->sslClientCert !== null) {
            $attrSslCert = class_exists(\Pdo\Mysql::class) ? \Pdo\Mysql::ATTR_SSL_CERT : PDO::MYSQL_ATTR_SSL_CERT;
            $attrSslKey = class_exists(\Pdo\Mysql::class) ? \Pdo\Mysql::ATTR_SSL_KEY : PDO::MYSQL_ATTR_SSL_KEY;
            $driverOptions[$attrSslCert] = $this->sslClientCert['cert'];
            $driverOptions[$attrSslKey] = $this->sslClientCert['key'];
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
