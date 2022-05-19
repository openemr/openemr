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
use OpenEMR\Cqm\Qdm\LaboratoryTestPerformed;
use OpenEMR\Services\Qdm\Interfaces\QdmServiceInterface;
use OpenEMR\Services\Qdm\QdmRecord;

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
                    RES.units,
                    RES.date
                FROM procedure_result RES
                    JOIN procedure_report REP ON RES.procedure_report_id = REP.procedure_report_id
                    JOIN procedure_order O ON REP.procedure_order_id = O.procedure_order_id
                    JOIN procedure_order_code OC ON O.procedure_order_id = OC.procedure_order_id
                WHERE O.procedure_order_type = 'laboratory_test'
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

        $qdmModel = new LaboratoryTestPerformed([
            'relevantDatetime' => new DateTime([
                'date' => $record['date']
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
