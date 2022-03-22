<?php
/**
 * @package OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qdm;

use GuzzleHttp\Psr7\LazyOpenStream;
use GuzzleHttp\Psr7;
use OpenEMR\Cqm\CqmServiceManager;
use OpenEMR\Services\Qdm\Interfaces\QdmRequestInterface;

class CqmCalculator
{
    protected $client;
    protected $measure;

    /**
     * CqmCalculator constructor.
     * @param $client
     */
    public function __construct()
    {
        $this->client = CqmServiceManager::makeCqmClient();
    }

    /**
     * @param QdmRequestInterface $request
     * @param $measure
     * @param $effectiveDate
     * @param $effectiveEndDate
     * @return \Psr\Http\Message\StreamInterface|array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function calculateMeasure($patients, $measure, $effectiveDate, $effectiveEndDate)
    {
        $json_models = json_encode($patients);
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

        return $this->client->calculate($patientStream, $measureFileStream, $valueSetFileStream, $optionsStream);
    }

    public function getMeasure()
    {
        return $this->measure;
    }
}
