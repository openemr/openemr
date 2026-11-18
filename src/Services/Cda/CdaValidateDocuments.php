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

use DOMDocument;
use Exception;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Logging\SystemLoggerAwareTrait;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\Cda\Schematron\SchemaRegistry;

class CdaValidateDocuments
{
    use SystemLoggerAwareTrait;

    public $externalValidatorUrl;
    public $externalValidatorEnabled;

    private readonly ClientInterface $httpClient;

    public function __construct(?ClientInterface $httpClient = null)
    {
        $this->httpClient = $httpClient ?? ServiceContainer::getGuzzle();
        $this->externalValidatorEnabled = OEGlobalsBag::getInstance()->getBoolean('mdht_conformance_server_enable');
        if (empty(OEGlobalsBag::getInstance()->getString('mdht_conformance_server'))) {
            $this->externalValidatorEnabled = false;
        }
        $this->externalValidatorUrl = null;
        if ($this->externalValidatorEnabled) {
            // should never get to where the url is '' as we disable it if the conformance server is empty
            $this->externalValidatorUrl = trim(OEGlobalsBag::getInstance()->getString('mdht_conformance_server') ?? null) ?: '';
            if (!str_ends_with($this->externalValidatorUrl, '/')) {
                $this->externalValidatorUrl .= '/';
            }

            $this->externalValidatorUrl .= 'referenceccdaservice/';
        }
    }

    /**
     * @param $document
     * @param $type
     * @throws Exception
     */
    public function validateDocument($document, $type): array
    {
        $documentXml = is_string($document) ? $document : '';
        $schemaType = is_string($type) ? $type : SchemaRegistry::TYPE_CCDA;

        // always validate schema XSD
        $xsd = $this->validateXmlXsd($document, $type);
        if ($this->externalValidatorEnabled) {
            $schema_results = $this->ettValidateCcda($documentXml);
        } else {
            $schema_results = $this->validateSchematron($documentXml, $schemaType);
        }

        $totals = array_merge($xsd, $schema_results);

        return $totals;
    }

    /**
     * @param $xml
     */
    public function ettValidateCcda($xml): array
    {
        try {
            $result = $this->ettValidateDocumentRequest($xml);
        } catch (\Throwable $e) {
            $this->getSystemLogger()->error($e->getMessage(), ['exception' => $e]);
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
     * @return array<string, mixed>
     */
    private function schematronValidateDocument(string $xml, string $type = SchemaRegistry::TYPE_CCDA): array
    {
        $registry = new SchemaRegistry();
        $validator = $registry->loadValidator($type);
        $schematron = file_get_contents($registry->schematronPath($type));
        if ($schematron === false) {
            throw new Exception('Failed to read schematron file for type ' . $type);
        }
        return $validator->validate($xml, $schematron)->toArray();
    }

    /**
     * @param $xml
     * @return array|mixed
     */
    private function ettValidateDocumentRequest($xml)
    {
        $reply = [];
        if (empty($xml)) {
            return $reply;
        }

        $url = $this->externalValidatorUrl;
        if (!is_string($url) || $url === '') {
            return $this->ettFailureReply(0);
        }

        try {
            $response = $this->httpClient->request('POST', $url, [
                RequestOptions::HEADERS => ['Accept' => 'application/json'],
                RequestOptions::MULTIPART => [
                    ['name' => 'validationObjective', 'contents' => 'C-CDA_IG_Plus_Vocab'],
                    ['name' => 'referenceFileName', 'contents' => 'noscenariofile'],
                    ['name' => 'vocabularyConfig', 'contents' => 'ccdaReferenceValidatorConfig'],
                    ['name' => 'severityLevel', 'contents' => 'ERROR'],
                    ['name' => 'curesUpdate', 'contents' => '1'],
                    [
                        'name' => 'ccdaFile',
                        'contents' => $xml,
                        'filename' => 'ccda.xml',
                        'headers' => ['Content-Type' => 'application/xhtml+xml'],
                    ],
                ],
                RequestOptions::HTTP_ERRORS => false,
                RequestOptions::CONNECT_TIMEOUT => 10,
                // Validating a large document takes longer than the shared client's default.
                RequestOptions::TIMEOUT => 120,
                RequestOptions::VERIFY => OEGlobalsBag::getInstance()->getBoolean('http_verify_ssl', true),
            ]);
        } catch (GuzzleException $e) {
            ServiceContainer::getLogger()->error('C-CDA validation request failed', ['exception' => $e]);
            return $this->ettFailureReply(0);
        }

        $status = $response->getStatusCode();
        $body = (string) $response->getBody();
        if ($status !== 200 || $body === '') {
            return $this->ettFailureReply($status);
        }

        return json_decode($body, true, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * Shape a failed validation request like a validator reply reporting one error.
     *
     * @return array{resultsMetaData: array{resultMetaData: list<array{count: int}>}, ccdaValidationResults: list<array{type: string, description: string, documentLineNumber: string, xPath: string}>}
     */
    private function ettFailureReply(int $status): array
    {
        return [
            'resultsMetaData' => ['resultMetaData' => [['count' => 1]]],
            'ccdaValidationResults' => [[
                'type' => 'error',
                'description' => xlt('Validation Request failed') . ' ' . xlt('Request Status') . ': ' . $status,
                'documentLineNumber' => '',
                'xPath' => '',
            ]],
        ];
    }

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
            }
            libxml_clear_errors();
            $this->getSystemLogger()->error("CDA XSD Validation Errors", ['errors' => $xsd_log['xsd']]);
        }

        return $xsd_log;
    }

    /**
     * @return array<string, mixed>
     */
    private function validateSchematron(string $xml, string $type = SchemaRegistry::TYPE_CCDA): array
    {
        // Matches ValidationResult::toArray() shape so downstream renderers get
        // the same keys whether validation succeeds or fails.
        $defaults = [
            'errorCount' => 0,
            'warningCount' => 0,
            'ignoredCount' => 0,
            'errors' => [],
            'warnings' => [],
            'ignored' => [],
            'validationFailed' => false,
        ];
        try {
            return array_merge($defaults, $this->schematronValidateDocument($xml, $type));
        } catch (\Throwable $e) {
            $this->getSystemLogger()->error('Schematron validation failed', ['exception' => $e]);
            // Never return a clean bill of health for a validation that did not run.
            // An empty error list is indistinguishable from a conformant document, so
            // report the failure as a finding and flag it for callers that check.
            return array_merge($defaults, [
                'errorCount' => 1,
                'validationFailed' => true,
                'errors' => [[
                    'type' => 'error',
                    'test' => '',
                    'simplifiedTest' => null,
                    'description' => xlt('Schematron validation could not be completed. This document was not checked for conformance; see the system log for details.'),
                    'patternId' => '',
                    'ruleId' => '',
                    'assertionId' => null,
                    'context' => '',
                    'line' => null,
                    'path' => '',
                    'xml' => null,
                ]],
            ]);
        }
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
    public function createSchematronHtml($amid): string
    {
        $errors = $this->fetchValidationLog($amid);

        if (count($errors ?? [])) {
            $twig = ServiceContainer::getTwig();
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
        // JSON_INVALID_UTF8_SUBSTITUTE: a validation report is assembled from document
        // content, and one malformed byte anywhere in it would otherwise make
        // json_encode() return false. That would store an empty column, which reads back
        // as "no findings" - a silent pass. Substituting U+FFFD keeps the rest readable.
        $content = json_encode($log ?? [], JSON_INVALID_UTF8_SUBSTITUTE);
        if ($content === false) {
            // ServiceContainer::getLogger(), not the deprecated getSystemLogger(): the
            // PHPStan baseline pins this file at three deprecated calls, and new code
            // should use the supported accessor anyway.
            ServiceContainer::getLogger()->error('Could not encode CDA validation log', [
                'documentId' => $docId,
                'jsonError' => json_last_error_msg(),
            ]);
            return;
        }
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
