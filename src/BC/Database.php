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
        error_log(__METHOD__);
        // error_log(isset($sqlconf) ? 'sc exist' : 'no exust');
        // require __DIR__ . '/../../library/sqlconf.php';
        global $sqlconf;
        error_log(isset($sqlconf) ? 'sc exist' : 'no exust');
        // error_log(print_r(array_keys(get_defined_vars()), true));
        error_log(print_r($sqlconf, true));
        // replicate the same ssl cert detection in a compatible format
        // return $sqlconf;

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
