<?php

/**
 * CarePlanFixtureManager.php
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

class CarePlanFixtureManager extends BaseFixtureManager
{
    // use a prefix so we can easily remove fixtures
    const FIXTURE_PREFIX = "test-fixture";

    private $encounterFixtureManager;

    public function __construct()
    {
        $patientFixtureManager = new FixtureManager();
        $this->encounterFixtureManager = new EncounterFixtureManager(null, $patientFixtureManager);
        parent::__construct("care-plan.php", "form_care_plan");
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
        $carePlanIds = $this->getInstalledFixtureIds('form_care_plan');
        try {
            if ($carePlanIds !== []) {
                $placeholders = implode(',', array_fill(0, count($carePlanIds), '?'));
                QueryUtils::sqlStatementThrowException(
                    "DELETE FROM form_care_plan WHERE id IN ($placeholders)",
                    $carePlanIds
                );
            }
        } catch (SqlQueryException $exception) {
            ServiceContainer::getLogger()->error("Failed to delete form_care_plan data ", ['message' => $exception, 'trace' => $exception->getTraceAsString()]);
            throw $exception;
        } finally {
            $this->encounterFixtureManager->removeFixtures();
        }
    }
}
