<?php


namespace OpenEMR\Services\Qdm\Services;


use OpenEMR\Cqm\Qdm\BaseTypes\Code;
use OpenEMR\Cqm\Qdm\BaseTypes\Interval;
use OpenEMR\Cqm\Qdm\BaseTypes\Quantity;
use OpenEMR\Cqm\Qdm\MedicationActive;
use OpenEMR\Services\CodeTypesService;

class MedicationService extends AbstractQdmService
{
    public function getSqlStatement()
    {
        $sql = "SELECT pid, drug, rxnorm_drugcode, dosage, unit, L.title AS drug_route, date_added, start_data, end_date
                FORM prescriptions P
                JOIN list_options L ON P.route = L.option_id AND L.list_id = 'drug_route'
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
            'dosage' => new Quantity([]),
            'frequency' => new Code([
                // TODO codes in list_options may not match exactly and do not have the actual SNOMED codes loaded
                // https://browser.ihtsdotools.org/?perspective=full&conceptId1=396125000&edition=MAIN/2021-07-31&release=&languages=en
                'code' => '',
                'system' => $this->getSystemForCodeType(CodeTypesService::CODE_TYPE_SNOMED_CT)
            ]),
            'route' => null // In sample files, route was null, probably doesn't mater for eCQM
        ]);

        $qdmModel->addCode(new Code([
            'code' => $record['rxnorm_drugcode'],
            'system' => $this->getSystemForCodeType(CodeTypesService::CODE_TYPE_RXNORM)
        ]));
    }
}
