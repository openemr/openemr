<?php

/**
 * Site Version Reader Service
 *
 * Reads version information and site name from database.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Admin\Services;

use OpenEMR\Admin\Contracts\SiteVersionReaderInterface;
use OpenEMR\Admin\Exceptions\DatabaseQueryException;
use OpenEMR\Admin\ValueObjects\SiteVersion;

class SiteVersionReader implements SiteVersionReaderInterface
{
    private int $expectedDatabase;
    private int $expectedAcl;
    private int $expectedPatch;

    public function __construct(string $versionFilePath)
    {
        $this->loadVersionExpectations($versionFilePath);
    }

    /**
     * Load expected version numbers from version.php
     */
    private function loadVersionExpectations(string $versionFilePath): void
    {
        if (!file_exists($versionFilePath)) {
            throw new \RuntimeException("Version file not found: {$versionFilePath}");
        }

        /** @var int $v_database */
        $v_database = 0;
        /** @var int $v_acl */
        $v_acl = 0;
        /** @var int $v_realpatch */
        $v_realpatch = 0;

        require $versionFilePath;

        $this->expectedDatabase = $v_database;
        $this->expectedAcl = $v_acl;
        $this->expectedPatch = $v_realpatch;
    }

    /**
     * {@inheritdoc}
     */
    public function readVersion(\mysqli $connection): SiteVersion
    {
        // Check if version table exists
        $result = $this->executeQuery(
            $connection,
            "SHOW TABLES LIKE 'version'"
        );

        if (!$result) {
            throw new DatabaseQueryException(
                "Version table not found",
                "SHOW TABLES LIKE 'version'",
                "Table does not exist"
            );
        }

        // Fetch version data
        $versionRow = $this->executeQuery(
            $connection,
            "SELECT * FROM version LIMIT 1"
        );

        if (!$versionRow) {
            throw new DatabaseQueryException(
                "No version data found",
                "SELECT * FROM version",
                "No rows returned"
            );
        }

        return new SiteVersion(
            major: (string)($versionRow['v_major'] ?? '0'),
            minor: (string)($versionRow['v_minor'] ?? '0'),
            patch: (string)($versionRow['v_patch'] ?? '0'),
            tag: (string)($versionRow['v_tag'] ?? ''),
            realPatch: (int)($versionRow['v_realpatch'] ?? 0),
            database: (int)($versionRow['v_database'] ?? 0),
            acl: (int)($versionRow['v_acl'] ?? 0)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSiteName(\mysqli $connection): string
    {
        $row = $this->executeQuery(
            $connection,
            "SELECT gl_value FROM globals WHERE gl_name = 'openemr_name' LIMIT 1"
        );

        return $row ? (string)$row['gl_value'] : '';
    }

    /**
     * Get expected version numbers for upgrade comparison
     *
     * @return array{database: int, acl: int, patch: int}
     */
    public function getExpectedVersions(): array
    {
        return [
            'database' => $this->expectedDatabase,
            'acl' => $this->expectedAcl,
            'patch' => $this->expectedPatch,
        ];
    }

    /**
     * Execute a query and return single row
     *
     * @return array<string, mixed>|null
     */
    private function executeQuery(\mysqli $connection, string $query): ?array
    {
        if (!$connection instanceof \mysqli || !@mysqli_ping($connection)) {
            throw new DatabaseQueryException(
                "Invalid database connection",
                $query,
                "Connection is not valid"
            );
        }

        $result = @mysqli_query($connection, $query);

        if (!$result instanceof \mysqli_result) {
            $error = mysqli_error($connection) ?? 'Unknown error';
            throw new DatabaseQueryException(
                "Database query failed",
                $query,
                $error
            );
        }

        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        mysqli_free_result($result);

        return $row ?: null;
    }
}
