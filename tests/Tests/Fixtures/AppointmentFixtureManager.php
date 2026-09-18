<?php

/**
 * AppointmentFixtureManager - Provides test fixture data for AppointmentService tests.
 *
 * AI-Generated Code Notice: This file contains code generated with
 * assistance from Claude Code (Anthropic). The code has been reviewed
 * and tested by the contributor.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Craig Allen <craigrallen@gmail.com>
 * @copyright Copyright (c) 2026 Craig Allen <craigrallen@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Fixtures;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;

class AppointmentFixtureManager
{
    const FIXTURE_PREFIX = "test-fixture";

    /**
     * @var bool
     */
    private $hasInstalledDependencies = false;

    public function __construct(
        private readonly FixtureManager $patientFixtureManager = new FixtureManager(),
        private readonly FacilityFixtureManager $facilityFixtureManager = new FacilityFixtureManager()
    ) {
    }

    /**
     * Installs patient and facility fixtures that appointments depend on.
     * Returns the pid and facility id for use in test data.
     *
     * @return array{pid: int, facility_id: int}
     */
    public function installDependencies(): array
    {
        if (!$this->hasInstalledDependencies) {
            $this->patientFixtureManager->installPatientFixtures();
            $this->facilityFixtureManager->installFacilityFixtures();
            $this->hasInstalledDependencies = true;
        }

        // Get the first test patient's pid
        $patientRow = QueryUtils::querySingleRow(
            "SELECT pid FROM patient_data WHERE pubpid LIKE ? LIMIT 1",
            [self::FIXTURE_PREFIX . "%"]
        );
        if ($patientRow === false || !isset($patientRow['pid']) || !is_numeric($patientRow['pid'])) {
            // @codeCoverageIgnoreStart Defensive check — only fires if test infrastructure is broken.
            throw new \RuntimeException('Failed to find test patient fixture — did installPatientFixtures() succeed?');
            // @codeCoverageIgnoreEnd
        }
        $pid = (int) $patientRow['pid'];

        // Get the first test facility's id
        $facilityRow = QueryUtils::querySingleRow(
            "SELECT id FROM facility WHERE name LIKE ? LIMIT 1",
            [self::FIXTURE_PREFIX . "%"]
        );
        if ($facilityRow === false || !isset($facilityRow['id']) || !is_numeric($facilityRow['id'])) {
            // @codeCoverageIgnoreStart Defensive check — only fires if test infrastructure is broken.
            throw new \RuntimeException('Failed to find test facility fixture — did installFacilityFixtures() succeed?');
            // @codeCoverageIgnoreEnd
        }
        $facilityId = (int) $facilityRow['id'];

        return ['pid' => $pid, 'facility_id' => $facilityId];
    }

    /**
     * Returns a valid appointment data array for use with AppointmentService::insert().
     *
     * @param int $facilityId The facility ID
     * @return array<string, mixed>
     */
    public function getSingleAppointmentFixture(int $facilityId): array
    {
        return [
            'pc_catid' => 5, // office_visit — always exists in default install
            'pc_title' => self::FIXTURE_PREFIX . '-Office Visit',
            'pc_duration' => 900, // 15 minutes in seconds
            'pc_hometext' => self::FIXTURE_PREFIX . ' Routine checkup notes',
            'pc_apptstatus' => '-', // None — always exists in default install
            'pc_eventDate' => date('Y-m-d'),
            'pc_startTime' => '10:00',
            'pc_facility' => $facilityId,
            'pc_billing_location' => $facilityId,
        ];
    }

    /**
     * Returns a second appointment data array with different values.
     *
     * @param int $facilityId The facility ID
     * @return array<string, mixed>
     */
    public function getSecondAppointmentFixture(int $facilityId): array
    {
        return [
            'pc_catid' => 10, // new_patient — always exists in default install
            'pc_title' => self::FIXTURE_PREFIX . '-New Patient Visit',
            'pc_duration' => 1800, // 30 minutes in seconds
            'pc_hometext' => self::FIXTURE_PREFIX . ' New patient intake',
            'pc_apptstatus' => '^', // Pending — always exists in default install
            'pc_eventDate' => date('Y-m-d', strtotime('+1 day')),
            'pc_startTime' => '14:30',
            'pc_facility' => $facilityId,
            'pc_billing_location' => $facilityId,
        ];
    }

    /**
     * Removes all test appointment fixtures and their dependencies.
     */
    public function removeFixtures(): void
    {
        $bindVariable = self::FIXTURE_PREFIX . "%";

        // Remove test appointments by title
        try {
            // Remove uuid_registry entries for our test appointments
            $select = "SELECT `uuid` FROM `openemr_postcalendar_events` WHERE `pc_title` LIKE ?";
            $records = QueryUtils::fetchRecords($select, [$bindVariable]);
            foreach ($records as $row) {
                if ($row['uuid'] !== null && $row['uuid'] !== '') {
                    QueryUtils::sqlStatementThrowException(
                        "DELETE FROM `uuid_registry` WHERE `table_name` = 'openemr_postcalendar_events' AND `uuid` = ?",
                        [$row['uuid']]
                    );
                }
            }

            // Remove the appointments
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM openemr_postcalendar_events WHERE pc_title LIKE ?",
                [$bindVariable]
            );
        // @codeCoverageIgnoreStart Defensive catch — only fires on unexpected DB errors during cleanup.
        } catch (SqlQueryException $exception) {
            ServiceContainer::getLogger()->error(
                "Failed to delete appointment fixture data",
                ['message' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]
            );
        }
        // @codeCoverageIgnoreEnd

        // Remove dependency fixtures
        if ($this->hasInstalledDependencies) {
            $this->facilityFixtureManager->removeFixtures();
            $this->patientFixtureManager->removePatientFixtures();
            $this->hasInstalledDependencies = false;
        }
    }
}
