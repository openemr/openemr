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
use OpenEMR\Cqm\Qdm\LaboratoryTestOrder;
use OpenEMR\Services\Qdm\Interfaces\QdmServiceInterface;

class LaboratoryTestOrderedService extends AbstractCarePlanService implements QdmServiceInterface
{
    public function getCarePlanType()
    {
        return AbstractCarePlanService::CARE_PLAN_TYPE_TEST_OR_ORDER;
    }

    public function makeQdmModel(array $record)
    {
        $model = new LaboratoryTestOrder([
            'authorDatetime' => new DateTime(
                [
                    'date' => $record['date']
                ]
            ),
        ]);

        // If there is a reason noted why this plan was NOT done, add a negation
        if (!empty($record['reason_code'])) {
            $model->negationRationale = $this->makeQdmCode($record['reason_code']);
        } else {
            // Only add the code if there's not a negation reason
            $model->addCode($this->makeQdmCode($record['code']));
        }

        return $model;
    }
}
