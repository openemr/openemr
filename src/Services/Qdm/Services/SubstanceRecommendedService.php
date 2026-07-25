<?php

/**
 * @package OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qdm\Services;

use OpenEMR\Cqm\Qdm\SubstanceRecommended;
use OpenEMR\Services\Qdm\Interfaces\QdmServiceInterface;

class SubstanceRecommendedService extends AbstractCarePlanService implements QdmServiceInterface
{
    public function getCarePlanType(): string
    {
        return AbstractCarePlanService::CARE_PLAN_TYPE_MEDICATION;
    }

    public function getModelClass(): string
    {
        return SubstanceRecommended::class;
    }
}
