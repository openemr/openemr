<?php

/**
 * QrdaDocumentService.php
 * Several methods borrowed or refactored from Ken's CQM Tool.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2022 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Qrda;

use GuzzleHttp\Psr7\LazyOpenStream;
use GuzzleHttp\Psr7;
use OpenEMR\Common\System\System;
use OpenEMR\Cqm\CqmClient;
use OpenEMR\Cqm\CqmServiceManager;
use OpenEMR\Cqm\Generator;
use OpenEMR\Services\Qdm\MeasureService;
use OpenEMR\Services\Qdm\QdmBuilder;
use OpenEMR\Services\Qdm\QdmRequestAll;
use OpenEMR\Services\Qdm\QdmRequestOne;

class QrdaReportService
{
    protected $client;
    protected $patientJson;
    public $measuresPath;

    public function __construct()
    {
        $this->client = CqmServiceManager::makeCqmClient();
        $this->measuresPath = MeasureService::fetchMeasuresPath();
        $this->patientJson = "";
    }

    /**
     * @param $measures
     * @return array
     */
    function resolveMeasuresArray($measures): array
    {
        $resolved = [];
        $result = [];
        if (is_array($measures)) {
            $resolved = $measures;
        } elseif (is_string($measures)) {
            $resolved = explode(';', $measures);
        }
        foreach ($resolved as $measure) {
            if (empty($measure)) {
                continue;
            }
            $result[] = $this->measuresPath . DIRECTORY_SEPARATOR . $measure;
        }

        return $result;
    }

    /**
     * @return void
     */
    public function generateModels()
    {
        $generator = new Generator();
        $generator->execute();
    }

    /**
     * @param $pid
     * @return array
     * @throws \Exception
     */
    public function generatePatient($pid): array
    {
        if ($pid) {
            $request = new QdmRequestOne($pid);
        } else {
            $request = new QdmRequestAll();
        }
        $builder = new QdmBuilder();
        $models = $builder->build($request) ?? [];

        return $models;
    }

    /**
     * @param $pid
     * @param $measure
     * @param $effectiveDate
     * @param $effectiveEndDate
     * @return \Psr\Http\Message\StreamInterface|array
     * @throws \Exception
     */
    public function executeMeasure($pid, $measure, $effectiveDate, $effectiveEndDate): \Psr\Http\Message\StreamInterface|array
    {
        if ($pid) {
            $request = new QdmRequestOne($pid);
        } else {
            $request = new QdmRequestAll();
        }
        $builder = new QdmBuilder();
        $models = $builder->build($request);
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

    /**
     * @param $pid
     * @param $measures
     * @return string
     */
    public function generateCategoryIXml($pid, $measures = []): string
    {
        $exportService = new ExportService(new QdmBuilder(), new QdmRequestOne($pid));
        $xml = $exportService->export(MeasureService::fetchAllMeasuresArray($measures));

        return $xml;
    }
}
