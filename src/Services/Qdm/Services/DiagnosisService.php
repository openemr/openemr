<?php

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qdm\Services;

use OpenEMR\Cqm\Qdm\BaseTypes\AbstractType;
use OpenEMR\Cqm\Qdm\BaseTypes\Code;
use OpenEMR\Cqm\Qdm\BaseTypes\DateTime;
use OpenEMR\Cqm\Qdm\Diagnosis;
use OpenEMR\Cqm\Qdm\BaseTypes\Interval;
use OpenEMR\Services\Qdm\Interfaces\QdmServiceInterface;
use OpenEMR\Services\Qdm\QdmRecord;

class DiagnosisService extends AbstractQdmService implements QdmServiceInterface
{
    public function getSqlStatement()
    {
        $sql = "SELECT pid, begdate, enddate, `date`, diagnosis
                FROM lists
                WHERE `type` = 'medical_problem' AND subtype = 'diagnosis'
                ";
        return $sql;
    }

    public function makeQdmModel(QdmRecord $recordObj)
    {
        $record = $recordObj->getData();
        $qdmModel = new Diagnosis([
            'prevalencePeriod' => new Interval([
                'low' => $this->validDateOrNull($record['begdate']),
                'high' => $this->validDateOrNull($record['enddate']),
                'lowClosed' => $this->validDateOrNull($record['begdate']) ? true : false,
                'highClosed' => $this->validDateOrNull($record['enddate']) ? true : false
            ])
        ]);

        $codes = $this->explodeAndMakeCodeArray($record['diagnosis']);
        foreach ($codes as $code) {
            $qdmModel->addCode($code);
        }

        return $qdmModel;
    }
}
