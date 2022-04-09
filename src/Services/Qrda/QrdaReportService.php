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
use OpenEMR\Services\Qdm\CqmCalculator;
use OpenEMR\Services\Qdm\MeasureService;
use OpenEMR\Services\Qdm\QdmBuilder;
use OpenEMR\Services\Qdm\QdmRequestAll;
use OpenEMR\Services\Qdm\QdmRequestOne;

class QrdaReportService
{
    protected $builder;
    protected $calculator;
    protected $patientJson;
    public $measuresPath;

    public function __construct()
    {
        $this->builder = new QdmBuilder();
        $this->calculator = new CqmCalculator($this->builder);
        $this->measuresPath = MeasureService::fetchMeasuresPath();
        $this->patientJson = "";
    }

    function fetchCurrentMeasures($scope = 'active')
    {
        $measures = [];
        $year = trim($GLOBALS['cqm_performance_period'] ?? '2022');
        $list = 'ecqm_' . $year . '_reporting';
        $active = $scope == 'active' ? 1 : 0;
        $results = sqlStatement("SELECT `option_id` as measure_id, `title`, `activity` as active FROM `list_options` WHERE `list_id` = ? AND `activity` >= ?", array($list, $active));
        while ($row = sqlFetchArray($results)) {
            $measures[] = $row;
        }
        return $measures;
    }

    /**
     * @param  $measures
     * @return array
     */
    function resolveMeasuresPath($measures): array
    {
        $resolved = [];
        $result = [];
        if (empty($measures)) {
            $measures = $this->fetchCurrentMeasures('active');
        }
        if (is_array($measures)) {
            $resolved = $measures;
        } elseif (is_string($measures)) {
            $resolved = explode(';', $measures);
        }
        foreach ($resolved as $measure) {
            if (empty($measure)) {
                continue;
            }
            if (is_array($measure)) {
                $measure = $measure['measure_id'];
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
     * @param  $pid
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
        $models = $this->builder->build($request) ?? [];

        return $models;
    }

    /**
     * @param  $pid
     * @param  $measure
     * @param  $effectiveDate
     * @param  $effectiveEndDate
     * @return \Psr\Http\Message\StreamInterface|array
     * @throws \Exception
     */
    public function executeMeasure($pid, $measure, $effectiveDate, $effectiveEndDate)
    {
        if ($pid) {
            $request = new QdmRequestOne($pid);
        } else {
            $request = new QdmRequestAll();
        }
        return $this->calculator->calculateMeasure($request, $measure, $effectiveDate, $effectiveEndDate);
    }

    /**
     * @param  $pid
     * @param array $measures
     * @param array $options
     * @return string
     */
    public function generateCategoryIXml($pid, $measures = [], $options = []): string
    {
        if ($pid) {
            $request = new QdmRequestOne($pid);
        } else {
            $request = new QdmRequestAll();
        }
        $exportService = new ExportCat1Service($this->builder, $request);
        $xml = $exportService->export($measures, $options);

        return $xml;
    }

    public function generateCategoryIIIXml($pid, $measures, $effectiveDate, $efffectiveDateEnd): string
    {
        if ($pid) {
            $request = new QdmRequestOne($pid);
        } else {
            $request = new QdmRequestAll();
        }
        $exportService = new ExportCat3Service($this->builder, $this->calculator, $request);
        $xml = $exportService->export($measures, $effectiveDate, $efffectiveDateEnd);

        return $xml;
    }
}
