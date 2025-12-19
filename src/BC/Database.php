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
    private static ?Database $instance = null;
    public static function instance(): Database
    {
        if (self::$instance === null) {
            $params = [
                'driver' => 'pdo_mysql',
                'user' => 'openemr',
                'password' => 'openemr',
                'dbname' => 'openemr',
                'host' => 'mysql',
                'port' => 3306,
                'charset' => 'utf8mb4',
                // 'driverOptions' => [
                //     // ssl settings?
                // ],

            ];
            $connection = DriverManager::getConnection($params);
            self::$instance = new self($connection);
        }
        return self::$instance;
    }

    private function __construct(
        private Connection $connection,
    ) {
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
        error_log($sql); // FIXME: remove

        $stmt = $this->connection->prepare($sql);
        foreach ($bindings as $i => $binding) {
            // SQL bindings are 1-indexed, not 0-indexed like the input
            $stmt->bindValue($i + 1, $binding);
        }
        return $stmt->executeQuery();
    }

    // private static function fromGlobals(array $globals): Database
    // {
    // }
    public function c(): Connection { return $this->connection; }
}
