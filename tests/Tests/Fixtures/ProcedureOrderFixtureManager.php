<?php

/**
 * ProcedureOrderFixtureManager.php
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
 * Manages test fixtures for procedure orders
 *
 * This class handles installation and removal of test procedure order
 * records along with associated procedure codes and form entries.
 * It coordinates with patient, encounter, and provider fixtures.
 */
class ProcedureOrderFixtureManager extends BaseFixtureManager
{
    /**
     * @var FixtureManager Patient fixture manager
     */
    private FixtureManager $patientFixtureManager;

    /**
     * @var EncounterFixtureManager Encounter fixture manager
     */
    private EncounterFixtureManager $encounterFixtureManager;

    /**
     * @var ProcedureProviderFixtureManager Procedure provider fixture manager
     */
    private ProcedureProviderFixtureManager $procedureProviderFixtureManager;

    /**
     * @var array<int, int> Array of installed procedure order IDs
     */
    private array $installedOrderIds = [];

    /**
     * @var array<int, int> Array of installed provider IDs (unused but kept for compatibility)
     */
    private array $installedProviderIds = [];

    /**
     * Initialize the fixture manager for procedure orders
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
        parent::__construct("procedure-orders.json", "procedure_order");

        if (isset($patientFixtureManager)) {
            $this->patientFixtureManager = $patientFixtureManager;
        } else {
            $this->patientFixtureManager = new FixtureManager();
        }

        if (isset($encounterFixtureManager)) {
            $this->encounterFixtureManager = $encounterFixtureManager;
        } else {
            $this->encounterFixtureManager = new EncounterFixtureManager();
        }

        if (isset($procedureProviderFixtureManager)) {
            $this->procedureProviderFixtureManager = $procedureProviderFixtureManager;
        } else {
            $this->procedureProviderFixtureManager = new ProcedureProviderFixtureManager();
        }
    }

    /**
     * Install procedure order fixtures into the database
     *
     * Installs patient, encounter, and provider dependencies first,
     * then creates procedure orders with associated codes and form entries.
     *
     * @return int Number of fixtures successfully installed
     */
    public function installFixtures(): int
    {
        // Install dependencies first
        $this->patientFixtureManager->installPatientFixtures();
        $this->encounterFixtureManager->installFixtures();
        $this->procedureProviderFixtureManager->installFixtures();

        $fixtures = $this->getFixturesFromFile();

        // Get first patient and encounter for the test
        $patientId = QueryUtils::fetchSingleValue(
            "SELECT pid FROM patient_data WHERE pubpid LIKE 'test-fixture-%' LIMIT 1",
            'pid'
        );

        $encounterRecords = QueryUtils::fetchRecords(
            "SELECT encounter, date FROM form_encounter WHERE reason LIKE 'test-fixture-%' LIMIT 1",
            []
        );
        $encounter = !empty($encounterRecords) ? $encounterRecords[0] : null;

        // Get first provider
        $providerId = QueryUtils::fetchSingleValue(
            "SELECT id FROM users WHERE username LIKE 'test-fixture-%' OR id = 1 LIMIT 1",
            'id'
        );
        if (empty($providerId)) {
            $providerId = 1; // Default to first user
        }

        // Get the installed procedure providers
        $providers = $this->procedureProviderFixtureManager->getInstalledProviders();

        $insertCount = 0;
        foreach ($fixtures as $fixture) {
            $fixture['patient_id'] = $patientId;
            $fixture['encounter_id'] = $encounter['encounter'];
            $fixture['provider_id'] = $providerId;

            // Assign first provider as default
            if (empty($fixture['lab_id']) && !empty($providers)) {
                $fixture['lab_id'] = $providers[0]['ppid'];
            }

            $orderId = $this->insertProcedureOrder($fixture);
            if ($orderId) {
                $this->installedOrderIds[] = $orderId;

                // Install procedure order codes
                $this->installProcedureOrderCodes($orderId);

                // Create form entry
                $this->createFormEntry($patientId, $encounter['encounter'], $orderId, $encounter['date']);

                $insertCount++;
            }
        }

        return $insertCount;
    }

    /**
     * Insert a single procedure order record into the database
     *
     * @param array<string, mixed> $fixture Order data to insert
     * @return int|false Inserted order ID (procedure_order_id) or false on failure
     * @throws SqlQueryException If database insert fails
     */
    private function insertProcedureOrder(array $fixture): int|false
    {
        $sql = "INSERT INTO procedure_order SET ";
        $sqlColumnValues = "";
        $sqlBinds = [];

        foreach ($fixture as $key => $value) {
            if ($key === 'procedure_order_id') {
                continue;
            }
            $sqlColumnValues .= $key . " = ?, ";
            array_push($sqlBinds, $value);
        }

        $sqlColumnValues = rtrim($sqlColumnValues, ", ");
        $sql .= $sqlColumnValues;

        try {
            $orderId = QueryUtils::sqlInsert($sql, $sqlBinds);
            return $orderId;
        } catch (SqlQueryException $exception) {
            (new SystemLogger())->error(
                "Failed to insert procedure_order data",
                ['message' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]
            );
            throw $exception;
        }
    }

    /**
     * Install procedure order codes for a given order
     *
     * Loads procedure codes from JSON fixture file and associates
     * them with the specified order ID.
     *
     * @param int $orderId The procedure order ID
     * @return void
     */
    private function installProcedureOrderCodes(int $orderId): void
    {
        $codeFixtures = $this->loadJsonFile("procedure-order-codes.json");

        foreach ($codeFixtures as $codeFixture) {
            $codeFixture['procedure_order_id'] = $orderId;

            $sql = "INSERT INTO procedure_order_code SET ";
            $sqlColumnValues = "";
            $sqlBinds = [];

            foreach ($codeFixture as $key => $value) {
                $sqlColumnValues .= $key . " = ?, ";
                array_push($sqlBinds, $value);
            }

            $sqlColumnValues = rtrim($sqlColumnValues, ", ");
            $sql .= $sqlColumnValues;

            try {
                QueryUtils::sqlInsert($sql, $sqlBinds);
            } catch (SqlQueryException $exception) {
                (new SystemLogger())->error(
                    "Failed to insert procedure_order_code data",
                    ['message' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]
                );
            }
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
     * @param string $date Order date
     * @return void
     */
    private function createFormEntry(int $patientId, int $encounterId, int $orderId, string $date): void
    {
        $sql = "INSERT INTO forms SET " .
            "date = ?, " .
            "encounter = ?, " .
            "form_name = ?, " .
            "form_id = ?, " .
            "pid = ?, " .
            "user = ?, " .
            "groupname = ?, " .
            "authorized = ?, " .
            "formdir = ?";

        $binds = [
            $date,
            $encounterId,
            'Procedure Order',
            $orderId,
            $patientId,
            'admin',
            'Default',
            1,
            'procedure_order'
        ];

        try {
            QueryUtils::sqlInsert($sql, $binds);
        } catch (SqlQueryException $exception) {
            (new SystemLogger())->error(
                "Failed to insert forms data",
                ['message' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]
            );
        }
    }

    /**
     * Get all installed procedure order IDs
     *
     * @return array<int, int> Array of installed order IDs
     */
    public function getInstalledOrderIds(): array
    {
        return $this->installedOrderIds;
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
     * Delete all test fixture order records from the database
     *
     * Removes procedure orders, procedure order codes, and associated
     * form entries. Also cleans up dependent fixtures (providers,
     * encounters, patients).
     *
     * @return void
     * @throws SqlQueryException If database deletion fails
     */
    protected function removeInstalledFixtures(): void
    {
        try {
            // Remove procedure order codes
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM procedure_order_code WHERE procedure_order_id IN (SELECT procedure_order_id FROM procedure_order WHERE control_id LIKE 'test-fixture-%')",
                []
            );

            // Remove forms entries
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM forms WHERE formdir = 'procedure_order' AND form_id IN (SELECT procedure_order_id FROM procedure_order WHERE control_id LIKE 'test-fixture-%')",
                []
            );

            // Remove procedure orders
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM procedure_order WHERE control_id LIKE 'test-fixture-%'",
                []
            );
        } catch (SqlQueryException $exception) {
            (new SystemLogger())->error(
                "Failed to delete procedure order fixtures",
                ['message' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]
            );
            throw $exception;
        } finally {
            $this->procedureProviderFixtureManager->removeFixtures();
            $this->encounterFixtureManager->removeFixtures();
            $this->patientFixtureManager->removePatientFixtures();
        }
    }
}
