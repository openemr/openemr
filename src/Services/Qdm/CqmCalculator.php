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
        foreach ($patients as $patient) {
            $patient->birthDatetime = DateHelper::format_datetime_cqm($patient->birthDatetime);
        }

        $json_models = json_encode($patients);
        //$json_models = file_get_contents('/Users/kchapple/Dev/QRDA_COMPARE/cat iii debug Adam Massey/Cypress.patients.out.json');
        $patientStream = Psr7\Utils::streamFor($json_models);
        $this->measure = $measure;
        $measureFiles = MeasureService::fetchMeasureFiles($measure->measure_path);
        // Convert to assoc array before converting back to json to send
        $json_measure = json_decode(json_encode($measure), true);
        $measureFileStream = Psr7\Utils::streamFor(json_encode($json_measure));
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
        return $results;//$this->convertResultsFromBSONObjectIdFormat($results);
    }

    public function convertResultsFromBSONObjectIdFormat($results)
    {
        $newResult = [];
        if (!empty($results)) {
            foreach ($results as $key => $result) {
                $convertedKey = $this->convertIdFromBSONObjectIdFormat($key);
                // go and update the inner patient_id
                $newResult[$convertedKey] = [];

                foreach ($result as $popKey => $popResult) {
                    $popResult['patient_id'] = $convertedKey;
                    $newResult[$convertedKey][$popKey] = $popResult;
                }
            }
        }
        return $newResult;
    }

    private function convertIdFromBSONObjectIdFormat($id)
    {
        // max bigint size is 8 bytes which will fit fine
        // string ID should be prefixed with 0s so the converted data type should be far smaller
        $trimmedId = ltrim($id, '\x0');
        $decimal = hexdec($trimmedId);
        return $decimal;
    }

    public function getMeasure()
    {
        return $this->measure;
    }
}
