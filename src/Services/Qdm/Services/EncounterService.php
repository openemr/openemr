<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Qdm\Services;

use OpenEMR\Cqm\Qdm\BaseTypes\DateTime;
use OpenEMR\Cqm\Qdm\BaseTypes\Interval;
use OpenEMR\Cqm\Qdm\BaseTypes\Quantity;
use OpenEMR\Cqm\Qdm\DiagnosisComponent;
use OpenEMR\Cqm\Qdm\EncounterPerformed;
use OpenEMR\Services\Qdm\Interfaces\QdmServiceInterface;
use OpenEMR\Services\Qdm\QdmRecord;

class EncounterService extends AbstractQdmService implements QdmServiceInterface
{
    public function getSqlStatement(): string
    {
        // Get the encounter, and also collect the duration of the encounter using the appointment category.
        // One row per visit, carrying the diagnoses of every problem of the visit's patient linked to it that
        // counts, joined with ';' as explodeAndMakeCodeArray() splits them (one problem's diagnosis can hold
        // several that way). The link and the problem are both matched on the visit's patient: issue_encounter
        // has no foreign key, and its writers don't check whose problem a posted issue id names.
        $sql = "SELECT
                    FE.encounter,
                    FE.pid,
                    FE.date,
                    FE.date_end,
                    FE.encounter_type_code,
                    FE.discharge_disposition,
                    (
                        SELECT GROUP_CONCAT(LISTS.diagnosis ORDER BY LISTS.id SEPARATOR ';')
                        FROM issue_encounter IE
                        JOIN lists LISTS ON IE.list_id = LISTS.id AND LISTS.pid = IE.pid
                        WHERE IE.encounter = FE.encounter AND IE.pid = FE.pid AND " . self::countedListEntry('LISTS') . "
                    ) AS diagnosis,
                    L.codes AS discharge_dispo_code,
                    C.pc_duration
                FROM form_encounter FE
                LEFT JOIN openemr_postcalendar_categories C ON FE.pc_catid = C.pc_catid
                LEFT JOIN list_options L ON FE.discharge_disposition = L.option_id AND L.list_id = 'discharge-disposition'
                ";

        return $sql;
    }

    public function makeQdmModel(QdmRecord $recordObj): EncounterPerformed
    {
        $record = $recordObj->getData();
        // Convert the encounter datetime into a DateTime Object so we can calculate end time based on encounter end date
        $start_tmp = \DateTime::createFromFormat('Y-m-d H:i:s', $record['date']);
        // DateTime->modify() modifies the calling object, so we need to copy our start date
        $start = clone $start_tmp;
        // The encounter form saves no end time; such a visit ends when it starts, so it falls "during" the
        // day it happened. Left unknown, it would be during no period at all (#14526).
        $end = empty($record['date_end']) ? clone $start : \DateTime::createFromFormat('Y-m-d H:i:s', $record['date_end']);

        $days = '';
        $end_date = '';
        if (!empty($end)) {
            // Get the difference in days for the length of stay (will usually be 0)
            // Format string "%a" is literal days https://www.php.net/manual/en/dateinterval.format.php
            $days = $end->diff($start)->format("%a");
            $end_date = $end->format('Y-m-d H:i:s');
        }
        $enc_id = self::convertToObjectIdBSONFormat($record['encounter']);
        $qdmRecord = new EncounterPerformed([
            '_id' => $enc_id,
            'id' => $enc_id,
            'relevantPeriod' => new Interval([
                'low' =>  new DateTime([
                    'date' => $start->format('Y-m-d H:i:s')
                ]),
                'high' => new DateTime([
                    'date' => $end_date
                ]),
                'lowClosed' => (bool) $record['date'],
                'highClosed' => $end_date !== ''
            ]),
            'authorDatetime' => new DateTime([
                'date' => $record['date']
            ]),
            'admissionSource' => null,
            'dischargeDisposition' => $this->makeQdmCode($record['discharge_dispo_code']) ?? null,
            'facilityLocations' => [],
            'lengthOfStay' => new Quantity([
                'value' => (int)$days,
                'unit' => 'd'
                ]),
            'negationRationale' => null
        ]);

        $encounter_diagnosis_codes = $this->explodeAndMakeCodeArray($record['diagnosis']);
        foreach ($encounter_diagnosis_codes as $encounter_diagnosis_code) {
            $qdmRecord->diagnoses [] = new DiagnosisComponent([
                'code' => $encounter_diagnosis_code
            ]);
        }

        $codes = $this->explodeAndMakeCodeArray($record['encounter_type_code']);
        foreach ($codes as $code) {
            $qdmRecord->addCode($code);
        }

        return $qdmRecord;
    }
}
