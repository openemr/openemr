<?php

/**
 * QrdaDocumentController.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2022 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Cqm\QrdaControllers;

use DOMDocument;
use Laminas\Filter\Compress\Zip;
use OpenEMR\Services\Qrda\QrdaReportService;
use XSLTProcessor;

class QrdaReportController
{
    private $reportService;
    public $reportMeasures;

    public function __construct()
    {
        $this->reportService = new QrdaReportService();
        $this->reportMeasures = $this->reportService->fetchCurrentMeasures('active');
    }

    /**
     * @param $pid      mixed
     * @param $measures mixed array or string
     * @param $type     string xml, html,
     * @return mixed
     */
    public function getCategoryIReport($pid, $measures, $type = 'xml')
    {
        if (empty($measures)) {
            $measures = $this->reportMeasures;
        }
        if (is_array($measures) && empty($measures[0])) {
            $measures = $this->reportMeasures;
        }
        // can be an array of measure data(measure_id,title,active or a delimited string. e.g. "CMS22;CMS69;CMS122;..."
        $measures_resolved = $this->reportService->resolveMeasuresPath($measures);
        // pass in measures with file path.
        $document = $this->reportService->generateCategoryIXml($pid, $measures_resolved);
        if ($type === 'html') {
            $xml = simplexml_load_string($document);
            $xsl = new DOMDocument();
            $xsl->load(__DIR__ . '/../../../interface/modules/zend_modules/public/xsl/qrda.xsl');
            $proc = new XSLTProcessor();
            if (!$proc->importStyleSheet($xsl)) { // attach the xsl rules
                throw new \RuntimeException("QRDA Stylesheet could not be found");
            }
            $outputFile = sys_get_temp_dir() . '/out_' . time() . '.html';
            $proc->transformToURI($xml, $outputFile);
            $document = file_get_contents($outputFile);
        }

        return $document;
    }

    /**
     * @param $pids
     * @param $measures
     * @return void
     */
    public function downloadQrdaIAsZip($pids, $measures = '', $type = 'xml')
    {
        $zip = new Zip();
        $zip_directory = sys_get_temp_dir() . "/catI_export_" . time();
        if (!is_dir($zip_directory)) {
            if (!mkdir($zip_directory, true) && !is_dir($zip_directory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $zip_directory));
            }
            chmod($zip_directory, 0777);
        }
        $pids = is_array($pids) ? $pids : [$pids];
        foreach ($measures as $measure) {
            $measure_directory = $zip_directory . "/" . $measure . "_" . time();
            if (!is_dir($measure_directory)) {
                if (!mkdir($measure_directory, true) && !is_dir($measure_directory)) {
                    throw new \RuntimeException(sprintf('Directory "%s" was not created', $measure_directory));
                }
                chmod($measure_directory, 0777);
            }
            foreach ($pids as $pid) {
                $meta = sqlQuery("Select `fname`, `lname` From `patient_data` Where `pid` = ?", [$pid]);
                $file = $measure_directory . "/{$meta['fname']}_{$meta['lname']}." . $type;
                $content = $this->getCategoryIReport($pid, $measure, $type);
                $directory = $GLOBALS['OE_SITE_DIR'] . DIRECTORY_SEPARATOR . 'documents' . DIRECTORY_SEPARATOR . 'temp';
                file_put_contents($directory . DIRECTORY_SEPARATOR . $measure . "_{$meta['lname']}_{$meta['fname']}." . 'xml', $content);
                $f_handle = fopen($file, "w");
                fwrite($f_handle, $content);
                fclose($f_handle);
                if ($type === 'xml') {
                    copy(__DIR__ . '/../../../interface/modules/zend_modules/public/xsl/qrda.xsl', $measure_directory . "/qrda.xsl");
                }
            }
        }
        $zip_name = "QRDA1_Export_" . time() . ".zip";
        $save_path = sys_get_temp_dir() . "/" . $zip_name;
        $zip->setArchive($save_path);
        $zip->compress($zip_directory);

        ob_clean();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        header('Content-type: application/zip');
        header("Content-Disposition: attachment; filename=\"" . $zip_name . "\";");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . filesize($save_path));
        flush();
        readfile($save_path);
        flush();
        exit;
    }
}
