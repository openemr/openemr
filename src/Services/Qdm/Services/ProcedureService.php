<?php

/**
 * @package OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qdm\Services;

use OpenEMR\Cqm\Qdm\BaseTypes\DateTime;
use OpenEMR\Cqm\Qdm\InterventionPerformed;
use OpenEMR\Cqm\Qdm\ProcedurePerformed;
use OpenEMR\Services\Qdm\Interfaces\QdmServiceInterface;

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
                    RES.date AS result_date,
                    RES.result_code,
                    RES.result_units,
                    RES.units as result_value
                FROM procedure_order O
                    JOIN procedure_order_code OC ON O.procedure_order_id = OC.procedure_order_id
                    JOIN procedure_order_report REP ON O.procedure_order_id = REP.procedure_order_id
                    JOIN procedure_order_result RES ON REP.procedure_report_id = RES.procedure_report_id
                WHERE O.procedure_order_type = 'order'
                ";

        return $sql;
    }

    public function getPatientIdColumn()
    {
        return 'O.patient_id';
    }

    public function makeQdmModel(array $record)
    {
        $qdmModel = new ProcedurePerformed([
            'relevantDatetime' => new DateTime([
                'date' => $record['date_ordered']
            ]),
            'result' => new Quantity([
                'value' => $record['result_value'],
                'unit' => $record['result_units']
            ]),
        ]);

        $codes = $this->explodeAndMakeCodeArray($record['procedure_code']);
        foreach ($codes as $code) {
            $qdmModel->addCode($code);
        }

        return $qdmModel;
    }
}
