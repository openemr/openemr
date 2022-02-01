<?php

/**
 * @package OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qdm\Services;

use OpenEMR\Cqm\Qdm\AllergyIntolerance;
use OpenEMR\Cqm\Qdm\BaseTypes\Interval;
use OpenEMR\Services\Qdm\Interfaces\QdmServiceInterface;

class AllergyIntoleranceService extends AbstractQdmService implements QdmServiceInterface
{
    public function getSqlStatement()
    {
        $sql = "SELECT pid, begdate, enddate, `date`, diagnosis
                FROM lists
                WHERE type = 'allergy'
                ";
        return $sql;
    }

    public function makeQdmModel(array $record)
    {
        $qdmModel = new AllergyIntolerance([
            'prevalencePeriod' => new Interval([
                'low' => $record['begdate'],
                'high' => $record['enddate'],
                'lowClosed' => $record['begdate'] ? true : false,
                'highClosed' => $record['enddate'] ? true : false
            ])
        ]);

        $codes = $this->explodeAndMakeCodeArray($record['diagnosis']);
        foreach ($codes as $code) {
            $qdmModel->addCode($code);
        }

        return $qdmModel;
    }
}
