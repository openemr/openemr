<?php

namespace OpenEMR\Services\Qdm;

use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Cqm\Qdm\BaseTypes\Interval;
use OpenEMR\Cqm\Qdm\EncounterPerformed;
use OpenEMR\Services\EncounterService as BaseService;
use OpenEMR\Services\Qdm\Interfaces\MakesQdmModelInterface;
use OpenEMR\Services\Search\TokenSearchField;

class EncounterService extends BaseService implements MakesQdmModelInterface
{
    public function fetchAllByPid($pid)
    {
        $result = sqlQuery("SELECT uuid from patient_data where pid = ?", [$pid]);
        $uuid = UuidRegistry::uuidToString($result['uuid']);
        $processingResult = $this->search(['puuid' => new TokenSearchField('puuid', $uuid, true)], true, false);
        $records = $processingResult->getData();
        return $records;
    }


    public function makeQdmModel(array $record)
    {
        $qdmRecord = new EncounterPerformed([
            'id' => $record['euuid'],
            'relevantPeriod' => new Interval([
                'low' => $record['date'],
                'high' => $record['date'],
                'lowClosed' => $record['date'] ? true : false,
                'highClosed' => $record['date'] ? true : false
            ]),
            // TODO figure out what the code and system are for these, CPT codes?
            'dataElementCodes' => [
                'code' => '424589009',
                'system' => '2.16.840.1.113883.6.96'
            ],
            'admissionSource' => null,
            'dischargeDisposition' => null,
            'facilityLocations' => [],
            'lengthOfStay' => new Interval(),
            'negationRationale' => null,
           //'principalDiagnosis' => null
        ]);

        return $qdmRecord;
    }
}
