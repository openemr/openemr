<?php

/**
 * Document Service for CCDA
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2021-2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use Application\Model\ApplicationTable;
use Carecoordination\Model\CcdaGenerator;
use Carecoordination\Model\EncounterccdadispatchTable;
use CouchDB;
use DOMDocument;
use Exception;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Core\OEGlobalsBag;
use RuntimeException;
use XSLTProcessor;
use ZipArchive;

/**
 * Class CDADocumentService
 *
 * @package OpenEMR\Services
 */
class CDADocumentService extends BaseService
{
    private const TABLE_NAME = "ccda";
    private const XSL_PATH = '/interface/modules/zend_modules/public/xsl/cda.xsl';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
        UuidRegistry::createMissingUuidsForTables([self::TABLE_NAME]);
    }

    /**
     * Get the path to the CDA stylesheet.
     */
    private function getXslPath(): string
    {
        return OEGlobalsBag::getInstance()->get('fileroot') . self::XSL_PATH;
    }

    /**
     * @param int|string $pid
     * @return array|false|null
     */
    public function getLastCdaMeta($pid): false|array|null
    {
        $query = "SELECT cc.uuid, cc.date, pd.fname, pd.lname, pd.pid FROM ccda AS cc
            LEFT JOIN patient_data AS pd ON pd.pid=cc.pid
            WHERE cc.pid = ?
            ORDER BY cc.id DESC LIMIT 1";

        return sqlQuery($query, [$pid]);
    }

    /**
     * @param string $id UUID
     * @return false|string
     */
    public function getFile(string $id): false|string
    {
        $query = "SELECT couch_docid, couch_revid, ccda_data, encrypted FROM ccda WHERE uuid = ?";
        $row = sqlQuery($query, [$id]);
        $content = '';

        if (empty($row)) {
            return $content;
        }

        if (!empty($row['couch_docid'])) {
            $couch = new CouchDB();
            $resp = $couch->retrieve_doc($row['couch_docid']);
            if ($row['encrypted']) {
                $cryptoGen = new CryptoGen();
                $content = $cryptoGen->decryptStandard($resp->data, null, 'database');
            } else {
                $content = base64_decode((string)$resp->data);
            }
        } elseif (!empty($row['ccda_data'])) {
            $fileData = file_get_contents($row['ccda_data']);
            if ($fileData === false) {
                return '';
            }
            if ($row['encrypted']) {
                $cryptoGen = new CryptoGen();
                $content = $cryptoGen->decryptStandard($fileData, null, 'database');
            } else {
                $content = $fileData;
            }
        }

        return $content;
    }

    /**
     * Generate CCDA XML using all documented components.
     *
     * @param int|string $pid Patient ID
     * @return string CCDA XML content
     * @throws Exception
     */
    public function generateCCDXml($pid): string
    {
        $dispatchTable = new EncounterccdadispatchTable(new ApplicationTable());
        $ccdaGenerator = new CcdaGenerator($dispatchTable);
        $result = $ccdaGenerator->generate(
            $pid,
            null,
            null,
            '0',
            '1',
            '0',
            null,
            null,
            'patient',
            '',
            'ccd',
            null,
            []
        );
        $content = $result->getContent();
        unset($result);

        if (str_starts_with($content, 'ERROR:')) {
            (new SystemLogger())->errorLogCaller("Error generating CCDA", ['message' => $content]);
            throw new Exception(xlt("Error generating CCDA") . ": " . $content);
        }

        return $content;
    }

    /**
     * Generate CCDA as HTML.
     *
     * @param int|string $pid Patient ID
     * @return string HTML content
     * @throws Exception
     */
    public function generateCCDHtml($pid): string
    {
        $content = $this->generateCCDXml($pid);
        return $this->xmlToHtmlContent($content);
    }

    /**
     * Generate CCDA as a ZIP bundle containing XML, HTML, and XSL.
     *
     * @param int|string $pid Patient ID
     * @return string ZIP file contents
     * @throws Exception
     */
    public function generateCCDZip($pid): string
    {
        $content = $this->generateCCDXml($pid);
        return $this->generateCCDAZipBundle($content);
    }

    /**
     * Create a ZIP bundle from CCDA XML content.
     *
     * @param string $content CCDA XML content
     * @return string ZIP file contents
     * @throws Exception
     */
    public function generateCCDAZipBundle(string $content): string
    {
        $xslSource = $this->getXslPath();
        if (!file_exists($xslSource)) {
            throw new RuntimeException(xlt("CDA stylesheet not found"));
        }

        $uniqueId = bin2hex(random_bytes(16));
        $tempDir = sys_get_temp_dir();
        $parentDir = $tempDir . "/CCDA_" . $uniqueId;
        $zipPath = $tempDir . "/CCDA_" . $uniqueId . ".zip";

        try {
            if (!mkdir($parentDir, 0700, true) && !is_dir($parentDir)) {
                throw new RuntimeException(xlt("Failed to create temporary directory"));
            }

            $filename = "CCDA_ccd_" . date("Y_m_d_His");
            $filenameXml = $filename . ".xml";
            $filenameHtml = $filename . ".html";

            if (file_put_contents($parentDir . "/" . $filenameXml, $content) === false) {
                throw new RuntimeException(xlt("Failed to write XML file"));
            }

            $htmlContent = $this->xmlToHtmlContent($content);
            if (file_put_contents($parentDir . "/" . $filenameHtml, $htmlContent) === false) {
                throw new RuntimeException(xlt("Failed to write HTML file"));
            }

            if (!copy($xslSource, $parentDir . "/CDA.xsl")) {
                throw new RuntimeException(xlt("Failed to copy stylesheet"));
            }

            $zip = new ZipArchive();
            $zipResult = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            if ($zipResult !== true) {
                throw new RuntimeException(xlt("Failed to create ZIP file") . " (code: $zipResult)");
            }

            $zip->addFile($parentDir . "/" . $filenameXml, $filenameXml);
            $zip->addFile($parentDir . "/" . $filenameHtml, $filenameHtml);
            $zip->addFile($parentDir . "/CDA.xsl", "CDA.xsl");

            if (!$zip->close()) {
                throw new RuntimeException(xlt("Failed to finalize ZIP file"));
            }

            $zipContent = file_get_contents($zipPath);
            if ($zipContent === false) {
                throw new RuntimeException(xlt("Failed to read ZIP file"));
            }

            return $zipContent;

        } finally {
            if (file_exists($zipPath)) {
                unlink($zipPath);
            }
            $this->removeDirectory($parentDir);
        }
    }

    /**
     * Recursively remove a directory and its contents.
     *
     * @param string $dir Directory path
     */
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }

    /**
     * Transform CCDA XML to HTML using XSL stylesheet.
     *
     * @param string $content CCDA XML content
     * @return string HTML content
     * @throws Exception
     */
    private function xmlToHtmlContent(string $content): string
    {
        $sheet = $this->getXslPath();
        if (!file_exists($sheet)) {
            throw new RuntimeException(xlt("CDA stylesheet not found"));
        }

        $xml = simplexml_load_string($content);
        if ($xml === false) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            (new SystemLogger())->errorLogCaller("Failed to parse CCDA XML", ['errors' => $errors]);
            throw new RuntimeException(xlt("Failed to parse CCDA XML"));
        }

        $xsl = new DOMDocument();
        if (!$xsl->load($sheet)) {
            throw new RuntimeException(xlt("Failed to load CDA stylesheet"));
        }

        $proc = new XSLTProcessor();
        if (!$proc->importStyleSheet($xsl)) {
            throw new RuntimeException(xlt("Failed to import CDA stylesheet"));
        }

        $uniqueId = bin2hex(random_bytes(16));
        $outputFile = sys_get_temp_dir() . '/cda_html_' . $uniqueId . '.html';

        try {
            $result = $proc->transformToURI($xml, $outputFile);
            if ($result === false) {
                throw new RuntimeException(xlt("Failed to transform CCDA to HTML"));
            }

            $htmlContent = file_get_contents($outputFile);
            if ($htmlContent === false) {
                throw new RuntimeException(xlt("Failed to read transformed HTML"));
            }

            return $htmlContent;

        } finally {
            if (file_exists($outputFile)) {
                if (!unlink($outputFile)) {
                    (new SystemLogger())->errorLogCaller(
                        "Failed to unlink temporary CDA output. This could expose PHI.",
                        ['filename' => $outputFile]
                    );
                }
            }
        }
    }
}
