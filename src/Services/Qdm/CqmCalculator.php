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
        //$json_models = file_get_contents('/Users/kchapple/Dev/QRDA_COMPARE/cat iii debug Andrew Rodriguez/Cypress.patients.out.json');
        $patientStream = Psr7\Utils::streamFor($json_models);
        $this->measure = $measure;
        $measureFiles = MeasureService::fetchMeasureFiles($measure->measure_path);
        // Convert to assoc array before converting back to json to send
        $measure_array = json_decode(json_encode($measure), true);
        $json_measure = json_encode($measure_array);
        //$json_measure = file_get_contents('/Users/kchapple/Dev/QRDA_COMPARE/cat iii debug Andrew Rodriguez/Cypress.measure.out.json');
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
