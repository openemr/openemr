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
use OpenEMR\Cqm\Qdm\DiagnosticStudyPerformed;
use OpenEMR\Services\Qdm\Interfaces\QdmServiceInterface;

class DiagnosticStudyService extends AbstractQdmService implements QdmServiceInterface
{
    public function getSqlStatement()
    {
        $sql = "SELECT pid, `date`, encounter, code, code_type, ob_value, description, ob_code, ob_type, ob_status
                FROM form_observation
                WHERE ob_type = 'procedure_diagnostic'
                ";
        return $sql;
    }

    public function makeQdmModel(array $record)
    {
        $qdmModel = new DiagnosticStudyPerformed([
            'relevantDatetime' => new DateTime([
                'date' => $record['date']
            ]),
            'authorDatetime' => new DateTime([
                'date' => $record['date']
            ]),
            'result' => $this->makeQdmCode($record['ob_code'])
        ]);

        $codes = $this->explodeAndMakeCodeArray($record['code']);
        foreach ($codes as $code) {
            $qdmModel->addCode($code);
        }

        return $qdmModel;
    }
}
