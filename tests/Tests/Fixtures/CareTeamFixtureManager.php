<?php

/**
 * CareTeamFixtureManager - Provides test fixture data for CareTeamService tests.
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

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\Logging\SystemLogger;

class CareTeamFixtureManager
{
    const FIXTURE_PREFIX = "test-fixture";

    /**
     * @var FixtureManager
     */
    private $patientFixtureManager;

    /**
     * @var FacilityFixtureManager
     */
    private $facilityFixtureManager;

    /**
     * @var PractitionerFixtureManager
     */
    private $practitionerFixtureManager;

    /**
     * @var bool
     */
    private $hasInstalledDependencies = false;

    public function __construct(
        ?FixtureManager $patientFixtureManager = null,
        ?FacilityFixtureManager $facilityFixtureManager = null,
        ?PractitionerFixtureManager $practitionerFixtureManager = null
    ) {
        $this->patientFixtureManager = $patientFixtureManager ?? new FixtureManager();
        $this->facilityFixtureManager = $facilityFixtureManager ?? new FacilityFixtureManager();
        $this->practitionerFixtureManager = $practitionerFixtureManager ?? new PractitionerFixtureManager();
    }

    /**
     * Installs patient, facility, and practitioner fixtures that care teams depend on.
     *
     * @return array{pid: int, facility_id: int, provider_id: int}
     */
    public function installDependencies(): array
    {
        if (!$this->hasInstalledDependencies) {
            $this->patientFixtureManager->installPatientFixtures();
            $this->facilityFixtureManager->installFacilityFixtures();
            $this->practitionerFixtureManager->installPractitionerFixtures();
            $this->hasInstalledDependencies = true;
        }

        // Get the first test patient's pid
        $patientRow = sqlQuery(
            "SELECT pid FROM patient_data WHERE pubpid LIKE ? LIMIT 1",
            [self::FIXTURE_PREFIX . "%"]
        );
        if ($patientRow === false || !isset($patientRow['pid'])) {
            throw new \RuntimeException('Failed to find test patient fixture — did installPatientFixtures() succeed?');
        }
        $pid = intval($patientRow['pid']);

        // Get the first test facility's id
        $facilityRow = sqlQuery(
            "SELECT id FROM facility WHERE name LIKE ? LIMIT 1",
            [self::FIXTURE_PREFIX . "%"]
        );
        if ($facilityRow === false || !isset($facilityRow['id'])) {
            throw new \RuntimeException('Failed to find test facility fixture — did installFacilityFixtures() succeed?');
        }
        $facilityId = intval($facilityRow['id']);

        // Get the first test practitioner's id
        $providerRow = sqlQuery(
            "SELECT id FROM users WHERE fname LIKE ? LIMIT 1",
            [self::FIXTURE_PREFIX . "%"]
        );
        if ($providerRow === false || !isset($providerRow['id'])) {
            throw new \RuntimeException('Failed to find test practitioner fixture — did installPractitionerFixtures() succeed?');
        }
        $providerId = intval($providerRow['id']);

        return ['pid' => $pid, 'facility_id' => $facilityId, 'provider_id' => $providerId];
    }

    /**
     * Removes all test care team fixtures and their dependencies.
     */
    public function removeFixtures(): void
    {
        $bindVariable = self::FIXTURE_PREFIX . "%";

        try {
            // Get care team IDs for our test teams
            $teamIds = [];
            $select = sqlStatement(
                "SELECT id, uuid FROM care_teams WHERE team_name LIKE ?",
                [$bindVariable]
            );
            while ($row = sqlFetchArray($select)) {
                $teamIds[] = $row['id'];
                if (!empty($row['uuid'])) {
                    sqlQuery(
                        "DELETE FROM uuid_registry WHERE table_name = 'care_teams' AND uuid = ?",
                        [$row['uuid']]
                    );
                }
            }

            // Remove care team members for our test teams
            if (!empty($teamIds)) {
                $placeholders = implode(',', array_fill(0, count($teamIds), '?'));
                QueryUtils::sqlStatementThrowException(
                    "DELETE FROM care_team_member WHERE care_team_id IN ($placeholders)",
                    $teamIds
                );
            }

            // Remove the care teams themselves
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM care_teams WHERE team_name LIKE ?",
                [$bindVariable]
            );
        } catch (SqlQueryException $exception) {
            (new SystemLogger())->error(
                "Failed to delete care team fixture data",
                ['message' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]
            );
        }

        // Remove dependency fixtures
        if ($this->hasInstalledDependencies) {
            $this->practitionerFixtureManager->removePractitionerFixtures();
            $this->facilityFixtureManager->removeFixtures();
            $this->patientFixtureManager->removePatientFixtures();
            $this->hasInstalledDependencies = false;
        }
    }
}
