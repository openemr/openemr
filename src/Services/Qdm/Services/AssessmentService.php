<?php

/**
 * @package OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qdm\Services;

use OpenEMR\Cqm\Qdm\AssessmentPerformed;
use OpenEMR\Cqm\Qdm\BaseTypes\Code;
use OpenEMR\Cqm\Qdm\BaseTypes\DateTime;
use OpenEMR\Cqm\Qdm\Diagnosis;
use OpenEMR\Services\Qdm\Interfaces\QdmServiceInterface;

class AssessmentService extends AbstractQdmService implements QdmServiceInterface
{
    /**
     * Return the SQL query string that will retrieve these record types from the OpenEMR database
     *
     * @return string
     */
    public function getSqlStatement()
    {
        $sql = "SELECT pid, encounter, `date`, code, code_type, ob_value, description, ob_code, ob_type, ob_status, ob_reason_code
                FROM form_observation
                WHERE ob_type = 'assessment'
                ";
        return $sql;
    }

    /**
     * Map an OpenEMR record into a QDM model
     *
     * @param array $record
     * @return Diagnosis|null
     * @throws \Exception
     */
    public function makeQdmModel(array $record)
    {
        $qdmModel = new AssessmentPerformed([
            'relevantDatetime' => new DateTime([
                'date' => $record['date']
            ]),
            'authorDatetime' => new DateTime([
                'date' => $record['date']
            ]),
            'result' => $this->makeQdmCode($record['ob_code']),
            'negationRationale' => $this->makeQdmCode($record['ob_reason_code'])
        ]);

        $codes = $this->explodeAndMakeCodeArray($record['code']);
        foreach ($codes as $code) {
            $qdmModel->addCode($code);
        }

        return $qdmModel;
    }
}
