<?php

/**
 * CcdaXmlBuilder.php - DOMDocument Wrapper for CCDA XML Construction
 *
 * Provides clean XML generation with proper namespace handling.
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

class CcdaXmlBuilder
{
    private readonly DOMDocument $doc;
    
    public const NS_CDA = 'urn:hl7-org:v3';
    public const NS_SDTC = 'urn:hl7-org:sdtc';
    public const NS_XSI = 'http://www.w3.org/2001/XMLSchema-instance';

    public function __construct()
    {
        $this->doc = new DOMDocument('1.0', 'UTF-8');
        $this->doc->formatOutput = true;
    }

    public function getDocument(): DOMDocument
    {
        return $this->doc;
    }

    public function createRoot(string $name): DOMElement
    {
        $root = $this->doc->createElementNS(self::NS_CDA, $name);
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:sdtc', self::NS_SDTC);
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', self::NS_XSI);
        $this->doc->appendChild($root);
        return $root;
    }

    public function createElement(string $name, ?string $text = null): DOMElement
    {
        $element = $this->doc->createElement($name);
        if ($text !== null && $text !== '') {
            $element->appendChild($this->doc->createTextNode($text));
        }
        return $element;
    }

    public function addElement(DOMElement $parent, string $name, ?string $text = null): DOMElement
    {
        $element = $this->createElement($name, $text);
        $parent->appendChild($element);
        return $element;
    }

    public function setAttributes(DOMElement $element, array $attrs): void
    {
        foreach ($attrs as $name => $value) {
            if ($value !== null && $value !== '') {
                $element->setAttribute($name, (string)$value);
            }
        }
    }

    public function toString(): string
    {
        return $this->doc->saveXML();
    }
}
