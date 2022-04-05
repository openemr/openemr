<?php

/**
 * @package OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qdm\Services;

use OpenEMR\Cqm\Qdm\BaseTypes\DateTime;
use OpenEMR\Cqm\Qdm\InterventionOrder;
use OpenEMR\Services\Qdm\Interfaces\QdmServiceInterface;

class InterventionOrderedService extends AbstractCarePlanService implements QdmServiceInterface
{
    public function getCarePlanType()
    {
        return AbstractCarePlanService::CARE_PLAN_TYPE_INTERVENTION;
    }

    public function makeQdmModel(array $record)
    {
        $model = new InterventionOrder([
            'authorDatetime' => new DateTime(
                [
                    'date' => $record['date']
                ]
            ),
        ]);

        $model->addCode($this->makeQdmCode($record['code']));

        return $model;
    }
}
