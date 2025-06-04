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
use OpenEMR\Cqm\Qdm\DeviceApplied;
use OpenEMR\Services\Qdm\Interfaces\QdmServiceInterface;
use OpenEMR\Services\Qdm\QdmRecord;

class DeviceAppliedService extends AbstractQdmService implements QdmServiceInterface
{
    public function getSqlStatement()
    {
        $sql = "SELECT
                    O.patient_id AS pid,
                    O.encounter_id AS encounter,
                    O.procedure_order_type,
                    O.date_ordered,
                    OC.procedure_code
                FROM procedure_order O
                    JOIN procedure_order_code OC ON O.procedure_order_id = OC.procedure_order_id
                WHERE O.procedure_order_type = 'device'
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
        $qdmModel = new DeviceApplied([
            '_id' => $id,
            'id' => $id,
            'relevantDatetime' => new DateTime([
                'date' => $record['date_ordered']
            ])
        ]);

        $codes = $this->explodeAndMakeCodeArray($record['procedure_code']);
        foreach ($codes as $code) {
            $qdmModel->addCode($code);
        }

        return $qdmModel;
    }
}
