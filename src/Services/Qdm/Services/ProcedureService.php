<?php

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qdm\Services;

use OpenEMR\Cqm\Qdm\BaseTypes\DateTime;
use OpenEMR\Cqm\Qdm\BaseTypes\Quantity;
use OpenEMR\Cqm\Qdm\ProcedurePerformed;
use OpenEMR\Services\Qdm\Interfaces\QdmServiceInterface;
use OpenEMR\Services\Qdm\QdmRecord;

class ProcedureService extends AbstractQdmService implements QdmServiceInterface
{
    public function getSqlStatement()
    {
        $sql = "SELECT
                    O.patient_id AS pid,
                    O.encounter_id AS encounter,
                    O.procedure_order_type,
                    O.date_ordered,
                    OC.procedure_code,
                    OC.reason_status,
                    OC.reason_code,
                    RES.date AS result_date,
                    RES.result_code,
                    RES.units as result_units,
                    RES.result as result_value
                FROM procedure_order O
                    LEFT JOIN procedure_order_code OC ON O.procedure_order_id = OC.procedure_order_id
                    LEFT JOIN procedure_report REP ON O.procedure_order_id = REP.procedure_order_id
                    LEFT JOIN procedure_result RES ON REP.procedure_report_id = RES.procedure_report_id
                WHERE O.procedure_order_type = 'order' AND O.activity != 0
                ";

        return $sql;
    }

    public function getPatientIdColumn()
    {
        return 'O.patient_id';
    }

    public function makeQdmModel(QdmRecord $recordObj)
    {
        $record = $recordObj->getData();
        $id = parent::convertToObjectIdBSONFormat($recordObj->getEntityCount());
        $qdmModel = new ProcedurePerformed([
            '_id' => $id,
            'id' => $id,
            'relevantDatetime' => new DateTime([
                'date' => $record['date_ordered']
            ]),
            'authorDatetime' => new DateTime([
                'date' => $record['date_ordered']
            ])
        ]);

        if (!empty($record['result_value']) && !empty($record['result_units'])) {
            $qdmModel->result = new Quantity([
                'value' => (int)$record['result_value'],
                'unit' => $record['result_units']
            ]);
        }

        if (!empty($record['reason_code'])) {
            if ($record['reason_status'] === parent::NEGATED) {
                $qdmModel->negationRationale = $this->makeQdmCode($record['reason_code']);
            } else {
                $qdmModel->reason = $this->makeQdmCode($record['reason_code']);
            }
        }

        $codes = $this->explodeAndMakeCodeArray($record['procedure_code']);
        foreach ($codes as $code) {
            $qdmModel->addCode($code);
        }

        return $qdmModel;
    }
}
