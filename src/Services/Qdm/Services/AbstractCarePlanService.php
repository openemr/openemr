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
    /**
     * Care Plan Types that map the care plan type in the form_care_plan.care_plan_type field to their QDM models
     */
    const CARE_PLAN_TYPE_TEST_OR_ORDER = 'test_or_order'; // for LaboratoryTestOrderedService
    const CARE_PLAN_TYPE_PLAN_OF_CARE = 'plan_of_care'; // for DiagnosticStudyOrderedService
    const CARE_PLAN_TYPE_INTERVENTION = 'intervention'; // for InterventionOrderedService
    const CARE_PLAN_TYPE_PLANNED_MED_ACTIVITY = 'planned_medication_activity'; // for MedicationOrderService
    const CARE_PLAN_TYPE_MEDICATION = 'medication'; // for SubstanceRecommendedService
    const CARE_PLAN_TYPE_PROCEDURE_REC = 'procedure'; // for ProcedureRecommendedService

    abstract public function getCarePlanType();

    public function getSqlStatement()
    {
        $carePlanType = $this->getCarePlanType();
        return "SELECT pid, `date`, code, codetext, description, care_plan_type, reason_code
            FROM form_care_plan
            WHERE care_plan_type = '" . add_escape_custom($carePlanType) . "'";
    }
}
