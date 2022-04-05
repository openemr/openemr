<?php
/**
 * @package OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qdm\Services;

/**
 * Class AbstractCarePlanService
 * @package OpenEMR\Services\Qdm\Services
 *
 * This class gets data from the form_care_plan which contains plans for
 * interventions, medications, lab tests, etc
 */
abstract class AbstractCarePlanService extends AbstractQdmService
{
    abstract public function getCarePlanType();

    public function getSqlStatement()
    {
        $carePlanType = $this->getCarePlanType();
        return "SELECT pid, `date`, code, codetext, description, care_plan_type
            FROM form_care_plan
            WHERE care_plan_type = '$carePlanType'";
    }
}
