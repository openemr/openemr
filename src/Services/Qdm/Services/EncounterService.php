<?php

namespace OpenEMR\Services\Qdm\Services;

use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Cqm\Qdm\BaseTypes\DateTime;
use OpenEMR\Cqm\Qdm\BaseTypes\Interval;
use OpenEMR\Cqm\Qdm\BaseTypes\Quantity;
use OpenEMR\Cqm\Qdm\EncounterPerformed;
use OpenEMR\Services\Search\TokenSearchField;

class EncounterService extends AbstractQdmService
{
    public function getSqlStatement()
    {
        $sql = "SELECT
                    FE.encounter,
                    FE.pid,
                    FE.date,
                    FE.encounter_type_code,
                    C.pc_duration
                FROM form_encounter FE
                LEFT JOIN openemr_postcalendar_categories C
                ON FE.pc_catid = C.pc_catid";

        return $sql;
    }


    public function makeQdmModel(array $record)
    {
        // Convert the encounter datetime into a DateTime Object so we can calculate end time based on appt category duration
        $start = \DateTime::createFromFormat('Y-m-d H:i:s', $record['date']);
        $end = $start->add(new DateInterval('PT' . $record['pc_duration']));

        // Get the difference in days for the length of stay (will usually be 0)
        // Format string "%a" is literal days https://www.php.net/manual/en/dateinterval.format.php
        $days = $end->diff($start)->format("%a");

        $qdmRecord = new EncounterPerformed([
            'relevantPeriod' => new Interval([
                'low' =>  new DateTime([
                    'date' => $start->format('Y-m-d H:i:s')
                ]),
                'high' => new DateTime([
                    'date' => $end->format('Y-m-d H:i:s')
                ]),
                'lowClosed' => $record['date'] ? true : false,
                'highClosed' => $record['date'] ? true : false
            ]),
            'admissionSource' => null,
            'dischargeDisposition' => null,
            'facilityLocations' => [],
            'lengthOfStay' => new Quantity([
                'value' => $days,
                'unit' => 'd'
            ]),
            'negationRationale' => null,
            'diagnosis' => null
        ]);

        $codes = $this->explodeAndMakeCodeArray($record['encounter_type_code']);
        foreach ($codes as $code) {
            $qdmRecord->addCode($code);
        }

        return $qdmRecord;
    }
}
