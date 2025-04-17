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
use OpenEMR\Services\Qdm\QdmRequestSome;

class QrdaReportService
{
    protected $builder;
    protected $calculator;
    protected $patientJson;
    protected $effectiveDate;
    protected $effectiveDateEnd;
    protected $client;
    public $measuresPath;

    public function __construct()
    {
        // first thing, ensure have a node service.
        $this->client = CqmServiceManager::makeCqmClient();
        if (empty($this->client->getHealth()['uptime'] ?? null)) {
            $this->client->start();
            sleep(2); // give cpu a rest
        }
        if (empty($this->client->getHealth()['uptime'] ?? null)) {
            $msg = xlt("Can not complete report request. Node Service is not running.");
            throw new \RuntimeException($msg);
        }
        $this->builder = new QdmBuilder();
        $this->calculator = new CqmCalculator();
        $this->measuresPath = MeasureService::fetchMeasuresPath();
        $this->patientJson = "";
        $this->effectiveDate = trim($GLOBALS['cqm_performance_period'] ?? '2022') . '-01-01 00:00:00';
        $this->effectiveDateEnd = trim($GLOBALS['cqm_performance_period'] ?? '2022') . '-12-31 23:59:59';
    }

    /**
     * @param $scope
     * @return array
     */
    function fetchCurrentMeasures($scope = 'active'): array
    {
        $measures = [];
        $year = trim($GLOBALS['cqm_performance_period'] ?: '2022');
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
     * @return string
     */
    public function generateCategoryIXml($pid, $measures = []): string
    {
        $options = [
            'performance_period_start' => $this->effectiveDate,
            'performance_period_end' => $this->effectiveDateEnd
        ];
        if ($pid) {
            $request = new QdmRequestOne($pid);
        } else {
            $request = new QdmRequestAll();
        }

        $exportService = new ExportCat1Service($this->builder, $request);
        $xml = $exportService->export($measures, $options);

        return $xml;
    }

    /**
     * @param $pid
     * @param $measures
     * @return bool
     */
    public function qualifyPatientMeasure($pid, $measures): bool
    {
        if ($pid) {
            $request = new QdmRequestOne($pid);
        } else {
            $request = new QdmRequestAll();
        }

        $exportService = new ExportCat3Service($this->builder, $this->calculator, $request);
        $result = $exportService->export($measures, true);
        $include = array_shift($result);
        if ((int)$include[0]->IPP === 0) {
            error_log(errorLogEscape(xlt('Patient did not qualify') . ' pid: ' . $pid . ' Measures: ' . text(basename($measures[0]))));
            return false;
        }

        return true;
    }

    /**
     * @param $pid
     * @param $measures
     * @return string
     */
    public function generateCategoryIIIXml($pid, $measures): string
    {
        if (empty($pid)) {
            $request = new QdmRequestAll();
        } elseif (is_array($pid)) {
            $request = new QdmRequestSome($pid);
        } else {
            $request = new QdmRequestOne($pid);
        }

        if (!empty($this->client->getHealth()['uptime'] ?? null)) {
            $exportService = new ExportCat3Service($this->builder, $this->calculator, $request);
            $xml = $exportService->export($measures);
        } else {
            $msg = xlt("Can not complete report request. Node Service is not running.");
            throw new \RuntimeException($msg);
        }

        return $xml ?? '';
    }
}
