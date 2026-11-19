<?php

/**
 * EncounterFixtureManager.php
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Fixtures;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;

class EncounterFixtureManager extends BaseFixtureManager
{
    private $facilityFixture;
    private $patientFixtureManager;

    public function __construct(?FacilityFixtureManager $facilityFixtureManager = null, ?FixtureManager $patientFixtureManager = null)
    {
        parent::__construct("encounters.php", "form_encounter");
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
        // Each test-fixture encounter comes with a companion forms.newpatient
        // row addForm()s whenever the encounter was inserted through
        // EncounterService::insertEncounter (the FHIR + REST write paths).
        // The forms row joins form_encounter on the `encounter` number, so
        // the forms delete must happen BEFORE the form_encounter delete —
        // once form_encounter is gone the JOIN returns nothing and the
        // companion forms row orphans.
        //
        // The fixtures register each encounter's uuid (uuid('form_encounter')
        // in encounters.php), so the uuid_registry delete reads the uuid
        // through a subselect on form_encounter and must also run before
        // the form_encounter delete.
        $uuidSql = "DELETE FROM uuid_registry WHERE table_name = 'form_encounter' AND uuid IN "
            . "(SELECT uuid FROM form_encounter WHERE reason LIKE 'test-fixture-%')";
        $formsSql = "DELETE f FROM forms f"
            . " JOIN form_encounter fe ON fe.encounter = f.encounter"
            . " WHERE fe.reason LIKE 'test-fixture-%' AND f.formdir = 'newpatient'";
        $sql = "DELETE FROM form_encounter WHERE reason LIKE 'test-fixture-%'";
        try {
            QueryUtils::sqlStatementThrowException($uuidSql, []);
            QueryUtils::sqlStatementThrowException($formsSql, []);
            QueryUtils::sqlStatementThrowException($sql, []);
        } catch (SqlQueryException $exception) {
            ServiceContainer::getLogger()->error("Failed to delete form_encounter data ", ['message' => $exception, 'trace' => $exception->getTraceAsString()]);
            throw $exception;
        } finally {
            $this->patientFixtureManager->removePatientFixtures();
            $this->facilityFixture->removeFixtures();
        }
    }
}
