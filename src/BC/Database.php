<?php

declare(strict_types=1);

namespace OpenEMR\BC;

use Doctrine\DBAL\{
    Connection,
    DriverManager,
    Exception as DBALException,
    Result,
};
use LogicException;
use OpenEMR\Core\OEGlobalsBag;
use PDO;
use PDOException;

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
 *
 * @internal
 *
 * @phpstan-type Bindings array<string|int|float>
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
     * This is public only for unit-testing purposes, consider it private. All
     * access should run through `Database::instance()`.
     */
    public function __construct(
        private readonly Connection $conn,
    ) {
    }

    private static function readLegacyConfig(): array
    {
        $bag = OEGlobalsBag::getInstance();
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
     *
     * This will NOT trigger a runtime error if the query has multiple rows.
     *
     * @param Bindings $bindings
     * @return array<string, mixed>
     */
    public function fetchOneRow(string $sql, array $bindings): ?array
    {
        $row = $this->query($sql, $bindings)->fetchAssociative();
        if ($row === false) {
            return null;
        }
        return $row;
    }

    /**
     * Returns the entire dataset from the query, as a a list of associative
     * arrays.
     *
     * e.g. SELECT a, b FROM foos would return something like this:
     * [
     *   ['a' => 'a1', 'b' => 'b1'],
     *   ['a' => 'a2', 'b' => 'b2'],
     * ]
     *
     * @param Bindings $bindings
     * @return array<string, mixed>[]
     */
    public function fetchAll(string $sql, array $bindings): array
    {
        return $this->query($sql, $bindings)->fetchAllAssociative();
    }

    /**
     * Performs a SELECT statement and returns the result.
     *
     * Running any other type of query through this path is a logical error.
     *
     * @param Bindings $bindings
     */
    private function query(string $sql, array $bindings): Result
    {
        return $this->conn->executeQuery($sql, $bindings);
    }

    /**
     * Performs a INSERT/UPDATE/DELETE statement and returns the number of rows
     * affected.
     *
     * Running a SELECT statement through this path is a logical error.
     *
     * @param Bindings $bindings
     *
     * @return int The number of rows changed
     */
    public function execute(string $sql, array $bindings): int
    {
        // In practice, a SELECT here will return effectively the COUNT(), but
        // don't do that.
        return $this->conn->executeStatement($sql, $bindings);
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
        $_ = $this->conn->executeStatement($query);
        return (int) $this->conn->lastInsertId();
    }

    /**
     * Runs the DB code in a way that downsamples catchable DBALExceptions into
     * the legacy `HelpfulDie` crash path.
     *
     * More than any other methods, don't build any NEW code on top of this.
     * It's just to bridge sql.inc.php.
     *
     * @phpstan-template T
     * @param callable(): T $action
     * @return T
     */
    public static function helpfulDieOnFailure(callable $action): mixed
    {
        try {
            return $action();
        } catch (DBALException $e) {
            self::helpfulDieDbal($e);
        }
    }

    // Down-convert an exception into a crash for extra backwards compat
    private static function helpfulDieDbal(DBALException $e): never
    {
        $sql = $e->getQuery()->getSQL();
        $sqlInfo = $e->getMessage();
        if ($info = self::extractSqlErrorFromDBAL($e)) {
            $sqlInfo = $info[2];
        }
        \HelpfulDie("query failed: $sql", $sqlInfo);
    }

    /**
     * Extracts SQL error info from the dbal exception stack without direct access
     * to the connection. For backwards-compatibility only, do not use outside of
     * this file.
     *
     * Returns a tuple of [sqlstate code, driver error code, driver error message]
     *
     * @link https://www.php.net/manual/en/pdostatement.errorinfo.php
     *
     * @return array{
     *   0: string,
     *   1: string,
     *   2: string,
     * }
     */
    private static function extractSqlErrorFromDBAL(DBALException $e): ?array
    {
        while ($inner = $e->getPrevious()) {
            $e = $inner;
        }
        if ($e instanceof PDOException) {
            return $e->errorInfo;
        }
        // This shouldn't be reachable without very weird driver settings
        return null;
    }
}
