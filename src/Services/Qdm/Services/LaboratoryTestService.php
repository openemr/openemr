<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Qdm\Services;

use OpenEMR\Cqm\Qdm\BaseTypes\{
    DateTime,
    Interval,
    Quantity
};
use OpenEMR\Cqm\Qdm\LaboratoryTestPerformed;
use OpenEMR\Services\Qdm\Interfaces\QdmServiceInterface;
use OpenEMR\Services\Qdm\QdmRecord;

class LaboratoryTestService extends AbstractQdmService implements QdmServiceInterface
{
    public function getSqlStatement(): string
    {
        // Each result with the code of its own line of the order (procedure_report.procedure_order_seq),
        // as the procedure API's OpenEMR\Services\ProcedureService pairs them. A result, or its report,
        // marked Error is left out: the results screen's statuses (proc_res_status, proc_rep_status), and
        // an HL7 result received with status X.
        $sql = "SELECT
                    O.patient_id AS pid,
                    O.encounter_id AS encounter,
                    O.procedure_order_type,
                    O.date_ordered,
                    OC.procedure_code,
                    RES.result_code,
                    RES.result,
                    RES.units,
                    RES.date,
                    RES.date_end
                FROM procedure_result RES
                    JOIN procedure_report REP ON RES.procedure_report_id = REP.procedure_report_id
                    JOIN procedure_order O ON REP.procedure_order_id = O.procedure_order_id
                    JOIN procedure_order_code OC ON O.procedure_order_id = OC.procedure_order_id
                        AND OC.procedure_order_seq = REP.procedure_order_seq
                WHERE O.procedure_order_type = 'laboratory_test' AND O.activity != 0
                AND RES.result_status <> 'error' AND REP.report_status <> 'error'
                AND " . self::countedFormRow('O.procedure_order_id', 'O.patient_id', QdmForm::ProcedureOrder) . "
                ";

        return $sql;
    }

    public function makeQdmModel(QdmRecord $recordObj): LaboratoryTestPerformed
    {
        $record = $recordObj->getData();
        $result = 'Negative';
        if (
            !empty($record['result'])
            && $record['result'] != 'Negative'
        ) {
            $result = new Quantity([
                'value' => (int)$record['result'],
                'unit' => $record['units']
            ]);
        }
        // A result generally should have a code however,
        // for rare occasions with values such as Negative, schematron expects a code type
        // or will throw a warning. This holds for CDA too.
        if (
            is_string($record['result'] ?? null)
            && $record['result'] == 'Negative'
        ) {
            $result = $this->makeQdmCode('SNOMED-CT:260385009');
        }

        $qdmModel = new LaboratoryTestPerformed([
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
                'lowClosed' => (bool) $record['date'],
                'highClosed' => (bool) $this->validDateOrNull($record['date_end'])
            ]),
            'result' => $result,
            'resultDatetime' => new DateTime([
                'date' => $record['date']
            ])
        ]);

        $codes = $this->explodeAndMakeCodeArray($record['procedure_code']);
        foreach ($codes as $code) {
            $qdmModel->addCode($code);
        }

        return $qdmModel;
    }
}
