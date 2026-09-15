<?php

/**
 * Manages test fixtures for procedure orders.
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
 * Manages test fixtures for procedure orders
 *
 * This class handles installation and removal of test procedure order
 * records along with associated procedure codes and form entries.
 * It coordinates with patient, encounter, and provider fixtures.
 */
class ProcedureOrderFixtureManager extends BaseFixtureManager
{
    private readonly EncounterFixtureManager $encounterFixtureManager;

    private readonly ProcedureProviderFixtureManager $procedureProviderFixtureManager;

    private readonly PractitionerFixtureManager $practitionerFixtureManager;

    /** @var list<int> Procedure order IDs created by this manager */
    private array $installedOrderIds = [];

    /** True once this manager installed the practitioner fixtures itself. */
    private bool $ownsPractitionerFixtures = false;

    /**
     * Initialize the fixture manager for procedure orders
     *
     * The patient fixtures are owned by the encounter manager, which installs and
     * removes them as part of its own lifecycle. The patient manager is therefore
     * only wired through to a default-constructed encounter manager; passing an
     * encounter manager makes this argument redundant.
     *
     * @param FixtureManager|null $patientFixtureManager Optional patient fixture manager
     * @param EncounterFixtureManager|null $encounterFixtureManager Optional encounter fixture manager
     * @param ProcedureProviderFixtureManager|null $procedureProviderFixtureManager Optional provider fixture manager
     */
    public function __construct(
        ?FixtureManager $patientFixtureManager = null,
        ?EncounterFixtureManager $encounterFixtureManager = null,
        ?ProcedureProviderFixtureManager $procedureProviderFixtureManager = null
    ) {
        parent::__construct('procedure-orders.php', 'procedure_order');

        $this->encounterFixtureManager = $encounterFixtureManager
            ?? new EncounterFixtureManager(null, $patientFixtureManager ?? new FixtureManager());
        $this->procedureProviderFixtureManager = $procedureProviderFixtureManager
            ?? new ProcedureProviderFixtureManager();
        // The ordering provider has to be a real practitioner with an NPI, and nothing
        // else in this dependency chain installs one: the encounter manager brings the
        // patient and facility fixtures only. This manager therefore owns the
        // practitioner fixtures itself.
        $this->practitionerFixtureManager = new PractitionerFixtureManager();
    }

    /**
     * Install procedure order fixtures into the database
     *
     * Installs patient, encounter, lab, and practitioner dependencies first,
     * then creates procedure orders with associated codes and form entries.
     *
     * @return int Number of fixtures installed
     * @throws \OpenEMR\Common\Database\SqlQueryException If a database write fails
     * @throws \RuntimeException If a required dependent fixture is missing
     */
    public function installFixtures(): int
    {
        // Install dependencies first. The encounter manager installs the patient
        // and facility fixtures it depends on; installing them again here would
        // duplicate every patient row and break the encounter insert's pubpid
        // subquery with "Subquery returns more than 1 row".
        $this->encounterFixtureManager->installFixtures();
        $this->procedureProviderFixtureManager->installFixtures();

        $patientId = self::requireId(
            QueryUtils::fetchSingleValue(
                <<<'SQL'
                SELECT pid FROM patient_data WHERE pubpid LIKE ? LIMIT 1
                SQL,
                'pid',
                [self::FIXTURE_PREFIX . '-%']
            ),
            'a patient fixture'
        );

        $encounterRecords = QueryUtils::fetchRecords(
            <<<'SQL'
            SELECT encounter FROM form_encounter WHERE reason LIKE ? LIMIT 1
            SQL,
            [self::FIXTURE_PREFIX . '-%']
        );
        if ($encounterRecords === []) {
            throw new \RuntimeException('Procedure order fixtures require an encounter fixture');
        }
        $encounterId = self::requireId($encounterRecords[0]['encounter'] ?? null, 'an encounter fixture');

        $orderingProviderId = $this->findFixturePractitionerId();
        if ($orderingProviderId === null) {
            $this->practitionerFixtureManager->installPractitionerFixtures();
            $this->ownsPractitionerFixtures = true;
            $orderingProviderId = self::requireId($this->findFixturePractitionerId(), 'a practitioner fixture');
        }

        $providers = $this->procedureProviderFixtureManager->getInstalledProviders();

        $insertCount = 0;
        foreach ($this->getFixturesFromFile() as $fixture) {
            $fixture['patient_id'] = $patientId;
            $fixture['encounter_id'] = $encounterId;
            $fixture['provider_id'] = $orderingProviderId;

            // Point the order at the first installed lab unless the fixture names one itself.
            if (($fixture['lab_id'] ?? null) === null && $providers !== []) {
                $fixture['lab_id'] = $providers[0]['ppid'] ?? null;
            }

            // The forms row carries the order's own date. form_encounter.date is not
            // usable here: encounters.php does not set it, so it is NULL for every
            // installed encounter.
            $orderDate = $fixture['date_ordered'] ?? null;

            $orderId = $this->insertProcedureOrder($fixture);
            $this->installedOrderIds[] = $orderId;
            $this->installProcedureOrderCodes($orderId);
            $this->createFormEntry($patientId, $encounterId, $orderId, is_string($orderDate) ? $orderDate : null);
            $insertCount++;
        }

        return $insertCount;
    }

    /**
     * Find the fixture practitioner to record as the ordering provider
     *
     * PractitionerFixtureManager stamps the fixture prefix onto `fname`, not
     * `username` — the fixture usernames are plain (kperez, lcohen, jmoses,
     * cjones, ajenane) — and its own teardown deletes by `fname` for the same
     * reason. Matching on `username` never selects a row.
     *
     * The NPI filter is what makes the result usable: HL7 order generation reads
     * the ordering provider's NPI, and the built-in admin user has none.
     *
     * @return int|null The practitioner's users.id, or null when no fixture practitioner is installed
     */
    private function findFixturePractitionerId(): ?int
    {
        $practitionerId = QueryUtils::fetchSingleValue(
            <<<'SQL'
            SELECT id FROM users
            WHERE fname LIKE ? AND npi IS NOT NULL AND npi <> ''
            ORDER BY id
            LIMIT 1
            SQL,
            'id',
            [self::FIXTURE_PREFIX . '-%']
        );

        return is_numeric($practitionerId) ? (int) $practitionerId : null;
    }

    /**
     * Insert a single procedure order record into the database
     *
     * @param array<string, mixed> $fixture Order data to insert
     * @return int Inserted order ID (procedure_order_id)
     * @throws \OpenEMR\Common\Database\SqlQueryException If the database insert fails
     */
    private function insertProcedureOrder(array $fixture): int
    {
        $assignments = [];
        $binds = [];

        foreach ($fixture as $column => $value) {
            if ($column === 'procedure_order_id') {
                continue;
            }
            $assignments[] = $column . ' = ?';
            $binds[] = $value;
        }

        return QueryUtils::sqlInsert('INSERT INTO procedure_order SET ' . implode(', ', $assignments), $binds);
    }

    /**
     * Install procedure order codes for a given order
     *
     * Loads procedure codes from the PHP fixture file and associates
     * them with the specified order ID.
     *
     * @param int $orderId The procedure order ID
     * @throws \OpenEMR\Common\Database\SqlQueryException If the database insert fails
     */
    private function installProcedureOrderCodes(int $orderId): void
    {
        foreach ($this->loadPhpFile('procedure-order-codes.php') as $codeFixture) {
            $codeFixture['procedure_order_id'] = $orderId;

            $assignments = [];
            $binds = [];
            foreach ($codeFixture as $column => $value) {
                $assignments[] = $column . ' = ?';
                $binds[] = $value;
            }

            QueryUtils::sqlInsert(
                'INSERT INTO procedure_order_code SET ' . implode(', ', $assignments),
                $binds
            );
        }
    }

    /**
     * Create a forms table entry for the procedure order
     *
     * Links the procedure order to an encounter via the forms table.
     *
     * @param int $patientId Patient ID
     * @param int $encounterId Encounter ID
     * @param int $orderId Procedure order ID
     * @param string|null $date Order date, or null to leave forms.date unset
     * @throws \OpenEMR\Common\Database\SqlQueryException If the database insert fails
     */
    private function createFormEntry(int $patientId, int $encounterId, int $orderId, ?string $date): void
    {
        QueryUtils::sqlInsert(
            <<<'SQL'
            INSERT INTO forms
            SET date = ?, encounter = ?, form_name = ?, form_id = ?, pid = ?,
                user = ?, groupname = ?, authorized = ?, formdir = ?
            SQL,
            [
                $date,
                $encounterId,
                'Procedure Order',
                $orderId,
                $patientId,
                'admin',
                'Default',
                1,
                'procedure_order',
            ]
        );
    }

    /**
     * Get all installed procedure order IDs
     *
     * @return list<int> Procedure order IDs created by this manager
     */
    public function getInstalledOrderIds(): array
    {
        return $this->installedOrderIds;
    }

    /**
     * Remove all installed fixtures from the database
     */
    public function removeFixtures(): void
    {
        parent::removeFixtures();
    }

    /**
     * Delete all test fixture order records from the database
     *
     * Removes procedure orders, procedure order codes, and associated
     * form entries. Also cleans up dependent fixtures (practitioners,
     * labs, encounters, patients).
     *
     * @throws \OpenEMR\Common\Database\SqlQueryException If a database deletion fails
     */
    protected function removeInstalledFixtures(): void
    {
        $fixturePrefix = self::FIXTURE_PREFIX . '-%';

        try {
            QueryUtils::sqlStatementThrowException(
                <<<'SQL'
                DELETE FROM procedure_order_code
                WHERE procedure_order_id IN (
                    SELECT procedure_order_id FROM procedure_order WHERE control_id LIKE ?
                )
                SQL,
                [$fixturePrefix]
            );

            QueryUtils::sqlStatementThrowException(
                <<<'SQL'
                DELETE FROM forms
                WHERE formdir = 'procedure_order'
                  AND form_id IN (
                    SELECT procedure_order_id FROM procedure_order WHERE control_id LIKE ?
                  )
                SQL,
                [$fixturePrefix]
            );

            QueryUtils::sqlStatementThrowException(
                <<<'SQL'
                DELETE FROM procedure_order WHERE control_id LIKE ?
                SQL,
                [$fixturePrefix]
            );

            $this->installedOrderIds = [];
        } finally {
            // Only tear down the practitioners this manager put there. When the
            // caller installed them first, they belong to the caller.
            if ($this->ownsPractitionerFixtures) {
                $this->practitionerFixtureManager->removePractitionerFixtures();
                $this->ownsPractitionerFixtures = false;
            }
            $this->procedureProviderFixtureManager->removeFixtures();
            // Removes the patient and facility fixtures along with the encounters.
            $this->encounterFixtureManager->removeFixtures();
        }
    }

    /**
     * Convert a database identifier read back as an untyped value into an int
     *
     * ADODB returns bigint columns as numeric strings, so the value has to be
     * narrowed before it can be passed on as an int.
     *
     * @throws \RuntimeException If the dependent fixture was not found
     */
    private static function requireId(mixed $value, string $description): int
    {
        if (!is_numeric($value)) {
            throw new \RuntimeException('Procedure order fixtures require ' . $description);
        }

        return (int) $value;
    }
}
