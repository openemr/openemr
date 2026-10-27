<?php

/**
 * @package OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qdm\Services;

use OpenEMR\Cqm\Qdm\InterventionOrder;
use OpenEMR\Services\Qdm\Interfaces\QdmServiceInterface;

class InterventionOrderedService extends AbstractCarePlanService implements QdmServiceInterface
{
    public function getCarePlanType(): string
    {
        return AbstractCarePlanService::CARE_PLAN_TYPE_INTERVENTION;
    }

    public function getModelClass(): string
    {
        return InterventionOrder::class;
    }
}
