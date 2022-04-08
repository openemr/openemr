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
use OpenEMR\Services\Qdm\Interfaces\QdmRequestInterface;

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

    private function convertToObjectIdBSONFormat($id)
    {
        $hexValue = dechex($id);
        // max bigint size will fit in 16 characters so we will always have enough space for this.
        return sprintf("%024x", $hexValue);
    }

    /**
     * @param  QdmRequestInterface $request
     * @param  $measure
     * @param  $effectiveDate
     * @param  $effectiveEndDate
     * @return \Psr\Http\Message\StreamInterface|array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function calculateMeasure($patients, $measure, $effectiveDate, $effectiveEndDate)
    {
        $serializedPatients = [];
        foreach ($patients as $patient) {
            if ($patient instanceof \JsonSerializable) {
                $patientJson = $patient->jsonSerialize();
                if (!empty($patientJson)) {
                    if ($patientJson['id'] instanceof Identifier) {
                        $patientJson['_id'] = $this->convertToObjectIdBSONFormat($patientJson['id']->value);
                    } else {
                        $patientJson['_id'] = $patientJson['id'];
                    }
                }
                $serializedPatients[] = $patientJson;
            } else {
                $serializedPatients[] = $patient;
            }
        }
        $json_models = json_encode($serializedPatients);
        $patientStream = Psr7\Utils::streamFor($json_models);
        $measureFiles = MeasureService::fetchMeasureFiles($measure);
        $this->measure = $measureFiles['measure'];
        $measureFileStream = new LazyOpenStream($measureFiles['measure'], 'r');
        $valueSetFileStream = new LazyOpenStream($measureFiles['valueSets'], 'r');
        $options = [
            'doPretty' => true,
            'includeClauseResults' => true,
            'requestDocument' => true,
            'effectiveDate' => $effectiveDate,
            'effectiveDateEnd' => $effectiveEndDate
        ];
        $optionsStream = Psr7\Utils::streamFor(json_encode($options));

        $results = $this->client->calculate($patientStream, $measureFileStream, $valueSetFileStream, $optionsStream);
        return $this->convertResultsFromBSONObjectIdFormat($results);
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
