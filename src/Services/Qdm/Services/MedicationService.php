<?php


namespace OpenEMR\Services\Qdm\Services;


use OpenEMR\Cqm\Qdm\BaseTypes\Code;
use OpenEMR\Cqm\Qdm\BaseTypes\DateTime;
use OpenEMR\Cqm\Qdm\BaseTypes\Interval;
use OpenEMR\Cqm\Qdm\BaseTypes\Quantity;
use OpenEMR\Cqm\Qdm\MedicationActive;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\Qdm\Interfaces\QdmServiceInterface;

class MedicationService extends AbstractQdmService implements QdmServiceInterface
{
    public function getSqlStatement()
    {
        $sql = "SELECT
                    patient_id AS pid,
                    drug,
                    rxnorm_drugcode,
                    dosage,
                    unit,
                    RL.title AS drug_route,
                    `interval`,
                    date_added,
                    start_date,
                    end_date
                FROM prescriptions P
                LEFT JOIN list_options RL ON P.route = RL.option_id AND RL.list_id = 'drug_route'
                JOIN list_options FL ON P.interval = FL.option_id AND FL.list_id = 'drug_intervals'
                ";

        return $sql;
    }

    public function makeQdmModel(array $record)
    {
        // If we don't have a start date, use date added
        // TODO no start date in QRDA Import
        $start_date = !empty($record['start_date']) ? $record['start_date'] : $record['date_added'];

        // If no end date, use a null
        $end_date = !empty($record['end_date']) ? $record['end_date'] : null;

        $qdmModel = new MedicationActive([
            '_pid' => $record['pid'],
            'relevantPeriod' => new Interval([
                'low' =>  new DateTime([
                    'date' => $start_date
                ]),
                'high' => new DateTime([
                    'date' => $end_date
                ]),
                'lowClosed' => $start_date ? true : false,
                'highClosed' => $end_date ? true : false
            ]),
            'dosage' => new Quantity([
                'value' => $record['dosage'],
                'unit' => $record['unit']
            ]),
            'frequency' => new Code([
                // TODO codes in list_options for frequency may not match exactly and do not have the actual SNOMED codes loaded
                // https://browser.ihtsdotools.org/?perspective=full&conceptId1=396125000&edition=MAIN/2021-07-31&release=&languages=en
                'code' => $record['interval'],
                'system' => $this->getSystemForCodeType(CodeTypesService::CODE_TYPE_SNOMED_CT)
            ]),
            'route' => null // In sample files, route was null, probably doesn't mater for eCQM
        ]);

        $qdmModel->addCode(new Code([
            'code' => $record['rxnorm_drugcode'],
            'system' => $this->getSystemForCodeType(CodeTypesService::CODE_TYPE_RXNORM)
        ]));

        return $qdmModel;
    }
}
