<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 *
 * Class creation date: April 15th, 2026 16:02+0000
 *
 * PHPFHIR Copyright:
 *
 * Copyright 2016-2026 Daniel Carbone (daniel.p.carbone@gmail.com)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 */

use OpenEMR\FHIR\FHIRVersion;
use OpenEMR\FHIR\Types\TypeInterface;
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Version;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRXHTML implements TypeInterface
{
    use TypeValidationsTrait;

    private const _FHIR_VALIDATION_RULES = [];

    /** @var string */
    protected string $value;

    /**
     * FHIRXHTML Constructor
     * @param null|string|\DOMNode|\SimpleXMLElement $value
     */
    public function __construct(null|string|\DOMNode|\SimpleXmlElement $value = null)
    {
        $this->setValue($value);
    }

    /**
     * @return string
     */
    public function _getFHIRTypeName(): string
    {
        return 'XHTML';
    }

    /* class_xhtml.php:82 */
    public function _getFHIRVersion(): FHIRVersion
    {
        return Version::getFHIRVersion();
    }

    /**
     * @return null|string
     */
    public function getValue(): null|string
    {
        return $this->value ?? null;
    }

    /**
     * Set the full XHTML content of this element.
     *
     * @param null|string|\DOMNode|\SimpleXmlElement $value
     * @return static
     */
    public function setValue(null|string|\DOMNode|\SimpleXMLElement $value): self
    {
        if (null === $value) {
            unset($this->value);
            return $this;
        }
        if ($value instanceof \DOMDocument) {
            $value = $value->saveXML($value->documentElement);
        } else if ($value instanceof \DOMNode) {
            $value = $value->ownerDocument->saveXML($value);
        } else if ($value instanceof \SimpleXMLElement) {
            $value = $value->asXML();
        }
        $this->value = $value;
        return $this;
    }

    /**
     * @param int $libxmlOpts libxml options mask
     * @return null|\SimpleXMLElement
     * @throws \Exception
     */
    public function getSimpleXMLElement(int $libxmlOpts): null|\SimpleXMLElement
    {
        if (!isset($this->value)) {
            return null;
        }
        return new \SimpleXMLElement($this->value, $libxmlOpts);
    }

    /**
     * @param int $libxmlOpts libxml options mask
     * @return null|\DOMDocument
     */
    public function getDOMDocument(int $libxmlOpts): null|\DOMDocument
    {
        if (!isset($this->value)) {
            return null;
        }
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->loadXML($this->value, $libxmlOpts);
        return $dom;
    }

    /**
     * @param int $libxmlOpts libxml options mask
     * @return null|\XMLReader
     */
    public function getXMLReader(int $libxmlOpts): null|\XMLReader
    {
        if (!isset($this->value)) {
            return null;
        }
        $xr = \XMLReader::XML($this->value, 'UTF-8', $libxmlOpts);
        $xr->read();
        return $xr;
    }

    /**
     * @return null|string
     */
    public function jsonSerialize(): null|string
    {
        if (!isset($this->value)) {
            return null;
        }
        return $this->value;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->getValue();
    }
}
