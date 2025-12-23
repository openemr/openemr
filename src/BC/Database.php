<?php

declare(strict_types=1);

namespace OpenEMR\BC;

use Doctrine\DBAL\{
    Connection,
    DriverManager,
    Result,
};

/**
 * Transitional wrapper for database operations (see library/sql.inc.php)
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
            error_log(print_r($params, true));
            $connection = DriverManager::getConnection($params);
            self::$instance = new self($connection);
        }
        return self::$instance;
    }

    private function __construct(
        private Connection $connection,
    ) {
    }

    private static function readLegacyConfig(): array
    {
        // require __DIR__ . '/../../library/sqlconf.php';
        global $sqlconf;
        // replicate the same ssl cert detection in a compatible format

        $connParams = [
            'driver' => 'pdo_mysql',
            'user' => $sqlconf['login'],
            'password' => $sqlconf['pass'],
            'dbname' => $sqlconf['dbase'],
            'host' => $sqlconf['host'],
            'charset' => $sqlconf['db_encoding'],
            'port' => $sqlconf['port'],
        ];

        // if ssl, provide
        return $connParams;
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

    private function query(string $sql, array $bindings = []): Result
    {
        // TODO: middleware for logging, performance metrics, etc.
        error_log($sql);

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
