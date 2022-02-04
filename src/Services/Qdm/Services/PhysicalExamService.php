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
use OpenEMR\Cqm\Qdm\BaseTypes\Quantity;
use OpenEMR\Cqm\Qdm\PhysicalExamPerformed;
use OpenEMR\Services\Qdm\Interfaces\QdmServiceInterface;

class PhysicalExamService extends AbstractQdmService implements QdmServiceInterface
{
    protected $code_maping = [
        'bmi' => [
            'code' => '39156-5',
            'system' => '2.16.840.1.113883.6.1'
        ],
        'sbp' => [
            'code' => '8480-6',
            'system' => '2.16.840.1.113883.6.1'
        ],
        'dbp' => [
            'code' => '8462-4',
            'system' => '2.16.840.1.113883.6.1'
        ],
    ];

    public function getSqlStatement()
    {
        // Since the vitals are all stored in the same record, and QDM needs them as separate QDM::PhysicalExamPerformed
        // models, let's pick out the pieces and union our results
        $sql = [];
        $sql[] = "SELECT pid, `date`, bpd AS `value`, 'mm[Hg]' AS unit, 'dbp' AS type FROM form_vitals"; // Diastolic blood pressure
        $sql[] = "SELECT pid, `date`, bps AS `value`, 'mm[Hg]' AS unit, 'sbp' AS type FROM form_vitals"; // Systolic blood pressure
        $sql[] = "SELECT pid, `date`, BMI AS `value`, 'kg/m2' AS unit, 'bmi' AS type FROM form_vitals"; // BMI ratio

        $unions = implode(" UNION ", $sql);
        $fullSql = "SELECT * FROM ( $unions ) AS physical_exams";
        return $fullSql;
    }

    public function makeQdmModel(array $record)
    {
        $model = new PhysicalExamPerformed([
            'relevantDatetime' => new DateTime([
                'date' => $record['date']
            ]),
            'authorDatetime' => new DateTime([
                'date' => $record['date']
            ]),
            'result' => new Quantity([
                'value' => $record['value'],
                'unit' => $record['unit']
            ])
        ]);

        if (isset($this->code_maping[$record['type']])) {
            $model->addCode(new Code($this->code_maping[$record['type']]));
        } else {
            throw new \Exception("Unknown Physical Exam Type: `{$record['type']}`");
        }

        return $model;
    }
}
