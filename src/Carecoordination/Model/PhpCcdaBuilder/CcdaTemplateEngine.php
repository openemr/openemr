<?php

/**
 * CcdaTemplateEngine.php - Template Processing Engine for CCDA Generation
 *
 * This class is a PHP port of engine.js from oe-blue-button-generate.
 * It processes template definitions and generates XML using DOMDocument.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Carecoordination\Model\PhpCcdaBuilder;

use DOMDocument;
use DOMElement;
use OpenEMR\Carecoordination\Model\PhpCcdaBuilder\Templates\DocumentLevel;

class CcdaTemplateEngine
{
    private DOMDocument $doc;
    private array $context = [];
    private bool $preventNullFlavor = false;
    private string $stylesheetPath = "../../../interface/modules/zend_modules/public/xsl/cda.xsl";

    /**
     * Set the XSL stylesheet path for the XML output
     *
     * @param string $path The path to the stylesheet (relative or absolute URL)
     */
    public function setStylesheetPath(string $path): self
    {
        $this->stylesheetPath = $path;
        return $this;
    }

    /**
     * Cleans a CCDA XML document for use with Laminas XML or DOMDocument.
     * Optionally removes or replaces <br/> tags.
     *
     * @param string $xmlContent The raw CCDA XML string.
     * @param bool   $removeBr   Whether to remove <br/> tags. Defaults to false.
     * @return string Cleaned XML content.
     * @throws Exception If the input XML is invalid or cannot be parsed.
     */
    protected function cleanCcdaXmlContent(string $xmlContent, bool $replaceBr = false): string
    {
        // Handle <br/> tags if required
        if ($replaceBr) {
            $xmlContent = preg_replace('/<\/?br\s*\/?>/i', '', $xmlContent);
        } else {
            $xmlContent = preg_replace('/<\/?br\s*\/?>/i', '\n', $xmlContent); // Replace <br/> with newline
        }
        $xmlContent = preg_replace('/\xC2\xA0/', '', (string)$xmlContent);
        $xmlContent = str_replace('Ã‚', '', $xmlContent);

        // Load the raw XML into DOMDocument for further cleaning
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        libxml_use_internal_errors(true);
        if (!$dom->loadXML($xmlContent, LIBXML_NOERROR | LIBXML_NOWARNING)) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            throw new Exception("Invalid XML provided: " . implode(", ", array_map(fn($e): string => $e->message, $errors)));
        }
        // Normalize and ensure UTF-8 encoding
        $dom->encoding = 'UTF-8';

        return $dom->saveXML();
    }

    /**
     * Generate a CCD document from transformed data
     *
     * @param array $data The transformed CCDA data
     * @return string The generated XML
     */
    public function generateCcd(array $data): string
    {
        // Initialize DOM document
        $this->doc = new DOMDocument('1.0', 'UTF-8');
        $this->doc->formatOutput = true;
        $this->doc->preserveWhiteSpace = false;

        // Determine document type and get appropriate template
        $docType = $data['meta']['type'] ?? 'ccd';
        $templateRoot = $data['meta']['ccda_header']['template']['root'] ?? '';

        if ($templateRoot === '2.16.840.1.113883.10.20.22.1.10' || $docType === 'unstructured') {
            $template = DocumentLevel::unstructured();
        } else {
            $template = DocumentLevel::ccd2();
        }

        // Initialize context for reference tracking
        $this->context = [
            'references' => [],
            'tableReferences' => [],
            'rootId' => $data['meta']['identifiers'][0]['identifier'] ?? null,
            'preventNullFlavor' => false,
        ];

        // Process the template
        $this->update($this->doc, $data, $this->context, $template);

        // Get the XML output
        $xml = $this->doc->saveXML();
        //$xml = $this->cleanCcdaXmlContent($xml, true);
       // $xsl = file_get_contents($this->stylesheetPath);

        // Insert stylesheet processing instruction after XML declaration
        if (!empty($this->stylesheetPath)) {
            $stylesheetPI = '<?xml-stylesheet type="text/xsl" href="' . htmlspecialchars($this->stylesheetPath) . '"?>';
            $xml = preg_replace('/^(<\?xml[^?]*\?>)/', '$1' . "\n" . $stylesheetPI, $xml);
        }

        return $xml;
    }

    /**
     * Main update function - processes a template against input data
     *
     * This is the PHP equivalent of engine.js update() function.
     *
     * @param DOMDocument|DOMElement $xmlDoc   The XML document or parent element
     * @param mixed                  $input    The input data
     * @param array                  $context  Processing context
     * @param array                  $template The template definition
     * @return bool Whether any content was added
     */
    public function update($xmlDoc, $input, array $context, array $template): bool
    {
        $filled = false;

        if ($input !== null) {
            $input = $this->transformInput($input, $template);

            if ($input !== null) {
                if (is_array($input) && isset($input[0])) {
                    // Array of items
                    foreach ($input as $element) {
                        $filled = $this->updateUsingTemplate($xmlDoc, $element, $context, $template) || $filled;
                    }
                } else {
                    $filled = $this->updateUsingTemplate($xmlDoc, $input, $context, $template);
                }
            }
        }

        // Handle required but missing elements
        if (!$filled && ($template['required'] ?? false) && !($context['preventNullFlavor'] ?? false)) {
            $node = $this->newNode($xmlDoc, $template['key']);
            $this->nodeAttr($node, ['nullFlavor' => 'UNK']);
        }

        return $filled;
    }

    /**
     * Update XML document using a single template
     */
    private function updateUsingTemplate($xmlDoc, $input, array $context, array $template): bool
    {
        // Check condition
        $condition = $template['existsWhen'] ?? null;
        if ($condition !== null && is_callable($condition)) {
            if (!$condition($input, $context)) {
                return false;
            }
        }

        $name = $template['key'] ?? null;
        if ($name === null) {
            return false;
        }

        $text = $this->expandText($input, $template);

        if ($text !== null || isset($template['content']) || isset($template['attributes'])) {
            $node = $this->newNode($xmlDoc, $name, $text);

            $this->fillAttributes($node, $input, $context, $template);
            $this->fillContent($node, $input, $context, $template);

            return true;
        }

        return false;
    }

    /**
     * Transform input data based on template dataKey and dataTransform
     */
    private function transformInput($input, array $template)
    {
        $inputKey = $template['dataKey'] ?? null;

        if ($inputKey !== null) {
            $pieces = explode('.', $inputKey);

            foreach ($pieces as $piece) {
                if (is_array($input) && $this->isIndexedArray($input) && $piece !== '0') {
                    // Handle array input with dot navigation
                    $nextInputs = [];
                    foreach ($input as $inputElement) {
                        if (is_array($inputElement) && isset($inputElement[$piece])) {
                            $nextInput = $inputElement[$piece];
                            if (is_array($nextInput) && $this->isIndexedArray($nextInput)) {
                                foreach ($nextInput as $nextInputElement) {
                                    if ($nextInputElement !== null) {
                                        $nextInputs[] = $nextInputElement;
                                    }
                                }
                            } else {
                                $nextInputs[] = $nextInput;
                            }
                        }
                    }
                    $input = empty($nextInputs) ? null : $nextInputs;
                } else {
                    $input = is_array($input) && array_key_exists($piece, $input) ? $input[$piece] : null;
                }

                if ($input === null) {
                    break;
                }
            }
        }

        // Apply transform function if exists
        $transform = $template['dataTransform'] ?? null;
        if ($input !== null && $transform !== null) {
            $input = $this->invokeCallable($transform, $input);
        }

        return $input;
    }

    /**
     * Check if array is numerically indexed (sequential)
     */
    private function isIndexedArray($arr): bool
    {
        if (!is_array($arr)) {
            return false;
        }
        if (empty($arr)) {
            return false;
        }
        return array_keys($arr) === range(0, count($arr) - 1);
    }

    /**
     * Invoke a callable - handles closures, array callables, factory methods
     *
     * Array callables can be:
     * - [ClassName::class, 'methodName'] - static method call
     * - [ClassName::class, 'methodName', 'arg1', ...] - factory that returns callable
     */
    private function invokeCallable($callable, $input, $context = null)
    {
        // Direct closure
        if ($callable instanceof \Closure) {
            return $context !== null ? $callable($input, $context) : $callable($input);
        }

        // Array-style callable
        if (is_array($callable) && count($callable) >= 2 && is_string($callable[0])) {
            $class = $callable[0];
            $method = $callable[1];

            // Check if class exists
            if (!class_exists($class)) {
                return null;
            }

            // If there are additional arguments, it's a factory method
            // e.g., [LeafLevel::class, 'inputProperty', 'family'] 
            // -> LeafLevel::inputProperty('family') returns a closure
            if (count($callable) > 2) {
                $args = array_slice($callable, 2);
                $factory = call_user_func([$class, $method], ...$args);

                // The factory returns a closure - invoke it with input
                if ($factory instanceof \Closure || is_callable($factory)) {
                    return $factory($input);
                }
                return $factory;
            }

            // Direct static method call with input
            // e.g., [Translate::class, 'name'] -> Translate::name($input)
            return call_user_func([$class, $method], $input);
        }

        // Standard callable
        if (is_callable($callable)) {
            return $context !== null ? $callable($input, $context) : $callable($input);
        }

        return $input;
    }

    /**
     * Expand text content from template
     */
    private function expandText($input, array $template): ?string
    {
        $text = $template['text'] ?? null;

        if ($text === null) {
            return null;
        }

        // Invoke the text callable/value
        if ($text instanceof \Closure || is_array($text) || is_callable($text)) {
            $text = $this->invokeCallable($text, $input);
        }

        // Handle the result
        if ($text === null || $text === '') {
            return null;
        }

        // If result is an array, we can't use it as text
        // This shouldn't happen with proper templates
        if (is_array($text)) {
            // Try to get first element if indexed array of strings
            if (isset($text[0]) && is_string($text[0])) {
                return $text[0];
            }
            // Otherwise skip - this element needs iteration, not text
            return null;
        }

        return (string)$text;
    }

    /**
     * Fill element attributes
     */
    private function fillAttributes(DOMElement $node, $input, array $context, array $template): void
    {
        $attrObj = $template['attributes'] ?? null;

        if ($attrObj === null) {
            return;
        }

        // Handle attributeKey
        $inputAttrKey = $template['attributeKey'] ?? null;
        if ($inputAttrKey !== null && is_array($input)) {
            $input = $input[$inputAttrKey] ?? $input;
        }

        if ($input !== null) {
            $attrs = [];
            $this->expandAttributes($input, $context, $attrObj, $attrs);
            $this->nodeAttr($node, $attrs);
        }
    }

    /**
     * Expand attributes from definition
     */
    private function expandAttributes($input, array $context, $attrObj, array &$attrs): void
    {
        if ($attrObj === null) {
            return;
        }

        if (is_array($attrObj) && $this->isIndexedArray($attrObj)) {
            // Array of attribute objects
            foreach ($attrObj as $attrObjElem) {
                $this->expandAttributes($input, $context, $attrObjElem, $attrs);
            }
        } elseif ($attrObj instanceof \Closure || (is_array($attrObj) && isset($attrObj[0]) && is_string($attrObj[0]) && class_exists($attrObj[0]))) {
            // Function that returns attributes (closure or array callable)
            $result = $this->invokeCallable($attrObj, $input, $context);
            if ($result !== null && is_array($result)) {
                $this->expandAttributes($input, $context, $result, $attrs);
            }
        } elseif (is_array($attrObj)) {
            // Direct attribute object (associative array)
            foreach ($attrObj as $attrKey => $attrVal) {
                if ($attrVal instanceof \Closure || is_callable($attrVal)) {
                    $attrVal = $this->invokeCallable($attrVal, $input, $context);
                }
                if ($attrVal !== null && $attrVal !== '') {
                    $attrs[$attrKey] = (string)$attrVal;
                }
            }
        }
    }

    /**
     * Fill element content (child elements)
     */
    private function fillContent(DOMElement $node, $input, array $context, array $template): void
    {
        $content = $template['content'] ?? null;

        if ($content === null) {
            return;
        }

        if (!is_array($content) || !isset($content[0])) {
            $content = [$content];
        }

        foreach ($content as $element) {
            if (is_array($element) && isset($element[0]) && is_array($element[0])) {
                // Template with modifiers
                $actualElement = $element[0];
                for ($i = 1; $i < count($element); $i++) {
                    if (is_callable($element[$i])) {
                        $element[$i]($actualElement);
                    }
                }
                $this->update($node, $input, $context, $actualElement);
            } else {
                $this->update($node, $input, $context, $element);
            }
        }
    }

    /**
     * Default namespace for CDA elements
     */
    private const CDA_NAMESPACE = 'urn:hl7-org:v3';

    /**
     * SDTC extension namespace
     */
    private const SDTC_NAMESPACE = 'urn:hl7-org:sdtc';

    /**
     * Create a new XML element with proper namespace handling
     */
    private function newNode($parent, string $name, ?string $text = null): DOMElement
    {
        $doc = ($parent instanceof DOMDocument) ? $parent : $parent->ownerDocument;
        
        // Only use createElementNS for root element (ClinicalDocument) and sdtc: elements
        // All other elements inherit namespace from root
        if ($parent instanceof DOMDocument && $name === 'ClinicalDocument') {
            // Root element - create with namespace and set all namespace declarations
            $node = $doc->createElementNS(self::CDA_NAMESPACE, $name);
            $node->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
            $node->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:sdtc', self::SDTC_NAMESPACE);
        } elseif (str_starts_with($name, 'sdtc:')) {
            // SDTC namespace elements - need explicit namespace
            $node = $doc->createElementNS(self::SDTC_NAMESPACE, $name);
        } else {
            // All other elements - use createElement (inherits default namespace)
            $node = $doc->createElement($name);
        }

        $parent->appendChild($node);

        // Only add text if it's not null and not just whitespace
        if ($text !== null) {
            $trimmed = trim($text);
            if ($trimmed !== '') {
                // Normalize multiple spaces to single space
                $normalized = preg_replace('/\s+/', ' ', $text);
                $node->appendChild($doc->createTextNode(trim((string) $normalized)));
            }
        }

        return $node;
    }

    /**
     * Set attributes on a node
     */
    private function nodeAttr(DOMElement $node, array $attrs): void
    {
        foreach ($attrs as $name => $value) {
            if ($value !== null && $value !== '') {
                $node->setAttribute($name, $value);
            }
        }
    }

    /**
     * Create document from template
     *
     * This is the main entry point equivalent to engine.js exports.create()
     */
    public function create(array $template, $input, array $context = []): string
    {
        $this->doc = new DOMDocument('1.0', 'UTF-8');
        $this->doc->formatOutput = true;

        // Merge provided context with defaults
        $this->context = array_merge([
            'references' => [],
            'tableReferences' => [],
            'rootId' => null,
            'preventNullFlavor' => false,
        ], $context);

        $this->update($this->doc, $input, $this->context, $template);

        return $this->doc->saveXML();
    }

    /**
     * Get next reference for a key (used in narrative text)
     */
    public static function nextReference(array &$context, string $referenceKey): string
    {
        $index = ($context['references'][$referenceKey] ?? 0) + 1;
        $context['references'][$referenceKey] = $index;
        return '#' . $referenceKey . $index;
    }

    /**
     * Get same reference for a key (current index)
     */
    public static function sameReference(array &$context, string $referenceKey): string
    {
        $index = $context['references'][$referenceKey] ?? 0;
        return '#' . $referenceKey . $index;
    }

    /**
     * Get next table reference for a key
     */
    public static function nextTableReference(array &$context, string $referenceKey): string
    {
        $index = ($context['tableReferences'][$referenceKey] ?? 0) + 1;
        $context['tableReferences'][$referenceKey] = $index;
        return $referenceKey . $index;
    }
}
