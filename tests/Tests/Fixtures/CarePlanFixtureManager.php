<?php

/**
 * CarePlanFixtureManager.php
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
use OpenEMR\Common\Uuid\UuidRegistry;
use Ramsey\Uuid\Uuid;

class CarePlanFixtureManager extends BaseFixtureManager
{
    // use a prefix so we can easily remove fixtures
    const FIXTURE_PREFIX = "test-fixture";

    private $encounterFixtureManager;

    public function __construct()
    {
        $patientFixtureManager = new FixtureManager();
        $this->encounterFixtureManager = new EncounterFixtureManager(null, $patientFixtureManager);
        parent::__construct("care-plan.json", "form_care_plan");
    }

    public function installFixtures()
    {
        // if we fail, attempt to remove everything
        try {
            $this->encounterFixtureManager->installFixtures();
            parent::installFixtures();
        } catch (SqlQueryException $exception) {
            $this->removeInstalledFixtures();
            throw $exception;
        }
    }

    protected function removeInstalledFixtures()
    {
        $sql = "DELETE FROM form_care_plan WHERE description LIKE '" . self::FIXTURE_PREFIX . "%'";
        try {
            QueryUtils::sqlStatementThrowException($sql, []);
        } catch (SqlQueryException $exception) {
            (new SystemLogger())->error("Failed to delete form_care_plan data ", ['message' => $exception, 'trace' => $exception->getTraceAsString()]);
            throw $exception;
        } finally {
            $this->encounterFixtureManager->removeFixtures();
        }
    }
}
