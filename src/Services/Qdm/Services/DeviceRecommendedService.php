<?php

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qdm\Services;

use OpenEMR\Cqm\Qdm\DeviceRecommended;
use OpenEMR\Services\Qdm\Interfaces\QdmServiceInterface;

class DeviceRecommendedService extends AbstractCarePlanService implements QdmServiceInterface
{
    public function getCarePlanType()
    {
        return parent::CARE_PLAN_TYPE_DEVICE_RECOMMENDED;
    }

    public function getModelClass()
    {
        return DeviceRecommended::class;
    }
}
