<?php

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qdm\Services;

use OpenEMR\Cqm\Qdm\BaseTypes\Code;
use OpenEMR\Cqm\Qdm\BaseTypes\DateTime;
use OpenEMR\Cqm\Qdm\BaseTypes\Interval;
use OpenEMR\Cqm\Qdm\SubstanceOrder;
use OpenEMR\Services\Qdm\Interfaces\QdmServiceInterface;
use OpenEMR\Services\Qdm\QdmRecord;

class SubstanceOrderService extends AbstractCarePlanService implements QdmServiceInterface
{
    public function getCarePlanType()
    {
        return AbstractCarePlanService::CARE_PLAN_TYPE_PLANNED_MED_ACTIVITY;
    }

    public function getModelClass()
    {
        return SubstanceOrder::class;
    }

    public function makeQdmModel(QdmRecord $recordObj)
    {
        $model = parent::makeQdmModel($recordObj);
        $record = $recordObj->getData();

        // The medication order has an additional field for relevantPeriod that is not in the parent
        $model->relevantPeriod = new Interval([
            'low' =>  new DateTime([
                'date' => $record['date']
            ]),
            'high' => new DateTime([
                'date' => $record['date_end']
            ]),
            'lowClosed' => $record['date'] ? true : false,
            'highClosed' => $this->validDateOrNull($record['date_end']) ? true : false,
        ]);

        $model->authorDatetime = new DateTime([
            'date' => $record['date']
        ]);

        $model->frequency = new Code([
            "code" => "396125000",
            "system" => "2.16.840.1.113883.6.96",
        ]);

        return $model;
    }
}
