<?php

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2022 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qdm\Services;

use OpenEMR\Cqm\Qdm\AssessmentPerformed;
use OpenEMR\Cqm\Qdm\BaseTypes\{
    DateTime,
    Interval
};
use OpenEMR\Services\Qdm\Interfaces\QdmServiceInterface;
use OpenEMR\Services\Qdm\QdmRecord;

class AssessmentService extends AbstractObservationService implements QdmServiceInterface
{
    public function getObservationType()
    {
        return parent::OB_TYPE_ASSESSMENT;
    }

    public function getModelClass()
    {
        return AssessmentPerformed::class;
    }

    /**
     * @param array $record
     * @return mixed
     * @throws \Exception
     *
     * Map an OpenEMR record into a QDM model
     */
    public function makeQdmModel(QdmRecord $recordObj)
    {
        $record = $recordObj->getData();
        $modelClass = $this->getModelClass();
        $id = parent::convertToObjectIdBSONFormat($recordObj->getEntityCount());
        $qdmModel = new $modelClass([
            '_id' => $id,
            'id' => $id,
            'relevantPeriod' => new Interval([
                'low' => new DateTime([
                    'date' => $record['date']
                ]),
                'high' => null, // TODO We don't have an end-date for assessment?,
                'lowClosed' => $record['date'] ? true : false,
                'highClosed' => false
            ]),
            'authorDatetime' => new DateTime([
                'date' => $record['date']
            ]),
        ]);

        $qdmModel->result = $this->makeResult($record);

        // If the reason status is "negated" then add the code to negation rationale, otherwise add to reason
        if (!empty($record['ob_reason_code'])) {
            if ($record['ob_reason_status'] == parent::NEGATED) {
                $qdmModel->negationRationale = $this->makeQdmCode($record['ob_reason_code']);
            } else {
                $qdmModel->reason = $this->makeQdmCode($record['ob_reason_code']);
            }
        }

        $codes = $this->explodeAndMakeCodeArray($record['code']);
        foreach ($codes as $code) {
            $qdmModel->addCode($code);
        }

        return $qdmModel;
    }
}
