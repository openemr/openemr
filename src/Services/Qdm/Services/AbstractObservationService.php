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

use OpenEMR\Cqm\Qdm\BaseTypes\{
    DateTime,
    Interval
};
use OpenEMR\Services\Qdm\Interfaces\QdmServiceInterface;
use OpenEMR\Services\Qdm\QdmRecord;

abstract class AbstractObservationService extends AbstractQdmService implements QdmServiceInterface
{
    /**
     * Types of observations in ob_type field
     */
    public const OB_TYPE_ASSESSMENT = 'assessment';
    public const OB_TYPE_DIAGNOSTIC_STUDY = 'procedure_diagnostic';
    public const OB_TYPE_PHYSICAL_EXAM = 'physical_exam_performed';

    abstract public function getObservationType();

    abstract public function getModelClass();

    /**
     * Return the SQL query string that will retrieve these record types from the OpenEMR database
     *
     * @return string
     */
    public function getSqlStatement()
    {
        $observation_type = add_escape_custom($this->getObservationType());
        $sql = "SELECT `pid`, `encounter`, `date`, `code`, `code_type`, `ob_value`, `ob_unit`,
                `description`, `ob_code`, `ob_type`, `ob_status`,
                `ob_reason_status`, `ob_reason_code`, `date_end`
                FROM `form_observation`
                WHERE `ob_type` = '$observation_type'
                ";
        return $sql;
    }

    public function makeResult($record)
    {
        return $this->makeQdmCode($record['ob_code']);
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
            'relevantDatetime' => new DateTime([
                'date' => $record['date']
            ]),
            'relevantPeriod' => new Interval([
                'low' => new DateTime([
                    'date' => $record['date']
                ]),
                'high' => new DateTime([
                    'date' => $record['date_end'] ?: null
                ]),
                'lowClosed' => $record['date'] ? true : false,
                'highClosed' => $this->validDateOrNull($record['date_end']) ? true : false
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
