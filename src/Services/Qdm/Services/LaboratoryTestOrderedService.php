<?php

/**
 * @package OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qdm\Services;

use OpenEMR\Cqm\Qdm\LaboratoryTestOrder;
use OpenEMR\Services\Qdm\Interfaces\QdmServiceInterface;

class LaboratoryTestOrderedService extends AbstractCarePlanService implements QdmServiceInterface
{
    public function getCarePlanType()
    {
        return AbstractCarePlanService::CARE_PLAN_TYPE_TEST_OR_ORDER;
    }

    public function getModelClass()
    {
        return LaboratoryTestOrder::class;
    }
}
