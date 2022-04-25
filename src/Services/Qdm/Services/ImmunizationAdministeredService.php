<?php

/**
 * @package   OpenEMR
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
use OpenEMR\Services\Qdm\QdmRecord;

class ImmunizationAdministeredService extends AbstractQdmService implements QdmServiceInterface
{
    public function getPatientIdColumn()
    {
        return 'patient_id';
    }

    public function getSqlStatement()
    {
        $sql = "SELECT patient_id, patient_id AS pid, administered_date, cvx_code, reason_code, reason_status
                FROM immunizations";
        return $sql;
    }

    public function makeQdmModel(QdmRecord $recordObj)
    {
        $record = $recordObj->getData();
        $model = new ImmunizationAdministered([
            'relevantDatetime' => new DateTime([
                'date' => $record['administered_date']
            ]),
            'authorDatetime' => new DateTime([
                'date' => $record['administered_date']
            ]),
        ]);

        // If the reason status is "negated" then add the code to negation rationale, otherwise add to reason
        if (!empty($record['reason_code'])) {
            if ($record['reason_status'] == parent::NEGATED) {
                $model->negationRationale = $this->makeQdmCode($record['reason_code']);
            } else {
                $model->reason = $this->makeQdmCode($record['reason_code']);
            }
        }

        if (str_starts_with($record['cvx_code'], 'OID:')) {
            // Sometimes codes are nulled out and sdc:valueSet is in the code, like this:
            // <code nullFlavor="NA" sdtc:valueSet="2.16.840.1.113883.3.526.3.1254"/>
            $model->addCode(
                new Code([
                    'code' => str_replace('OID:', '', $record['cvx_code']),
                    'system' => null
                ])
            );
        } else {
            $model->addCode(
                new Code([
                    'code' => $record['cvx_code'],
                    'system' => $this->getSystemForCodeType('CVX')
                ])
            );
        }

        return $model;
    }
}
