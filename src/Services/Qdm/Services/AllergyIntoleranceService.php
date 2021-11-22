<?php

namespace OpenEMR\Services\Qdm\Services;

use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Cqm\Qdm\AllergyIntolerance;
use OpenEMR\Cqm\Qdm\BaseTypes\AbstractType;
use OpenEMR\Cqm\Qdm\BaseTypes\DataElement;
use OpenEMR\Cqm\Qdm\BaseTypes\Interval;
use OpenEMR\Services\Qdm\Interfaces\QdmServiceInterface;
use OpenEMR\Services\Search\TokenSearchField;

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
