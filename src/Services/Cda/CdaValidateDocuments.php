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
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Logging\SystemLoggerAwareTrait;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\Cda\Schematron\SchemaRegistry;

class CdaValidateDocuments
{
    use SystemLoggerAwareTrait;

    public $externalValidatorUrl;
    public $externalValidatorEnabled;

    public function __construct()
    {
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
     * @return array|bool|null
     * @throws Exception
     */
    public function validateDocument($document, $type)
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
     * @return array|mixed
     */
    public function ettValidateCcda($xml)
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

        $headers = [
            "Content-Type: multipart/form-data",
            "Accept: application/json",
        ];
        $post_url = $this->externalValidatorUrl;
        // I know there's a better way to do this but, not seeing it just now.
        $post_file = OEGlobalsBag::getInstance()->getString('temporary_files_dir') . '/ccda.xml';
        file_put_contents($post_file, $xml);
        $file = new CURLFile($post_file, 'application/xhtml+xml', 'ccda.xml');

        $post_this = [
            'validationObjective' => 'C-CDA_IG_Plus_Vocab',
            'referenceFileName' => 'noscenariofile',
            'vocabularyConfig' => 'ccdaReferenceValidatorConfig',
            'severityLevel' => 'ERROR',
            'curesUpdate' => true,
            'ccdaFile' => $file
        ];
        $httpVerifySsl = (bool) (OEGlobalsBag::getInstance()->get('http_verify_ssl') ?? true);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $post_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $httpVerifySsl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_this);

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        if (empty($response) || $status !== '200') {
            $reply['resultsMetaData']["resultMetaData"][0]["count"] = 1;
            $reply['ccdaValidationResults'][] = [
                'description' => xlt('Validation Request failed') .
                    ': Error ' . (curl_error($ch) ?: xlt('Unknown')) . ' ' .
                    xlt('Request Status') . ':' . $status
            ];
        }
        if ($status == '200') {
            $reply = json_decode($response, true);
        }

        return $reply;
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
        ];
        try {
            return array_merge($defaults, $this->schematronValidateDocument($xml, $type));
        } catch (\Throwable $e) {
            $this->getSystemLogger()->error('Schematron validation failed', ['exception' => $e]);
            return $defaults;
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
    public function createSchematronHtml($amid)
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
