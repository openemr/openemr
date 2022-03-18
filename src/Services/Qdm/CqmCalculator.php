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
use OpenEMR\Cqm\CqmServiceManager;
use OpenEMR\Services\Qdm\Interfaces\QdmRequestInterface;

class CqmCalculator
{
    protected $client;
    protected $builder;

    /**
     * CqmCalculator constructor.
     * @param $client
     */
    public function __construct()
    {
        $this->client = CqmServiceManager::makeCqmClient();
        $this->builder = new QdmBuilder();
    }

    /**
     * @param QdmRequestInterface $request
     * @param $measure
     * @param $effectiveDate
     * @param $effectiveEndDate
     * @return \Psr\Http\Message\StreamInterface|array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function calculateMeasure(QdmRequestInterface $request, $measure, $effectiveDate, $effectiveEndDate)
    {
        $models = $this->builder->build($request);
        $json_models = json_encode($models);
        $patientStream = Psr7\Utils::streamFor($json_models);
        $measureFiles = MeasureService::fetchMeasureFiles($measure);
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
}
