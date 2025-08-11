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
use OpenEMR\Common\Logging\EventAuditLogger;
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
        if (empty($document)) {
            return '';
        }
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
        $document = $this->reportService->generateCategoryIIIXml($pid, $measures_resolved);

        return $document;
    }

    /**
     * NEW METHOD: Get consolidated QRDA III report for preview/processing
     *
     * @param mixed $pids Patient IDs
     * @param array $measures Measures to include
     * @param array $options Additional options
     * @return string XML content
     */
    public function getConsolidatedCategoryIIIReport($pids = null, $measures = [], $options = []): string
    {
        if (empty($measures)) {
            $measures = $this->reportMeasures;
        }

        return $this->reportService->generateConsolidatedCategoryIIIXml($pids, $measures);
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

        $zip_directory = sys_get_temp_dir() . ($bypid ? '/ep_measures_' : "/qrda_export_") . time();

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

                // delete existing to make reporting easier with last exported reports, current.
                $glob = glob("$local_directory/*.*");
                array_map('unlink', $glob);
                // create reports
                foreach ($pids as $pid) {
                    $meta = sqlQuery("Select `fname`, `lname`, `pid` From `patient_data` Where `pid` = ?", [$pid]);
                    $file = $measure_directory . "/{$meta['pid']}_{$meta['fname']}_{$meta['lname']}." . $type;
                    $file_local = $local_directory . "/{$meta['pid']}_{$meta['fname']}_{$meta['lname']}." . $type;
                    $content = $this->getCategoryIReport($pid, $measure, $type, $options);
                    if (empty($content)) {
                        continue;
                    }
                    file_put_contents($file, $content);
                    file_put_contents($file_local, $content);
                    // in order to deal with our zip files we are going to force our garbage collector to run
                    // json_decode stores a LOT of memory with our measures so we need to collect it.  Otherwise
                    // we end up exceeding our memory usage. ~60 patients exceeds over 256MB of memory...
                    // we end up trading CPU cycles to make sure we don't blow up smaller OpenEMR installations.
                    unset($content);
                    gc_mem_caches();
                    gc_collect_cycles(); // attempt to force memory collection here.
                    if ($type === 'xml') {
                        copy(
                            __DIR__ . '/../../../interface/modules/zend_modules/public/xsl/qrda.xsl',
                            $measure_directory . "/qrda.xsl"
                        );
                    }
                }
            }
            $zip_measure = 'measures';
            if (count($measures ?? []) === 1) {
                $zip_measure = $measures[0];
            }
            $zip_name = "QRDA1_" . $zip_measure . "_" . time() . ".zip";
        } elseif ($bypid) {
            foreach ($pids as $pid) {
                $meta = sqlQuery("Select `fname`, `lname`, `pid` From `patient_data` Where `pid` = ?", [$pid]);
                $file = $zip_directory . "/{$meta['pid']}_{$meta['fname']}_{$meta['lname']}." . $type;
                $file_local = $directory . "/{$meta['pid']}_{$meta['fname']}_{$meta['lname']}." . $type;
                $content = $this->getCategoryIReport($pid, '', $type, $options);
                if (empty($content)) {
                    continue;
                }
                file_put_contents($file, $content);
                file_put_contents($file_local, $content);
                unset($content);
                unset($file);
            }
            $zip_name = "ep_measures_" . time() . ".zip";
        }

        $save_path = sys_get_temp_dir() . "/" . $zip_name;
        $zip = new \ZipArchive();
        $ret = $zip->open($save_path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        if ($ret !== true) {
            throw new \RuntimeException(sprintf('Zip file "%s" was not created due to "%s"', $save_path, $ret));
        } else {
            $dir = opendir($zip_directory);
            while ($filename = readdir($dir)) {
                $filename_path = $zip_directory . "/" . $filename;
                if (is_file($filename_path)) {
                    $zip->addFile($filename_path, $filename);
                }
                if (
                    is_dir($filename_path) &&
                    (
                    !($filename == "." || $filename == "..")
                    )
                ) {
                    $dir_in_dir = opendir($filename_path);
                    while ($filename_in_dir = readdir($dir_in_dir)) {
                        if (!($filename_in_dir == "." || $filename_in_dir == "..")) {
                            $zip->addFile($filename_path . "/" . $filename_in_dir, $filename_in_dir);
                        }
                    }
                }
            }
            $zip->close();
        }

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

    /**
     * Modified downloadQrdaIII to support a consolidated option
     */
    public function downloadQrdaIII($pids, $measures = '', $options = [], $consolidated = false): void
    {
        if ($consolidated) {
            // Use a new consolidated download
            $this->downloadConsolidatedQrdaIII($pids, $measures, $options);
            return;
        }

        // Your existing individual download logic
        if (empty($measures)) {
            $measures = $this->reportMeasures;
        } elseif (!is_array($measures) && $measures === 'all') {
            $measures = $this->reportMeasures;
        }

        $directory = $GLOBALS['OE_SITE_DIR'] . DIRECTORY_SEPARATOR . 'documents' . DIRECTORY_SEPARATOR . 'cat3_reports';
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0775, true) && !is_dir($directory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $directory));
            }
        }

        if (is_array($pids)) {
            if (count($pids) === 1) {
                $pids = $pids[0];
            }
        }
        foreach ($measures as $measure) {
            if (is_array($measure)) {
                $measure = $measure['measure_id'];
            }
            $xml = $this->getCategoryIIIReport($pids, $measure, $options);
            $filename = $measure . "_selected_patients.xml";
            if (!empty($pids) && !is_array($pids)) {
                $meta = sqlQuery("Select `fname`, `lname`, `pid` From `patient_data` Where `pid` = ?", [$pids]);
                $filename = $measure . '_' . $pids . '_' . $meta['fname'] . '_' . $meta['lname'] . ".xml";
            }
            $file = $directory . DIRECTORY_SEPARATOR . $filename;
            file_put_contents($file, $xml);
            unset($content);
        }
        EventAuditLogger::instance()->newEvent("qrda3-export", $_SESSION['authUser'], $_SESSION['authProvider'], 1, "QRDA3 download");
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

    /**
     * NEW METHOD: Download consolidated QRDA III containing all measures
     *
     * @param mixed $pids Patient IDs (if empty, uses all patients)
     * @param array $measures Measures to include (if empty, uses all active)
     * @param array $options Additional options
     */
    public function downloadConsolidatedQrdaIII($pids = null, $measures = [], $options = []): void
    {
        try {
            // Use all active measures if none specified
            if (empty($measures)) {
                $measures = $this->reportMeasures;
            } elseif (!is_array($measures) && $measures === 'all') {
                $measures = $this->reportMeasures;
            }

            // Generate consolidated QRDA III XML
            $xml = $this->reportService->generateConsolidatedCategoryIIIXml($pids, $measures);

            // Generate filename
            $filename = $this->reportService->getConsolidatedFilename();

            // Create directory for saving locally
            $directory = $GLOBALS['OE_SITE_DIR'] . DIRECTORY_SEPARATOR . 'documents' . DIRECTORY_SEPARATOR . 'cat3_reports';
            if (!is_dir($directory)) {
                if (!mkdir($directory, 0775, true) && !is_dir($directory)) {
                    throw new \RuntimeException(sprintf('Directory "%s" was not created', $directory));
                }
            }

            // Save file locally
            $filePath = $directory . DIRECTORY_SEPARATOR . $filename;
            file_put_contents($filePath, $xml);

            // Log the event
            EventAuditLogger::instance()->newEvent(
                "qrda3-consolidated-export",
                $_SESSION['authUser'],
                $_SESSION['authProvider'],
                1,
                "QRDA III Consolidated download - " . count($measures) . " measures"
            );

            // Stream download to browser
            $this->streamXmlDownload($filename, $xml);
        } catch (\Exception $e) {
            error_log("Consolidated QRDA III download failed: " . $e->getMessage());

            // Send error response
            http_response_code(500);
            echo "Error generating consolidated QRDA III report: " . $e->getMessage();
            exit;
        }
    }

    /**
     * Helper method to stream XML download
     *
     * @param string $filename
     * @param string $content
     */
    private function streamXmlDownload($filename, $content): void
    {
        // Clean any output
        ob_clean();

        // Set download headers
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        header('Content-type: application/xml');
        header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . strlen($content));

        // Output content
        flush();
        echo $content;
        flush();
        exit;
    }
}
