<?php

declare(strict_types=1);

namespace OpenEMR\BC;

use Doctrine\DBAL\{
    Connection,
    DriverManager,
    Result,
};
use LogicException;
use OpenEMR\Core\OEGlobalsBag;
use PDO;

/**
 * Backwards-compatible wrapper for database operations. See
 * `library/sql.inc.php`.
 *
 * DO NOT WRITE CODE THAT INTERACTS WITH THIS CLASS DIRECTLY!
 *
 * The aim of this class is to have a way to hook `doctrine/dbal` in to
 * existing code without changes, replacing connections that are managed with
 * `ADOdb` and `laminas-db`. For now, continue to use the existing wrappers
 * (e.g. QueryUtils) for database interactions.
 *
 * In the future, the DBAL `Connection` may be made avaible through a DI
 * container, and/or something like `doctrine/orm` for most interactions.
 */
class Database
{
    /**
     * Singleton instance. Future scope may have this removed in favor of some
     * sort of DI/service container.
     */
    private static ?Database $instance = null;

    /**
     * Gets the singleton based on the "legacy" config system
     * (sites/default/sqlconf.php, etc)
     */
    public static function instance(): Database
    {
        if (self::$instance === null) {
            $params = self::readLegacyConfig();
            $connection = DriverManager::getConnection($params);
            self::$instance = new self($connection);
        }
        return self::$instance;
    }

    /**
     * This is private to force access through `instance()`. Future
     * DBAL-related integration should use its connection directly, rather than
     * go through this backwards-compatibility layer.
     */
    private function __construct(
        private Connection $connection,
    ) {
    }

    private static function readLegacyConfig(): array
    {
        $bag = OEGlobalsBag::getInstance(true);
        $sqlconf = $bag->get('sqlconf');
        if (empty($sqlconf)) {
            throw new LogicException(
                'sqlconf empty or missing. Was interface/globals.php included?'
            );
        }
        // replicate the same ssl cert detection in a compatible format

        $connParams = [
            'driver' => 'pdo_mysql',
            'dbname' => $sqlconf['dbase'],
            'host' => $sqlconf['host'],
            'port' => $sqlconf['port'],
            'user' => $sqlconf['login'],
            'password' => $sqlconf['pass'],
            'charset' => $sqlconf['db_encoding'],
        ];

        $siteDir = $bag->getString('OE_SITE_DIR');
        $options = self::inferSslOptions($siteDir);
        $connParams['driverOptions'] = $options;

        return $connParams;
    }

    /**
     * Inspects the filesystem for MySQL certificate files in
     * OE_SITE_DIR/documents/certificates and, if present, returns PDO SSL
     * options to use them.
     *
     * @param string $siteDir The currently-active site directory (OE_SITE_DIR,
     * typically speaking).
     *
     * @return array<PDO::MYSQL_*, string>
     */
    private static function inferSslOptions(string $siteDir): array
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
                throw new LogicException('MySQL cert or key file missing. You need both or neither.');
            }
            $options[PDO::MYSQL_ATTR_SSL_CERT] = $cert;
            $options[PDO::MYSQL_ATTR_SSL_KEY] = $key;
        }

        return $options;
    }

    /**
     * Returns the single row as an associative array, or null if there was no
     * result.
     */
    public function fetchOneRow(string $sql, array $bindings = []): ?array
    {
        $row = $this->query($sql, $bindings)->fetchAssociative();
        if ($row === false) {
            return null;
        }
        return $row;
    }

    /**
     * Performs a SELECT statement and returns the result
     */
    private function query(string $sql, array $bindings = []): Result
    {
        // TODO: middleware for logging, performance metrics, etc.
        // error_log($sql);

        $stmt = $this->connection->prepare($sql);
        foreach ($bindings as $i => $binding) {
            // SQL bindings are 1-indexed, not 0-indexed like the input
            $stmt->bindValue($i + 1, $binding);
        }
        return $stmt->executeQuery();
    }

    /**
     * @param literal-string $table The sequence table (usually `sequences`)
     * @return int The next incremental ID
     *
     * Caution: this _may_ have data races in the current implementation,
     * especially across parallel requests.
     */
    public function generateSequentialId(string $table): int
    {
        // Warning: table names cannot be parameterized.
        $query = sprintf('UPDATE %s SET id=LAST_INSERT_ID(id+1)', $table);
        $_ = $this->connection->executeStatement($query);
        return (int) $this->connection->lastInsertId();
    }
}
