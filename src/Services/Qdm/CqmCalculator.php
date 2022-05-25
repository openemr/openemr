<?php

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qdm;

use GuzzleHttp\Psr7\LazyOpenStream;
use GuzzleHttp\Psr7;
use OpenEMR\Cqm\CqmServiceManager;
use OpenEMR\Cqm\Qdm\BaseTypes\Code;
use OpenEMR\Cqm\Qdm\Diagnosis;
use OpenEMR\Cqm\Qdm\Identifier;
use OpenEMR\Cqm\Qdm\MedicationOrder;
use OpenEMR\Cqm\Qdm\SubstanceOrder;
use OpenEMR\Services\Qdm\Interfaces\QdmRequestInterface;
use OpenEMR\Services\Qrda\Util\DateHelper;

class CqmCalculator
{
    protected $client;
    protected $measure;

    /**
     * CqmCalculator constructor.
     *
     * @param $client
     */
    public function __construct()
    {
        $this->client = CqmServiceManager::makeCqmClient();
    }

    protected function findCodeByOid($valueSetArray, $oid_code)
    {
        $code = null;
        foreach ($valueSetArray as $component) {
            if ($component['oid'] == $oid_code) {
                $first_concept = $component['concepts'][0];
                $code = new Code([
                    "code" => $first_concept['code'],
                    "system" => $first_concept['code_system_oid']
                ]);
                break;
            }
        }

        return $code;
    }

    /**
     * @param  QdmRequestInterface $request
     * @param  Measure $measure
     * @param  $effectiveDate
     * @param  $effectiveEndDate
     * @return \Psr\Http\Message\StreamInterface|array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function calculateMeasure($patients, Measure $measure, $effectiveDate, $effectiveEndDate)
    {
        $this->measure = $measure;
        $measureFiles = MeasureService::fetchMeasureFiles($measure->measure_path);
        $valueSetArray = json_decode(file_get_contents($measureFiles['valueSets']), true);
        // Fix somethings that the cqm calculator needs before we create JSON out of the patient models.
        foreach ($patients as $patient) {
            $patient->birthDatetime = DateHelper::format_datetime_cqm($patient->birthDatetime);
            $data_elements_to_add = [];
            foreach ($patient->dataElements as $dataElement) {
                // We need to look up OIDs and add the first code concept from the value set.
                // We do this first in case it's in a dataElement that we need to clone for the calculator below
                if (isset($dataElement->negationRationale) && $dataElement->negationRationale !== null) {
                    $to_add = [];
                    foreach ($dataElement->dataElementCodes as $dataElementCode) {
                        if (empty($dataElementCode->system)) {
                            // placeholder for "oid" codes that calculator likes
                            $dataElementCode->system = "1.2.3.4.5.6.7.8.9.10";
                            // Look up OID code in measure
                            $code = $this->findCodeByOid($valueSetArray, $dataElementCode->code);
                            if ($code !== null) {
                                $to_add[] = $code;
                            }
                        }
                    }
                    foreach ($to_add as $item) {
                        $dataElement->dataElementCodes [] = $item;
                    }
                }

                // The calculator seems to be confused whether it needs a substance order or medication order, so Cypress
                // sends both... so we do too.
                if ($dataElement instanceof SubstanceOrder) {
                    $medOrder = new MedicationOrder([
                        'authorDatetime' => $dataElement->authorDatetime,
                        'dataElementCodes' => $dataElement->dataElementCodes,
                        'negationRationale' => $dataElement->negationRationale,
                        'relevantPeriod' => $dataElement->relevantPeriod,
                        'frequency' => $dataElement->frequency
                    ]);
                    $data_elements_to_add[] = $medOrder;
                }
            }

            foreach ($data_elements_to_add as $data_elem) {
                $patient->dataElements[] = $data_elem;
            }
        }

        $json_models = json_encode($patients);
        $patientStream = Psr7\Utils::streamFor($json_models);

        // Convert to assoc array before converting back to json to send
        $measure_array = json_decode(json_encode($measure), true);
        $json_measure = json_encode($measure_array);
        $measureFileStream = Psr7\Utils::streamFor($json_measure);
        $valueSetFileStream = new LazyOpenStream($measureFiles['valueSets'], 'r');
        $options = [
            'doPretty' => true,
            'includeClauseResults' => true,
            'requestDocument' => true,
            'effectiveDate' => date('YmdHi', strtotime($effectiveDate)) . '00',
            'effectiveDateEnd' => null // !empty($effectiveEndDate) ? date('YmdHi', strtotime($effectiveEndDate)) . '00' : null
        ];
        $optionsStream = Psr7\Utils::streamFor(json_encode($options));

        $results = $this->client->calculate($patientStream, $measureFileStream, $valueSetFileStream, $optionsStream);
        return $results;
    }

    public function getMeasure()
    {
        return $this->measure;
    }
}
