<?php

/**
 * CDA QRDA Validation Class
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2022 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Cda;

use CURLFile;
use DOMDocument;
use Exception;
use OpenEMR\Common\Http\GuzzleHttpClient;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\System\System;
use OpenEMR\Common\Twig\TwigContainer;

class CdaValidateDocuments
{
    public $externalValidatorUrl;
    public $externalValidatorEnabled;
    private GuzzleHttpClient $httpClient;

    public function __construct(?GuzzleHttpClient $httpClient = null)
    {
        $this->externalValidatorEnabled = !empty($GLOBALS['mdht_conformance_server_enable'] ?? false);
        if (empty($GLOBALS['mdht_conformance_server'])) {
            $this->externalValidatorEnabled = false;
        }
        $this->externalValidatorUrl = null;
        if ($this->externalValidatorEnabled) {
            // should never get to where the url is '' as we disable it if the conformance server is empty
            $this->externalValidatorUrl = trim((string) ($GLOBALS['mdht_conformance_server'] ?? null)) ?: '';
            if (!str_ends_with($this->externalValidatorUrl, '/')) {
                $this->externalValidatorUrl .= '/';
            }

            $this->externalValidatorUrl .= 'referenceccdaservice/';
        }
        $httpVerifySsl = (bool) ($GLOBALS['http_verify_ssl'] ?? true);
        $this->httpClient = $httpClient ?? new GuzzleHttpClient([
            'verify' => $httpVerifySsl,
        ]);
    }

    /**
     * @param $document
     * @param $type
     * @return array|bool|null
     * @throws Exception
     */
    public function validateDocument($document, $type)
    {
        // always validate schema XSD
        $xsd = $this->validateXmlXsd($document, $type);
        if ($this->externalValidatorEnabled) {
            $schema_results = $this->ettValidateCcda($document);
        } else {
            $schema_results = $this->validateSchematron($document, $type);
        }

        $totals = array_merge($xsd, $schema_results);

        return $totals;
    }

    /**
     * @param $xml
     * @return array|mixed
     */
    public function ettValidateCcda($xml)
    {
        try {
            $result = $this->ettValidateDocumentRequest($xml);
        } catch (Exception $e) {
            (new SystemLogger())->errorLogCaller($e->getMessage(), ["trace" => $e->getTraceAsString()]);
            return [];
        }
        // translate result to our common render array
        $results = [
            'errorCount' => $result['resultsMetaData']["resultMetaData"][0]["count"],
            'warningCount' => 0,
            'ignoredCount' => 0,
        ];
        foreach ($result['ccdaValidationResults'] as $r) {
            $results['errors'][] = [
                'type' => 'error',
                'test' => $r['type'],
                'description' => $r['description'],
                'line' => $r['documentLineNumber'],
                'path' => $r['xPath'],
                'context' => $r['type'],
                'xml' => '',
            ];
        }
        return $results;
    }

    /**
     * @param string $port
     * @return bool
     * @throws Exception
     */
    public function startValidationService($port = '6662'): bool
    {
        $system = new System();
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket === false) {
            throw new Exception("Socket Creation Failed");
        }
        $serverActive = @socket_connect($socket, "localhost", $port);

        if ($serverActive === false) {
            $path = $GLOBALS['fileroot'] . "/ccdaservice/node_modules/oe-schematron-service";
            if (IS_WINDOWS) {
                $redirect_errors = " > ";
                $redirect_errors .= $system->escapeshellcmd($GLOBALS['temporary_files_dir'] . "/schematron_server.log") . " 2>&1";
                $cmd = $system->escapeshellcmd("node " . $path . "/app.js") . $redirect_errors;
                $pipeHandle = popen("start /B " . $cmd, "r");
                if ($pipeHandle === false) {
                    throw new Exception("Failed to start local schematron service");
                }
                if (pclose($pipeHandle) === -1) {
                    error_log("Failed to close pipehandle for schematron service");
                }
            } else {
                $command = 'nodejs';
                if (!$system->command_exists($command)) {
                    if ($system->command_exists('node')) {
                        $command = 'node';
                    } else {
                        error_log("Node is not installed on the system.  Connection failed");
                        throw new Exception('Connection Failed.');
                    }
                }
                $cmd = $system->escapeshellcmd("$command " . $path . "/app.js");
                exec($cmd . " > /dev/null &");
            }
            sleep(2); // give cpu a rest
            $serverActive = socket_connect($socket, "localhost", $port);
            if ($serverActive === false) {
                error_log("Failed to start and connect to local schematron service server on port 6662");
                throw new Exception("Connection Failed");
            }
        }
        socket_close($socket);

        return $serverActive;
    }

    /**
     * @param $xml
     * @param $type
     * @return mixed|null
     * @throws Exception
     * Code migrated from curl to Guzzle by GitHub Copilot AI
     */
    private function schematronValidateDocument($xml, $type = 'ccda')
    {
        $service = $this->startValidationService();
        $reply = [];

        $httpResponse = $this->httpClient->post('http://127.0.0.1:6662/?type=' . attr_url($type), [
            'body' => $xml,
            'headers' => [
                'Content-Type' => 'application/xml',
                'Accept' => 'application/json',
            ],
            'connect_timeout' => 10,
        ]);

        if ($httpResponse->getStatusCode() == 200) {
            $reply = json_decode($httpResponse->getBody(), true);
        }

        return $reply;
    }
    // End of AI-generated code

    /**
     * @param $xml
     * @return array|mixed
     * Code migrated from curl to Guzzle by GitHub Copilot AI
     */
    private function ettValidateDocumentRequest($xml)
    {
        $reply = [];
        if (empty($xml)) {
            return $reply;
        }

        $post_url = $this->externalValidatorUrl;
        // I know there's a better way to do this but, not seeing it just now.
        $post_file = $GLOBALS['temporary_files_dir'] . '/ccda.xml';
        file_put_contents($post_file, $xml);

        // Open file handle to be managed by Guzzle
        $fileHandle = fopen($post_file, 'r');
        if ($fileHandle === false) {
            $reply['resultsMetaData']["resultMetaData"][0]["count"] = 1;
            $reply['ccdaValidationResults'][] = [
                'description' => xlt('Failed to open file for upload')
            ];
            return $reply;
        }

        $httpResponse = $this->httpClient->post($post_url, [
            'multipart' => [
                [
                    'name' => 'validationObjective',
                    'contents' => 'C-CDA_IG_Plus_Vocab'
                ],
                [
                    'name' => 'referenceFileName',
                    'contents' => 'noscenariofile'
                ],
                [
                    'name' => 'vocabularyConfig',
                    'contents' => 'ccdaReferenceValidatorConfig'
                ],
                [
                    'name' => 'severityLevel',
                    'contents' => 'ERROR'
                ],
                [
                    'name' => 'curesUpdate',
                    'contents' => 'true'
                ],
                [
                    'name' => 'ccdaFile',
                    'contents' => $fileHandle,
                    'filename' => 'ccda.xml',
                    'headers' => ['Content-Type' => 'application/xhtml+xml']
                ]
            ],
            'headers' => [
                'Accept' => 'application/json',
            ],
            'connect_timeout' => 10,
        ]);

        $status = $httpResponse->getStatusCode();
        $response = $httpResponse->getBody();
        
        if (empty($response) || $status !== 200) {
            $reply['resultsMetaData']["resultMetaData"][0]["count"] = 1;
            $reply['ccdaValidationResults'][] = [
                'description' => xlt('Validation Request failed') .
                    ': Error ' . ($httpResponse->getError() ?: xlt('Unknown')) . ' ' .
                    xlt('Request Status') . ':' . $status
            ];
        }
        if ($status == 200) {
            $reply = json_decode($response, true);
        }

        return $reply;
    }
    // End of AI-generated code

    /**
     * @param $document
     * @param $type
     * @return bool
     */
    public function validateXmlXsd($document, $type)
    {
        libxml_use_internal_errors(true);
        $dom = new DomDocument();
        $dom->loadXML($document);
        $xsd = __DIR__ . '/../../../interface/modules/zend_modules/public/xsd/Schema/CDA2/infrastructure/cda/CDA_SDTC.xsd';

        $xsd_log['xsd'] = [];
        if (!$dom->schemaValidate($xsd)) {
            $errors = libxml_get_errors();
            foreach ($errors as $error) {
                $detail = $this->formatXsdError($error);
                $xsd_log['xsd'][] = $detail;
                error_log($detail);
            }
            libxml_clear_errors();
        }

        return $xsd_log;
    }

    /**
     * @param $xml
     * @param $type
     * @return mixed|null
     * @throws Exception
     */
    private function validateSchematron($xml, $type = 'ccda')
    {
        $results = [
            'errorCount' => 0,
            'warningCount' => 0,
            'ignoredCount' => 0,
            'errors' => []
        ];
        try {
            $result = $this->schematronValidateDocument($xml, $type);
        } catch (Exception $e) {
            $e = $e->getMessage();
            error_log($e);
            $result = [];
        }
        // so we don't haves PHP errors concerning undefineds.
        $result = array_merge($results, $result);

        return $result;
    }

    /**
     * @param $error
     * @return string
     */
    private function formatXsdError($error): string
    {
        $error_str = "\n";
        switch ($error->level) {
            case LIBXML_ERR_WARNING:
                $error_str .= "Warning $error->code: ";
                break;
            case LIBXML_ERR_ERROR:
                $error_str .= "Error $error->code: ";
                break;
            case LIBXML_ERR_FATAL:
                $error_str .= "Fatal Error $error->code: ";
                break;
        }
        $error_str .= trim((string) $error->message);
        $error_str .= " on line $error->line\n";

        return $error_str;
    }

    /**
     * @param $amid
     * @return string
     */
    public function createSchematronHtml($amid)
    {
        $errors = $this->fetchValidationLog($amid);

        if (count($errors ?? [])) {
            $twig = (new TwigContainer(null, $GLOBALS['kernel']))->getTwig();
            $html = $twig->render("carecoordination/cda/cda-validate-results.html.twig", ['validation' => $errors]);
        } else {
            $html = xlt("No Errors or Validation service is disabled in Admin Config Connectors 'Disable All CDA Validation Reporting'.");
        }
        return $html;
    }

    /**
     * @param $docId
     * @param $log
     * @return void
     */
    public function saveValidationLog($docId, $log)
    {
        $content = json_encode($log ?? []);
        sqlStatement("UPDATE `documents` SET `document_data` = ? WHERE `id` = ?", [$content, $docId]);
    }

    /**
     * @param $docId
     * @return mixed
     */
    public function fetchValidationLog($audit_id)
    {
        $log = sqlQuery("SELECT `document_data` FROM `documents` WHERE `audit_master_id` = ?", [$audit_id])['document_data'];
        return json_decode($log ?? [], true);
    }
}
