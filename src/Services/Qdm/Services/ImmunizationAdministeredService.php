<?php

/**
 * @package OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qdm\Services;

use OpenEMR\Cqm\Qdm\BaseTypes\Code;
use OpenEMR\Cqm\Qdm\BaseTypes\DateTime;
use OpenEMR\Cqm\Qdm\ImmunizationAdministered;
use OpenEMR\Services\Qdm\Interfaces\QdmServiceInterface;

class ImmunizationAdministeredService extends AbstractQdmService implements QdmServiceInterface
{
    public function getPatientIdColumn()
    {
        return 'patient_id';
    }

    public function getSqlStatement()
    {
        $sql = "SELECT patient_id, patient_id AS pid, administered_date, cvx_code FROM immunizations";
        return $sql;
    }

    public function makeQdmModel(array $record)
    {
        $model = new ImmunizationAdministered([
            'relevantDatetime' => new DateTime([
                'date' => $record['administered_date']
            ]),
            'authorDatetime' => new DateTime([
                'date' => $record['administered_date']
            ]),
        ]);

        $model->addCode(new Code([
            'code' => $record['cvx_code'],
            'system' => $this->getSystemForCodeType('CVX')
        ]));

        return $model;
    }
}
