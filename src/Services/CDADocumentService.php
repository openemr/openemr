<?php

/**
 * Document Service for CCDA
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2021 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use Application\Model\ApplicationTable;
use Carecoordination\Model\CcdaGenerator;
use Carecoordination\Model\EncounterccdadispatchTable;
use CouchDB;
use DOMDocument;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Utils\NetworkUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use Symfony\Component\HttpClient\HttpClient;
use XSLTProcessor;

/**
 * Class CDADocumentService
 *
 * @package OpenEMR\Services
 *
 * See interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php
 * indexAction() and interface/modules/zend_modules/public/index.php
 */
class CDADocumentService extends BaseService
{
    const TABLE_NAME = "ccda";
    protected string $serverUrl;
    protected bool $verifySsl;
    protected string|bool $caCert;

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
        UuidRegistry::createMissingUuidsForTables([self::TABLE_NAME]);

        // Determine the server URL for internal CCD generation
        // Use the configured site address and apply appropriate SSL verification

        $this->serverUrl = $GLOBALS['qualified_site_addr'];

        $networkUtils = new NetworkUtils();
        if ($networkUtils->isLoopbackAddress($this->serverUrl)) {
            // Loopback address - traffic never leaves the local machine
            // SSL verification is always disabled for loopback (no security benefit, often fails)
            $this->verifySsl = false;
            $this->caCert = false;
        } else {
            // Non-loopback address (e.g., nginx sidecar, docker compose, kubernetes)
            $this->verifySsl = (bool)($GLOBALS['http_verify_ssl'] ?? true);
            $this->caCert = $GLOBALS['http_ca_cert'] ?? false; // Use custom CA cert for self-signed certificates
        }
    }

    protected function createHttpClient()
    {
        $config = [
            'verify_host' => $this->verifySsl,
            'verify_peer' => $this->verifySsl,
        ];

        // If SSL verification is enabled and a custom CA cert is provided, use it
        if ($this->verifySsl && $this->caCert && file_exists($this->caCert)) {
            $config['cafile'] = $this->caCert;
        }

        return HttpClient::create($config);
    }

    /**
     * @param $pid
     * @return array|false|null
     */
    public function getLastCdaMeta($pid)
    {
        $query = "SELECT cc.uuid, cc.date, pd.fname, pd.lname, pd.pid FROM ccda AS cc
		    LEFT JOIN patient_data AS pd ON pd.pid=cc.pid
		    WHERE cc.pid = ?
		    ORDER BY cc.id DESC LIMIT 1";

        return sqlQuery($query, [$pid]);
    }

    /**
     * @param $id
     * @return false|string
     */
    public function getFile($id)
    {
        $query = "select couch_docid, couch_revid, ccda_data, encrypted from ccda where uuid=?";
        $row = sqlQuery($query, [$id]);
        $content = '';
        if (!empty($row)) {
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
                $fccda = fopen($row['ccda_data'], "r");
                if ($row['encrypted']) {
                    $cryptoGen = new CryptoGen();
                    $content = $cryptoGen->decryptStandard(fread($fccda, filesize($row['ccda_data'])), null, 'database');
                } else {
                    $content = fread($fccda, filesize($row['ccda_data']));
                }
                fclose($fccda);
            }
        }

        return $content;
    }

    /**
     * @param $pid
     * @return string
     */
    public function generateCCDHtml($pid): string
    {
        $url = $this->serverUrl . "/interface/modules/zend_modules/public/encounterccdadispatch";
        $httpClient = $this->createHttpClient();
        $response = $httpClient->request('GET', $url, [
            'query' => [
                'combination' => $pid,
                'recipient' => 'self',
                'view' => '1',
                'site' => $_SESSION ['site_id'],
                'sent_by_app' => 'core_api',
                'me' => session_id()
            ]
        ]);

        $status = $response->getStatusCode(); // @todo validate

        return $response->getContent();
    }

    /**
     * @param $pid
     * @return string
     */
    public function generateCCDXml($pid): string
    {
        $url = $this->serverUrl . "/interface/modules/zend_modules/public/encounterccdadispatch";
        $httpClient = $this->createHttpClient();
        $response = $httpClient->request('GET', $url, [
            'query' => [
                'combination' => $pid,
                'recipient' => 'patient',
                'view' => '0',
                'hiehook' => '1',
                'sent_by_app' => 'core_api',
                'me' => session_id()
            ]
        ]);

        $status = $response->getStatusCode(); // @todo validate

        return $response->getContent();
    }

    /**
     * @param $pid
     * @return string
     */
    public function portalGenerateCCD($pid): string
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
            '',
            null,
            []
        );
        $content = $result->getContent();
        unset($result);

        if (str_starts_with($content, 'ERROR:')) {
            echo "<h3>" . text($content) . "</h3>";
            (new SystemLogger())->errorLogCaller("Error generating Portal CCDA", ['message' => $content]);
            die();
        }

        return $this->XmlToHtmlContent($content);
    }

    private function XmlToHtmlContent($content): false|string
    {
        $xml = simplexml_load_string($content);
        $xsl = new DOMDocument();
        // cda.xsl is self-contained with bootstrap and jquery.
        $sheet = __DIR__ . '/../../interface/modules/zend_modules/public/xsl/cda.xsl';
        $xsl->load($sheet);
        $proc = new XSLTProcessor();
        $proc->importStyleSheet($xsl); // attach the xsl rules
        $outputFile = sys_get_temp_dir() . '/out_' . time() . '.html';
        $proc->transformToURI($xml, $outputFile);

        $htmlContent = file_get_contents($outputFile);
        $result = unlink($outputFile); // remove the file so we don't have PHI left around on the filesystem
        if (!$result) {
            (new SystemLogger())->errorLogCaller("Failed to unlink temporary CDA output on hard drive. This could expose PHI and needs to be investigated.", ['filename' => $outputFile]);
        }

        return $htmlContent;
    }

    /**
     * @param $pid
     * @return string
     */
    public function portalGenerateCCDZip($pid): string
    {
        $parameterArray = [
            'combination' => $pid,
            'components' => 'allergies|medications|problems|immunizations|procedures|results|plan_of_care|vitals|social_history|encounters|functional_status|referral|instructions|medical_devices|goals',
            'downloadccda' => 'download_ccda',
            'latestccda' => '0',
            'send_to' => 'download_all',
            'sent_by_app' => 'portal',
            'ccda_pid' => [0 => $pid],
            'view' => 0,
            'recipient' => 'patient',
            'site' => $_SESSION ['site_id'],
        ];
        $url = $this->serverUrl . "/interface/modules/zend_modules/public/encounterccdadispatch";
        $httpClient = $this->createHttpClient();
        $response = $httpClient->request('POST', $url, [
            'query' => ['me' => session_id()], // to authenticate in CCM. Portal only.
            'body' => $parameterArray
        ]);

        $status = $response->getStatusCode(); // @todo validate

        return $response->getContent();
    }

    /**
     * Complete zip of xml, html version
     * when called within an openemr authorized session.
     *
     * @param $pid
     * @return string
     */
    public function generateCCDZip($pid): string
    {
        $parameterArray = [
            'combination' => $pid,
            'components' => 'allergies|medications|problems|immunizations|procedures|results|plan_of_care|vitals|social_history|encounters|functional_status|referral|instructions|medical_devices|goals',
            'downloadccda' => 'download_ccda',
            'latestccda' => '0',
            'send_to' => 'download_all',
            'sent_by_app' => 'core_api',
            'ccda_pid' => [0 => $pid],
            'view' => 0,
            'recipient' => 'self',
            'site' => $_SESSION['site_id'],
        ];
        $url = $this->serverUrl . "/interface/modules/zend_modules/public/encounterccdadispatch"; // add for debug ?XDEBUG_SESSION=PHPSTORM
        $httpClient = $this->createHttpClient();
        $response = $httpClient->request('POST', $url, [
            'query' => ['me' => session_id()],
            'body' => $parameterArray
        ]);

        $status = $response->getStatusCode(); // @todo validate

        return $response->getContent();
    }
}
