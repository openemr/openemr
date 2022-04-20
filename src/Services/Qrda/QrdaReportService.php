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

use OpenEMR\Cqm\CqmServiceManager;
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
        // first thing, start node service.
        $this->client = CqmServiceManager::makeCqmClient();
        $this->client->start();
        $this->builder = new QdmBuilder();
        $this->calculator = new CqmCalculator($this->builder);
        $this->measuresPath = MeasureService::fetchMeasuresPath();
        $this->patientJson = "";
    }

    function fetchCurrentMeasures($scope = 'active'): array
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
     * @param       $pid
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

    public function generateCategoryIIIXml($pid, $measures, $effectiveDate, $effectiveDateEnd): string
    {
        if ($pid) {
            $request = new QdmRequestOne($pid);
        } else {
            $request = new QdmRequestAll();
        }

        if (!empty($this->client->getHealth()['uptime'] ?? null)) {
            $exportService = new ExportCat3Service($this->builder, $this->calculator, $request);
            $xml = $exportService->export($measures, $effectiveDate, $effectiveDateEnd);
        } else {
            $msg = xlt("Can not complete report request. Node Service is not running.");
            throw new \RuntimeException($msg);
        }

        return $xml ?? '';
    }
}
