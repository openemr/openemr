<?php

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qdm\Services;

use OpenEMR\Cqm\Qdm\BaseTypes\DateTime;
use OpenEMR\Cqm\Qdm\BaseTypes\Interval;
use OpenEMR\Cqm\Qdm\MedicationOrder;
use OpenEMR\Services\Qdm\Interfaces\QdmServiceInterface;

class MedicationOrderService extends AbstractCarePlanService implements QdmServiceInterface
{
    public function getCarePlanType()
    {
        return AbstractCarePlanService::CARE_PLAN_TYPE_PLANNED_MED_ACTIVITY;
    }

    public function getModelClass()
    {
        return MedicationOrder::class;
    }

    public function makeQdmModel(array $record)
    {
        $model = parent::makeQdmModel($record);

        // The medication order has an additional field for relevantPeriod that is not in the parent
        $model->relevantPeriod = new Interval([
            'low' =>  new DateTime([
                'date' => $record['date']
            ]),
            'high' => new DateTime([
                'date' => $record['date']
            ]),
            'lowClosed' => $record['date'] ? true : false,
            'highClosed' => $record['date'] ? true : false
        ]);

        return $model;
    }
}
