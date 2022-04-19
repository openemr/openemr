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
    public function getCategoryIReport($pid, $measures, $type = 'xml', $options = [])
    {
        if (empty($measures)) {
            $measures = $this->reportMeasures;
        }
        // can be an array of measure data(measure_id,title,active or a delimited string. e.g. "CMS22;CMS69;CMS122;..."
        $measures_resolved = $this->reportService->resolveMeasuresPath($measures);
        // pass in measures with file path.
        $document = $this->reportService->generateCategoryIXml($pid, $measures_resolved, $options);
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

    public function getCategoryIIIReport($pid, $measures, $options = []): string
    {
        if (empty($measures)) {
            $measures = $this->reportMeasures;
        }
        // can be an array of measure data(measure_id,title,active or a delimited string. e.g. "CMS22;CMS69;CMS122;..."
        $measures_resolved = $this->reportService->resolveMeasuresPath($measures);
        // pass in measures with file path.
        $document = $this->reportService->generateCategoryIIIXml($pid, $measures_resolved, $options['performance_period_start'], $options['performance_period_end']);

        return $document;
    }

    /**
     * @param $pids
     * @param $measures
     * @return void
     */
    public function downloadQrdaIAsZip($pids, $measures = '', $type = 'xml', $options = []): void
    {
        $bypid = false;
        if (empty($measures)) {
            $measures = $this->reportMeasures;
        } elseif (!is_array($measures) && $measures === 'all') {
            $measures = '';
            $bypid = true;
        }

        $zip = new Zip();
        $zip_directory = sys_get_temp_dir() . ($bypid ? '/ep_measures_' : "/qrda_export_") . time();
        ;
        if (!is_dir($zip_directory)) {
            if (!mkdir($zip_directory, true, true) && !is_dir($zip_directory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $zip_directory));
            }
            chmod($zip_directory, 0777);
        }
        // local xml save directory
        $directory = $GLOBALS['OE_SITE_DIR'] . '/documents/' . 'cat1_reports';
        $directory .= ($bypid ? '/all_measures' : "/measures");
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true) && !is_dir($directory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $directory));
            }
        }

        $pids = is_array($pids) ? $pids : [$pids];
        if (!$bypid) {
            foreach ($measures as $measure) {
                if (is_array($measure)) {
                    $dir_measure = $measure['measure_id'];
                } else {
                    $dir_measure = $measure;
                }
                $measure_directory = $zip_directory . "/" . $dir_measure;
                $local_directory = $directory . "/" . $dir_measure;
                if (!is_dir($measure_directory)) {
                    if (!mkdir($measure_directory, true, true) && !is_dir($measure_directory)) {
                        throw new \RuntimeException(sprintf('Directory "%s" was not created', $measure_directory));
                    }
                    chmod($measure_directory, 0777);
                }
                if (!is_dir($local_directory)) {
                    if (!mkdir($local_directory, true, true) && !is_dir($local_directory)) {
                        throw new \RuntimeException(sprintf('Directory "%s" was not created', $local_directory));
                    }
                    chmod($local_directory, 0777);
                }
                foreach ($pids as $pid) {
                    $meta = sqlQuery("Select `fname`, `lname`, `pid` From `patient_data` Where `pid` = ?", [$pid]);
                    $file = $measure_directory . "/{$meta['pid']}_{$meta['fname']}_{$meta['lname']}." . $type;
                    $file_local = $local_directory . "/{$meta['pid']}_{$meta['fname']}_{$meta['lname']}." . $type;
                    $content = $this->getCategoryIReport($pid, $measure, $type, $options);
                    file_put_contents($file, $content);
                    file_put_contents($file_local, $content);
                    unset($content);
                    if ($type === 'xml') {
                        //copy(__DIR__ . '/../../../interface/modules/zend_modules/public/xsl/qrda.xsl', $measure_directory . "/qrda.xsl");
                    }
                }
            }
            $zip_name = "qrda1_export_" . time() . ".zip";
        } elseif ($bypid) {
            foreach ($pids as $pid) {
                $meta = sqlQuery("Select `fname`, `lname`, `pid` From `patient_data` Where `pid` = ?", [$pid]);
                $file = $zip_directory . "/{$meta['pid']}_{$meta['fname']}_{$meta['lname']}." . $type;
                $file_local = $directory . "/{$meta['pid']}_{$meta['fname']}_{$meta['lname']}." . $type;
                $content = $this->getCategoryIReport($pid, '', $type, $options);
                file_put_contents($file, $content);
                file_put_contents($file_local, $content);
                unset($content);
                unset($file);
            }
            $zip_name = "ep_measures_" . time() . ".zip";
        }

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

    public function downloadQrdaIII($pids, $measures = '', $options = []): void
    {
        if (empty($measures)) {
            $measures = $this->reportMeasures;
        } elseif (!is_array($measures) && $measures === 'all') {
            $measures = $this->reportMeasures;
        }
        $directory = $GLOBALS['OE_SITE_DIR'] . DIRECTORY_SEPARATOR . 'documents' . DIRECTORY_SEPARATOR . 'cat3_reports';
        if (!is_dir($directory)) {
            if (!mkdir($directory, 775, true) && !is_dir($directory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $directory));
            }
        }
        if (is_array($pids)) {
            if (count($pids) === 1) {
                $pids = $pids[0];
            } else {
                $pids = '';
            }
        }
        foreach ($measures as $measure) {
            if (is_array($measure)) {
                $measure = $measure['measure_id'];
            }
            $xml = $this->getCategoryIIIReport($pids, $measure, $options);
            $filename = $measure . "_all_patients.xml";
            if (!empty($pids)) {
                $meta = sqlQuery("Select `fname`, `lname`, `pid` From `patient_data` Where `pid` = ?", [$pids]);
                $filename = $measure . '_' . $pids . '_' . $meta['fname'] . '_' . $meta['lname'] . ".xml";
            }
            $file = $directory  . DIRECTORY_SEPARATOR . $filename;
            file_put_contents($file, $xml);
            unset($content);
        }
        ob_clean();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        header('Content-type: application/zip');
        header("Content-Disposition: attachment; filename=\"" . $filename . "\";");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . filesize($file));
        flush();
        readfile($file);
        flush();
        exit;
    }
}
