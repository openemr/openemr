<?php


namespace OpenEMR\Services\Qdm\Services;


use OpenEMR\Cqm\Qdm\BaseTypes\DateTime;
use OpenEMR\Cqm\Qdm\LaboratoryTestPerformed;
use OpenEMR\Services\Qdm\Interfaces\QdmServiceInterface;

class LaboratoryTestService extends AbstractQdmService implements QdmServiceInterface
{
    public function getSqlStatement()
    {
        $sql = "SELECT
                    O.patient_id AS pid,
                    O.encounter_id AS encounter,
                    O.procedure_order_type,
                    O.date_ordered,
                    OC.procedure_code,
                    RES.result_code,
                    RES.result,
                    RES.date
                FROM procedure_result RES
                    JOIN procedure_report REP ON RES.procedure_report_id = REP.procedure_report_id
                    JOIN procedure_order O ON REP.procedure_order_id = O.procedure_order_id
                    JOIN procedure_order_code OC ON O.procedure_order_id = OC.procedure_order_id
                WHERE O.procedure_order_type = 'laboratory_test'
                ";

        return $sql;
    }

    public function makeQdmModel(array $record)
    {
        $qdmModel = new LaboratoryTestPerformed([
            '_pid' => $record['pid'],
            '_encounter' => $record['encounter'],
            'relevantDatetime' => new DateTime([
                'date' => $record['date']
            ]),
            'result' => !empty($record['result']) ? $record['result'] : 'Negative',
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
