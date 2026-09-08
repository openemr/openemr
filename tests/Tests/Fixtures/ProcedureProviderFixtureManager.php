<?php

/**
 * Manages test fixtures for procedure providers (labs).
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Fixtures;

use OpenEMR\Common\Database\QueryUtils;

/**
 * Manages test fixtures for procedure providers (labs)
 *
 * This class handles installation and removal of test procedure provider
 * records for integration testing of HL7 order generation.
 */
class ProcedureProviderFixtureManager extends BaseFixtureManager
{
    /** @var list<array<string, mixed>> Installed provider records, each with its assigned ppid */
    private array $installedProviders = [];

    /**
     * Initialize the fixture manager for procedure providers
     */
    public function __construct()
    {
        parent::__construct('procedure-providers.php', 'procedure_providers');
    }

    /**
     * Install procedure provider fixtures into the database
     *
     * Loads provider data from the PHP fixture file and inserts it into the
     * procedure_providers table.
     *
     * @return int Number of fixtures installed
     */
    public function installFixtures(): int
    {
        $insertCount = 0;

        foreach ($this->getFixturesFromFile() as $fixture) {
            $fixture['ppid'] = $this->insertProcedureProvider($fixture);
            $this->installedProviders[] = $fixture;
            $insertCount++;
        }

        return $insertCount;
    }

    /**
     * Insert a single procedure provider record into the database
     *
     * @param array<string, mixed> $fixture Provider data to insert
     * @return int Inserted provider ID (ppid)
     * @throws \OpenEMR\Common\Database\SqlQueryException If the database insert fails
     */
    private function insertProcedureProvider(array $fixture): int
    {
        $assignments = [];
        $binds = [];

        foreach ($fixture as $column => $value) {
            if ($column === 'ppid') {
                continue;
            }
            $assignments[] = $column . ' = ?';
            $binds[] = $value;
        }

        return QueryUtils::sqlInsert('INSERT INTO procedure_providers SET ' . implode(', ', $assignments), $binds);
    }

    /**
     * Get all installed provider fixtures
     *
     * @return list<array<string, mixed>> Provider records, each with its assigned ppid
     */
    public function getInstalledProviders(): array
    {
        return $this->installedProviders;
    }

    /**
     * Find an installed provider by partial, case-insensitive name match
     *
     * @param string $nameLike Partial name to search for
     * @return array<string, mixed>|null Provider record if found, null otherwise
     */
    public function getProviderByName(string $nameLike): ?array
    {
        foreach ($this->installedProviders as $provider) {
            $name = $provider['name'] ?? null;
            if (is_string($name) && stripos($name, $nameLike) !== false) {
                return $provider;
            }
        }

        return null;
    }

    /**
     * Remove all installed fixtures from the database
     */
    public function removeFixtures(): void
    {
        parent::removeFixtures();
    }

    /**
     * Delete all test fixture provider records from the database
     *
     * Removes every procedure_providers row whose name carries the fixture prefix.
     *
     * @throws \OpenEMR\Common\Database\SqlQueryException If the database deletion fails
     */
    protected function removeInstalledFixtures(): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM procedure_providers WHERE name LIKE ?',
            [self::FIXTURE_PREFIX . '-%']
        );
        $this->installedProviders = [];
    }
}
