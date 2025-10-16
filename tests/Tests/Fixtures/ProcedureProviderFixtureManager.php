<?php

/**
 * ProcedureProviderFixtureManager.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@openemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Fixtures;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\Logging\SystemLogger;

/**
 * Manages test fixtures for procedure providers (labs)
 *
 * This class handles installation and removal of test procedure provider
 * records for integration testing of HL7 order generation.
 */
class ProcedureProviderFixtureManager extends BaseFixtureManager
{
    /**
     * @var array<int, array<string, mixed>> Array of installed provider records
     */
    private array $installedProviders = [];

    /**
     * Initialize the fixture manager for procedure providers
     */
    public function __construct()
    {
        parent::__construct("procedure-providers.json", "procedure_providers");
    }

    /**
     * Install procedure provider fixtures into the database
     *
     * Loads provider data from JSON fixture file and inserts into
     * procedure_providers table.
     *
     * @return int Number of fixtures successfully installed
     */
    public function installFixtures(): int
    {
        $fixtures = $this->getFixturesFromFile();
        $insertCount = 0;

        foreach ($fixtures as $fixture) {
            $ppid = $this->insertProcedureProvider($fixture);
            if ($ppid) {
                $fixture['ppid'] = $ppid;
                $this->installedProviders[] = $fixture;
                $insertCount++;
            }
        }

        return $insertCount;
    }

    /**
     * Insert a single procedure provider record into the database
     *
     * @param array<string, mixed> $fixture Provider data to insert
     * @return int|false Inserted provider ID (ppid) or false on failure
     * @throws SqlQueryException If database insert fails
     */
    private function insertProcedureProvider(array $fixture): int|false
    {
        $sql = "INSERT INTO procedure_providers SET ";
        $sqlColumnValues = "";
        $sqlBinds = [];

        foreach ($fixture as $key => $value) {
            if ($key === 'ppid') {
                continue;
            }
            $sqlColumnValues .= $key . " = ?, ";
            array_push($sqlBinds, $value);
        }

        $sqlColumnValues = rtrim($sqlColumnValues, ", ");
        $sql .= $sqlColumnValues;

        try {
            $ppid = QueryUtils::sqlInsert($sql, $sqlBinds);
            return $ppid;
        } catch (SqlQueryException $exception) {
            (new SystemLogger())->error(
                "Failed to insert procedure_providers data",
                ['message' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]
            );
            throw $exception;
        }
    }

    /**
     * Get all installed provider fixtures
     *
     * @return array<int, array<string, mixed>> Array of provider records
     */
    public function getInstalledProviders(): array
    {
        return $this->installedProviders;
    }

    /**
     * Find a provider by partial name match
     *
     * Performs case-insensitive search for provider name containing
     * the specified string.
     *
     * @param string $nameLike Partial name to search for
     * @return array<string, mixed>|null Provider record if found, null otherwise
     */
    public function getProviderByName(string $nameLike): ?array
    {
        foreach ($this->installedProviders as $provider) {
            if (stripos((string) $provider['name'], $nameLike) !== false) {
                return $provider;
            }
        }
        return null;
    }

    /**
     * Remove all installed fixtures from the database
     *
     * @return void
     */
    public function removeFixtures(): void
    {
        $this->removeInstalledFixtures();
    }

    /**
     * Delete all test fixture provider records from the database
     *
     * Removes all procedure_providers records with names starting
     * with 'test-fixture-' prefix.
     *
     * @return void
     * @throws SqlQueryException If database deletion fails
     */
    protected function removeInstalledFixtures(): void
    {
        $sql = "DELETE FROM procedure_providers WHERE name LIKE 'test-fixture-%'";
        try {
            QueryUtils::sqlStatementThrowException($sql, []);
        } catch (SqlQueryException $exception) {
            (new SystemLogger())->error(
                "Failed to delete procedure_providers data",
                ['message' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]
            );
            throw $exception;
        }
    }
}
