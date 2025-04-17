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
use OpenEMR\Cqm\Qdm\BaseTypes\Interval;
use OpenEMR\Cqm\Qdm\BaseTypes\Quantity;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\Qdm\Interfaces\QdmServiceInterface;
use OpenEMR\Services\Qdm\QdmRecord;

abstract class AbstractMedicationService extends AbstractQdmService implements QdmServiceInterface
{
    abstract function getModelClass();

    public function getSqlStatement()
    {
        $sql = "SELECT
                    patient_id AS pid,
                    drug,
                    rxnorm_drugcode,
                    dosage,
                    unit,
                    `interval`,
                    RL.title AS drug_route,
                    UL.title AS drug_unit,
                    FL.title AS drug_interval,
                    date_added,
                    start_date,
                    end_date
                FROM prescriptions P
                LEFT JOIN list_options UL ON P.unit = UL.option_id AND UL.list_id = 'drug_units'
                LEFT JOIN list_options RL ON P.route = RL.option_id AND RL.list_id = 'drug_routes'
                LEFT JOIN list_options FL ON P.interval = FL.option_id AND FL.list_id = 'drug_intervals'
                ";

        return $sql;
    }

    public function getPatientIdColumn()
    {
        return 'patient_id';
    }

    public function makeQdmModel(QdmRecord $recordObj)
    {
        $record = $recordObj->getData();
        // If we don't have a start date, use date added
        // TODO no start date in QRDA Import
        $start_date = !empty($record['start_date']) ? $record['start_date'] : $record['date_added'];

        // If no end date, use a null
        $end_date = !empty($record['end_date']) ? $record['end_date'] : null;

        $modelClass = $this->getModelClass();

        $qdmModel = new $modelClass([
            'relevantPeriod' => new Interval([
                'low' =>  new DateTime([
                    'date' => $start_date
                ]),
                'high' => new DateTime([
                    'date' => $end_date
                ]),
                'lowClosed' => $start_date ? true : false,
                'highClosed' => $this->validDateOrNull($end_date) ? true : false
            ]),
            'route' => null // In sample files, route was null, probably doesn't mater for eCQM
        ]);

        if ($record['dosage']) {
            $qdmModel->dosage = new Quantity([
                'value' => (int)$record['dosage'] ?? null,
                'unit' => $record['drug_unit'] ?? null,
            ]);
        }

        if ($record['drug_interval']) {
            $qdmModel->frequency = new Code([
                // TODO codes in list_options for frequency may not match exactly and do not have the actual SNOMED codes loaded
                // https://browser.ihtsdotools.org/?perspective=full&conceptId1=396125000&edition=MAIN/2021-07-31&release=&languages=en
                'code' => '396125000', // $record['interval'],
                'system' => '2.16.840.1.113883.6.96' // $this->getSystemForCodeType(CodeTypesService::CODE_TYPE_SNOMED_CT)
            ]);
        }

        $qdmModel->addCode(
            new Code([
                'code' => $record['rxnorm_drugcode'],
                'system' => $this->getSystemForCodeType(CodeTypesService::CODE_TYPE_RXNORM)
            ])
        );

        return $qdmModel;
    }
}
