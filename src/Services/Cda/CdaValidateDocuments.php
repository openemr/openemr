<?php

/**
 * CDA QRDA Validation Class
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2022 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Cda;

use DOMDocument;
use Exception;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Utils\Json;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\Cda\Schematron\SchemaRegistry;
use Psr\Log\LoggerInterface;

/**
 * @phpstan-type EttFinding array{type: string, description: string, documentLineNumber: string, xPath: string}
 * @phpstan-type EttReply array{count: int, findings: list<EttFinding>}
 */
class CdaValidateDocuments
{
    public ?string $externalValidatorUrl = null;
    public bool $externalValidatorEnabled = false;

    private readonly ClientInterface $httpClient;

    private readonly LoggerInterface $logger;

    private readonly OEGlobalsBag $globals;

    public function __construct(
        ?ClientInterface $httpClient = null,
        ?LoggerInterface $logger = null,
        ?OEGlobalsBag $globals = null,
    ) {
        $this->httpClient = $httpClient ?? ServiceContainer::getGuzzle();
        $this->logger = $logger ?? ServiceContainer::getLogger();
        $this->globals = $globals ?? OEGlobalsBag::getInstance();
        $server = trim($this->globals->getString('mdht_conformance_server'));
        $this->externalValidatorEnabled = $server !== ''
            && $this->globals->getBoolean('mdht_conformance_server_enable');
        if ($this->externalValidatorEnabled) {
            $this->externalValidatorUrl = rtrim($server, '/') . '/referenceccdaservice/';
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
        $xsd = $this->validateXmlXsd($documentXml);
        if ($this->externalValidatorEnabled) {
            $schema_results = $this->ettValidateCcda($documentXml);
        } else {
            $schema_results = $this->validateSchematron($documentXml, $schemaType);
        }

        $totals = array_merge($xsd, $schema_results);

        return $totals;
    }

    /**
     * @return array{}|array{errorCount: int, warningCount: 0, ignoredCount: 0, errors?: non-empty-list<array{type: 'error', test: string, description: string, line: string, path: string, context: string, xml: ''}>}
     */
    public function ettValidateCcda(string $xml): array
    {
        // A request that fails comes back as a one-error reply, so a validation that did not run
        // never reads as a conformant document.
        $result = $this->ettValidateDocumentRequest($xml);
        // translate result to our common render array
        $results = [
            'errorCount' => $result['count'],
            'warningCount' => 0,
            'ignoredCount' => 0,
        ];
        foreach ($result['findings'] as $r) {
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
     * @return EttReply
     */
    private function ettValidateDocumentRequest(string $xml): array
    {
        if ($xml === '') {
            return ['count' => 0, 'findings' => []];
        }

        $url = $this->externalValidatorUrl;
        if ($url === null) {
            $this->logger->error('C-CDA validation requested with no validator server configured');
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
                RequestOptions::ALLOW_REDIRECTS => false,
                RequestOptions::CONNECT_TIMEOUT => 10,
                // Validating a large document takes longer than the shared client's default.
                RequestOptions::TIMEOUT => 120,
                RequestOptions::VERIFY => $this->globals->getBoolean('http_verify_ssl', true),
            ]);
        } catch (GuzzleException | \InvalidArgumentException $e) {
            // InvalidArgumentException: Guzzle rejects a malformed server setting before sending.
            $this->logger->error('C-CDA validation request failed', ['exception' => $e]);
            return $this->ettFailureReply(0);
        }

        $status = $response->getStatusCode();
        $body = (string) $response->getBody();
        if ($status !== 200 || $body === '') {
            return $this->ettFailureReply($status);
        }

        // A reply that isn't a validation report (a proxy's HTML error page, say) is a failed
        // validation, not a document with no findings.
        try {
            $decoded = Json::decode($body);
        } catch (\JsonException) {
            $decoded = null;
        }
        $reply = is_array($decoded) ? $this->parseEttReply($decoded) : null;
        if ($reply === null) {
            $this->logger->error('C-CDA validator reply was not a validation report', ['status' => $status]);
            return $this->ettFailureReply($status);
        }
        return $reply;
    }

    /**
     * Read the error count and findings from a decoded validator reply.
     *
     * @param array<mixed> $decoded
     * @return ?EttReply null when the reply lacks the count or the findings list, or reports that the
     *                   validator itself failed
     */
    private function parseEttReply(array $decoded): ?array
    {
        $metaData = $decoded['resultsMetaData'] ?? null;
        if (is_array($metaData) && ($metaData['serviceError'] ?? null) === true) {
            // The validator answers 200 with no findings when it fails internally.
            $this->logger->error('C-CDA validator reported a service error', [
                'serviceErrorMessage' => $metaData['serviceErrorMessage'] ?? null,
            ]);
            return null;
        }
        $counts = is_array($metaData) ? $metaData['resultMetaData'] ?? null : null;
        $firstCount = is_array($counts) ? $counts[0] ?? null : null;
        $count = is_array($firstCount) ? $firstCount['count'] ?? null : null;
        $results = $decoded['ccdaValidationResults'] ?? null;
        if (!is_int($count) || !is_array($results)) {
            return null;
        }

        $findings = [];
        foreach ($results as $result) {
            if (!is_array($result)) {
                return null;
            }
            $findings[] = [
                'type' => self::ettText($result['type'] ?? null),
                'description' => self::ettText($result['description'] ?? null),
                'documentLineNumber' => self::ettText($result['documentLineNumber'] ?? null),
                'xPath' => self::ettText($result['xPath'] ?? null),
            ];
        }
        return ['count' => $count, 'findings' => $findings];
    }

    /**
     * A finding's field as text: the validator sends line numbers as strings or numbers.
     */
    private static function ettText(mixed $value): string
    {
        return match (true) {
            is_string($value) => $value,
            is_int($value), is_float($value) => (string) $value,
            default => '',
        };
    }

    /**
     * Shape a failed validation request like a validator reply reporting one error.
     *
     * @return EttReply
     */
    private function ettFailureReply(int $status): array
    {
        return [
            'count' => 1,
            'findings' => [[
                'type' => 'error',
                'description' => xlt('Validation Request failed') . ' ' . xlt('Request Status') . ': ' . $status,
                'documentLineNumber' => '',
                'xPath' => '',
            ]],
        ];
    }

    /**
     * @return array{xsd: list<string>} one formatted message per schema violation
     */
    public function validateXmlXsd(string $document): array
    {
        $xsd = __DIR__ . '/../../../interface/modules/zend_modules/public/xsd/Schema/CDA2/infrastructure/cda/CDA_SDTC.xsd';
        $previousInternalErrors = libxml_use_internal_errors(true);
        try {
            // Start from an empty buffer, so the report holds only this document's errors.
            libxml_clear_errors();
            $dom = new DomDocument();
            $dom->loadXML($document);
            $valid = $dom->schemaValidate($xsd);
            $errors = $valid ? [] : libxml_get_errors();
        } finally {
            // Restore libxml's state even when loadXML() throws, as it does for an empty document.
            libxml_clear_errors();
            libxml_use_internal_errors($previousInternalErrors);
        }

        $messages = [];
        foreach ($errors as $error) {
            $messages[] = $this->formatXsdError($error);
        }
        if (!$valid) {
            $this->logger->error('CDA XSD Validation Errors', ['errors' => $messages]);
        }

        return ['xsd' => $messages];
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
            $this->logger->error('Schematron validation failed', ['exception' => $e]);
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

    private function formatXsdError(\LibXMLError $error): string
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
        $error_str .= trim($error->message);
        $error_str .= " on line $error->line\n";

        return $error_str;
    }

    /**
     * @throws \JsonException|\UnexpectedValueException when the saved report is unreadable
     */
    public function createSchematronHtml(int|string|null $amid): string
    {
        $errors = $amid === null ? null : $this->fetchValidationLog($amid);

        if (is_array($errors) && $errors !== []) {
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
            $this->logger->error('Could not encode CDA validation log', [
                'documentId' => $docId,
                'jsonError' => json_last_error_msg(),
            ]);
            return;
        }
        sqlStatement("UPDATE `documents` SET `document_data` = ? WHERE `id` = ?", [$content, $docId]);
    }

    /**
     * @return ?array<mixed> the saved validation report, or null when the document has none
     * @throws \JsonException when the saved report is not valid JSON
     * @throws \UnexpectedValueException when the saved report is JSON but not a report
     */
    public function fetchValidationLog(int|string $audit_id): ?array
    {
        $sql = <<<'SQL'
            SELECT `document_data` FROM `documents` WHERE `audit_master_id` = ?
            SQL;
        $row = QueryUtils::querySingleRow($sql, [$audit_id]);
        $log = is_array($row) ? $row['document_data'] ?? null : null;
        if (!is_string($log) || $log === '') {
            return null;
        }
        $decoded = Json::decode($log);
        if (!is_array($decoded)) {
            throw new \UnexpectedValueException('The saved CDA validation report is not a JSON object or array');
        }
        return $decoded;
    }
}
