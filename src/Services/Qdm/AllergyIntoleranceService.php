<?php

namespace OpenEMR\Services\Qdm;

use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Cqm\Qdm\AllergyIntolerance;
use OpenEMR\Cqm\Qdm\BaseTypes\Interval;
use OpenEMR\Services\AllergyIntoleranceService as BaseService;
use OpenEMR\Services\Qdm\Interfaces\MakesQdmModelInterface;
use OpenEMR\Services\Search\TokenSearchField;

class AllergyIntoleranceService extends BaseService implements MakesQdmModelInterface
{
    public function fetchAllByPid($pid)
    {
        $result = sqlQuery("SELECT uuid from patient_data where pid = ?", [$pid]);
        $uuid = UuidRegistry::uuidToString($result['uuid']);
        $processingResult = $this->getAll(['puuid' => new TokenSearchField('puuid', $uuid, true)]);
        $records = $processingResult->getData();
        return $records;
    }

    public function makeQdmModel(array $record)
    {
        $qdmRecord = null;
        if ($record['type'] === 'allergy') {
            $qdmRecord = new AllergyIntolerance([
                'prevalencePeriod' => new Interval([
                    'low' => $record['begdate'],
                    'high' => $record['enddate'],
                    'lowClosed' => $record['begdate'] ? true : false,
                    'highClosed' => $record['enddate'] ? true : false
                ])
            ]);
        }

        return $qdmRecord;
    }
}
