<?php

/**
 * CcdaBuilder.php - Pure PHP CCDA Generator
 *
 * This class replaces the Node.js CCDA service, generating C-CDA documents
 * entirely in PHP without requiring Node.js runtime.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Carecoordination\Model\PhpCcdaBuilder;

use DOMDocument;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Carecoordination\Model\PhpCcdaBuilder\Core\LeafLevel;
use OpenEMR\Core\OEGlobalsBag;

class CcdaBuilder
{
    private readonly CcdaDataTransformer $transformer;
    private readonly CcdaTemplateEngine $templateEngine;
    private readonly CcdaXmlBuilder $xmlBuilder;
    private readonly SystemLogger $logger;
    private bool $debug = true;
    private string $documentLocation = '';
    private string $stylesheetPath = "../../../../interface/modules/zend_modules/public/xsl/cda.xsl";

    public function __construct()
    {
        $this->transformer = new CcdaDataTransformer();
        $this->templateEngine = new CcdaTemplateEngine();
        $this->xmlBuilder = new CcdaXmlBuilder();
        $this->logger = new SystemLogger();
        $this->debug = OEGlobalsBag::getInstance()->getInt('ccda_alt_service_enable') === 5;
    }

    /**
     * Enable debug mode - writes intermediate files
     */
    public function setDebug(bool $debug, string $documentLocation = ''): self
    {
        $this->debug = $debug;
        $this->documentLocation = $documentLocation;
        return $this;
    }

    /**
     * Set the XSL stylesheet path for the generated XML
     * 
     * @param string $path The path to the stylesheet (should be web-accessible)
     */
    public function setStylesheetPath(string $path): self
    {
        $this->stylesheetPath = $path;
        $this->templateEngine->setStylesheetPath($path);
        return $this;
    }

    /**
     * Generate CCDA document from XML input
     *
     * This is the main entry point that replaces the Node.js socket_get() call.
     * It takes the same XML input that was previously sent to serveccda.js
     * and returns the generated CCDA XML document.
     *
     * @param string $xmlInput The XML data from EncounterccdadispatchTable
     * @return string The generated CCDA XML document
     * @throws \Exception If generation fails
     */
    public function generate(string $xmlInput): string
    {
        $this->logger->debug("CcdaBuilder::generate() - Starting CCDA generation");

        // Step 1: Parse input XML to array (replaces xml2js in Node)
        $data = $this->parseInputXml($xmlInput);

        if (empty($data) || empty($data['CCDA'])) {
            throw new \RuntimeException("Invalid CCDA input data");
        }

        $ccdaData = $data['CCDA'];
        $docType = $ccdaData['doc_type'] ?? 'ccd';
        $xslUrl = $ccdaData['xslUrl'] ?? '';

        // Step 2: Check for unstructured document type
        if ($docType === 'unstructured') {
            return $this->generateUnstructured($ccdaData, $xslUrl);
        }

        // Step 3: Transform data to template format (replaces populate*() functions)
        $transformedData = $this->transformer->transform($ccdaData);

        // Debug output
        if ($this->debug) {
            $this->writeDebugFile('ccda_transformed.json', json_encode($transformedData, JSON_PRETTY_PRINT));
        }

        // Step 4: Generate CCDA XML using templates (replaces bbg.generateCCD())
        $ccdaXml = $this->templateEngine->generateCcd($transformedData);

        // Step 5: Apply XSL stylesheet reference if provided
        $ccdaXml = $this->applyXslHeader($ccdaXml, $xslUrl);

        // Step 6: Check for embedded patient files (unstructured attachment)
        $result = $ccdaXml;
        if (!empty($ccdaData['patient_files'])) {
            $unstructuredXml = $this->generateUnstructured($ccdaData, $xslUrl);
            $unstructuredXml = $this->applyXslHeader($unstructuredXml, $xslUrl);
            $result .= $unstructuredXml;
        }

        // Debug output
        if ($this->debug) {
            $this->writeDebugFile('ccda.xml', $result);
        }

        $this->logger->debug("CcdaBuilder::generate() - CCDA generation complete");

        return $result;
    }

    /**
     * Parse input XML string to associative array
     *
     * Replaces xml2js parsing in Node.js
     */
    private function parseInputXml(string $xml): array
    {
        // Clean up the XML input (same as Node version)
        $xml = preg_replace('/[\x0b\x1c]/m', '', $xml);
        $xml = preg_replace('/\t\s+/', ' ', (string) $xml);
        $xml = trim((string) $xml);

        // Validate XML structure
        if (!preg_match('/^<CCDA/i', $xml) || !preg_match('/<\/CCDA>$/i', $xml)) {
            throw new \RuntimeException("Invalid CCDA XML structure - missing CCDA root element");
        }

        // Parse XML to array using SimpleXML then convert to array
        libxml_use_internal_errors(true);
        $simpleXml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($simpleXml === false) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            $errorMsg = "XML parsing failed: ";
            foreach ($errors as $error) {
                $errorMsg .= $error->message . " ";
            }
            throw new \RuntimeException($errorMsg);
        }

        // Convert SimpleXML to array (similar to xml2js with mergeAttrs and no explicitArray)
        return $this->xmlToArray($simpleXml);
    }

    /**
     * Convert SimpleXMLElement to array
     *
     * Mimics xml2js behavior with {explicitArray: false, mergeAttrs: true}
     */
    private function xmlToArray(\SimpleXMLElement $xml): array
    {
        $result = [];
        $name = $xml->getName();

        // Get attributes and merge them into the result
        foreach ($xml->attributes() as $attrName => $attrValue) {
            $result[(string)$attrName] = (string)$attrValue;
        }

        // Process child elements
        $children = [];
        foreach ($xml->children() as $childName => $child) {
            $childArray = $this->xmlToArray($child);

            // Handle multiple children with same name
            if (isset($children[$childName])) {
                // Convert to array if not already
                if (!is_array($children[$childName]) || !isset($children[$childName][0])) {
                    $children[$childName] = [$children[$childName]];
                }
                $children[$childName][] = $childArray[$childName] ?? $childArray;
            } else {
                $children[$childName] = $childArray[$childName] ?? $childArray;
            }
        }

        // Get text content
        $text = trim((string)$xml);

        // Build result
        if (!empty($children)) {
            $result = array_merge($result, $children);
        } elseif ($text !== '') {
            // If no children but has text, return text directly (unless has attributes)
            if (empty($result)) {
                return [$name => $text];
            }
            $result['_text'] = $text;
        }

        // If result is empty and we have no text, return empty array
        if (empty($result) && $text === '') {
            return [$name => ''];
        }

        return [$name => $result];
    }

    /**
     * Generate unstructured CCDA document
     */
    private function generateUnstructured(array $ccdaData, string $xslUrl): string
    {
        // Set document type for unstructured
        $ccdaData['doc_type'] = 'unstructured';

        // Transform data for unstructured document
        $transformedData = $this->transformer->transformUnstructured($ccdaData);

        // Generate XML
        $xml = $this->templateEngine->generateCcd($transformedData);

        // Handle patient files template injection
        if (!empty($ccdaData['patient_files'])) {
            $filesTemplate = $this->buildPatientFilesTemplate($ccdaData['patient_files']);
            $xml = str_replace('</ClinicalDocument>', $filesTemplate . '</ClinicalDocument>', $xml);
        }

        return $xml;
    }

    /**
     * Build patient files template for unstructured documents
     */
    private function buildPatientFilesTemplate(array $patientFiles): string
    {
        // This would contain the embedded document components
        // The actual implementation depends on the patient_files structure
        return '';
    }

    /**
     * Apply XSL stylesheet processing instruction to XML header
     */
    private function applyXslHeader(string $xml, string $xslUrl): string
    {
        if (empty($xslUrl)) {
            return $xml;
        }

        // Find the XML declaration and add stylesheet PI after it
        $xslPi = '<?xml-stylesheet type="text/xsl" href="' . htmlspecialchars($xslUrl, ENT_XML1) . '"?>';

        if (preg_match('/^(<\?xml[^?]*\?>)/i', $xml, $matches)) {
            $xml = $matches[1] . "\n" . $xslPi . "\n" . substr($xml, strlen($matches[1]));
        } else {
            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $xslPi . "\n" . $xml;
        }

        return $xml;
    }

    /**
     * Write debug file
     */
    private function writeDebugFile(string $filename, string $content): void
    {
        $path = $this->documentLocation ?: ($GLOBALS['OE_SITE_DIR'] ?? '/tmp');
        $debugPath = $path . '/documents/temp/';

        if (!is_dir($debugPath)) {
            @mkdir($debugPath, 0755, true);
        }

        if (is_dir($debugPath) && is_writable($debugPath)) {
            file_put_contents($debugPath . $filename, $content);
        }
    }
}
