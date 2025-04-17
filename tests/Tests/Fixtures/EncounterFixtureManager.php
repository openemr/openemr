<?php

/**
 * EncounterFixtureManager.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Fixtures;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\Logging\SystemLogger;

class EncounterFixtureManager extends BaseFixtureManager
{
    private $facilityFixture;
    private $patientFixtureManager;

    public function __construct(FacilityFixtureManager $facilityFixtureManager = null, FixtureManager $patientFixtureManager = null)
    {
        parent::__construct("encounters.json", "form_encounter");
        if (isset($facilityFixtureManager)) {
            $this->facilityFixture = $facilityFixtureManager;
        } else {
            $this->facilityFixture = new FacilityFixtureManager();
        }
        if (isset($patientFixtureManager)) {
            $this->patientFixtureManager = $patientFixtureManager;
        } else {
            $this->patientFixtureManager = new FixtureManager();
        }
    }

    public function installFixtures()
    {
        $this->patientFixtureManager->installPatientFixtures();
        $this->facilityFixture->installFacilityFixtures();
        return parent::installFixtures();
    }

    protected function removeInstalledFixtures()
    {
        $sql = "DELETE FROM form_encounter WHERE reason LIKE 'test-fixture-%'";
        try {
            QueryUtils::sqlStatementThrowException($sql, []);
        } catch (SqlQueryException $exception) {
            (new SystemLogger())->error("Failed to delete form_encounter data ", ['message' => $exception, 'trace' => $exception->getTraceAsString()]);
            throw $exception;
        } finally {
            $this->patientFixtureManager->removePatientFixtures();
            $this->facilityFixture->removeFixtures();
        }
    }
}
