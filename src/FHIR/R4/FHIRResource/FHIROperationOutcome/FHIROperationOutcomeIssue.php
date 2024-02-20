<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIROperationOutcome;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 *
 * Class creation date: June 14th, 2019
 *
 * PHPFHIR Copyright:
 *
 * Copyright 2016-2017 Daniel Carbone (daniel.p.carbone@gmail.com)
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
 *
 * FHIR Copyright Notice:
 *
 *   Copyright (c) 2011+, HL7, Inc.
 *   All rights reserved.
 *
 *   Redistribution and use in source and binary forms, with or without modification,
 *   are permitted provided that the following conditions are met:
 *
 *    * Redistributions of source code must retain the above copyright notice, this
 *      list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright notice,
 *      this list of conditions and the following disclaimer in the documentation
 *      and/or other materials provided with the distribution.
 *    * Neither the name of HL7 nor the names of its contributors may be used to
 *      endorse or promote products derived from this software without specific
 *      prior written permission.
 *
 *   THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 *   ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 *   WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 *   IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 *   INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 *   NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 *   PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 *   WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 *   ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 *   POSSIBILITY OF SUCH DAMAGE.
 *
 *
 *   Generated on Thu, Dec 27, 2018 22:37+1100 for FHIR v4.0.0
 *
 *   Note: the schemas & schematrons do not contain all of the rules about what makes resources
 *   valid. Implementers will still need to be familiar with the content of the specification and with
 *   any profiles that apply to the resources in order to make a conformant implementation.
 *
 */

use OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement;

/**
 * A collection of error, warning, or information messages that result from a system action.
 */
class FHIROperationOutcomeIssue extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Indicates whether the issue indicates a variation from successful processing.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIssueSeverity
     */
    public $severity = null;

    /**
     * Describes the type of the issue. The system that creates an OperationOutcome SHALL choose the most applicable code from the IssueType value set, and may additional provide its own code for the error in the details element.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIssueType
     */
    public $code = null;

    /**
     * Additional details about the error. This may be a text description of the error or a system code that identifies the error.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $details = null;

    /**
     * Additional diagnostic information about the issue.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $diagnostics = null;

    /**
     * This element is deprecated because it is XML specific. It is replaced by issue.expression, which is format independent, and simpler to parse.

For resource issues, this will be a simple XPath limited to element names, repetition indicators and the default child accessor that identifies one of the elements in the resource that caused this issue to be raised.  For HTTP errors, will be "http." + the parameter name.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public $location = [];

    /**
     * A [simple subset of FHIRPath](fhirpath.html#simple) limited to element names, repetition indicators and the default child accessor that identifies one of the elements in the resource that caused this issue to be raised.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public $expression = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'OperationOutcome.Issue';

    /**
     * Indicates whether the issue indicates a variation from successful processing.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIssueSeverity
     */
    public function getSeverity()
    {
        return $this->severity;
    }

    /**
     * Indicates whether the issue indicates a variation from successful processing.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIssueSeverity $severity
     * @return $this
     */
    public function setSeverity($severity)
    {
        $this->severity = $severity;
        return $this;
    }

    /**
     * Describes the type of the issue. The system that creates an OperationOutcome SHALL choose the most applicable code from the IssueType value set, and may additional provide its own code for the error in the details element.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIssueType
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Describes the type of the issue. The system that creates an OperationOutcome SHALL choose the most applicable code from the IssueType value set, and may additional provide its own code for the error in the details element.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIssueType $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * Additional details about the error. This may be a text description of the error or a system code that identifies the error.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * Additional details about the error. This may be a text description of the error or a system code that identifies the error.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $details
     * @return $this
     */
    public function setDetails($details)
    {
        $this->details = $details;
        return $this;
    }

    /**
     * Additional diagnostic information about the issue.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getDiagnostics()
    {
        return $this->diagnostics;
    }

    /**
     * Additional diagnostic information about the issue.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $diagnostics
     * @return $this
     */
    public function setDiagnostics($diagnostics)
    {
        $this->diagnostics = $diagnostics;
        return $this;
    }

    /**
     * This element is deprecated because it is XML specific. It is replaced by issue.expression, which is format independent, and simpler to parse.

For resource issues, this will be a simple XPath limited to element names, repetition indicators and the default child accessor that identifies one of the elements in the resource that caused this issue to be raised.  For HTTP errors, will be "http." + the parameter name.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * This element is deprecated because it is XML specific. It is replaced by issue.expression, which is format independent, and simpler to parse.

For resource issues, this will be a simple XPath limited to element names, repetition indicators and the default child accessor that identifies one of the elements in the resource that caused this issue to be raised.  For HTTP errors, will be "http." + the parameter name.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $location
     * @return $this
     */
    public function addLocation($location)
    {
        $this->location[] = $location;
        return $this;
    }

    /**
     * A [simple subset of FHIRPath](fhirpath.html#simple) limited to element names, repetition indicators and the default child accessor that identifies one of the elements in the resource that caused this issue to be raised.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * A [simple subset of FHIRPath](fhirpath.html#simple) limited to element names, repetition indicators and the default child accessor that identifies one of the elements in the resource that caused this issue to be raised.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $expression
     * @return $this
     */
    public function addExpression($expression)
    {
        $this->expression[] = $expression;
        return $this;
    }

    /**
     * @return string
     */
    public function get_fhirElementName()
    {
        return $this->_fhirElementName;
    }

    /**
     * @param mixed $data
     */
    public function __construct($data = [])
    {
        if (is_array($data)) {
            if (isset($data['severity'])) {
                $this->setSeverity($data['severity']);
            }
            if (isset($data['code'])) {
                $this->setCode($data['code']);
            }
            if (isset($data['details'])) {
                $this->setDetails($data['details']);
            }
            if (isset($data['diagnostics'])) {
                $this->setDiagnostics($data['diagnostics']);
            }
            if (isset($data['location'])) {
                if (is_array($data['location'])) {
                    foreach ($data['location'] as $d) {
                        $this->addLocation($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"location" must be array of objects or null, ' . gettype($data['location']) . ' seen.');
                }
            }
            if (isset($data['expression'])) {
                if (is_array($data['expression'])) {
                    foreach ($data['expression'] as $d) {
                        $this->addExpression($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"expression" must be array of objects or null, ' . gettype($data['expression']) . ' seen.');
                }
            }
        } elseif (null !== $data) {
            throw new \InvalidArgumentException('$data expected to be array of values, saw "' . gettype($data) . '"');
        }
        parent::__construct($data);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->get_fhirElementName();
    }

    /**
     * @return array
     */
    public function jsonSerialize(): mixed
    {
        $json = parent::jsonSerialize();
        if (isset($this->severity)) {
            $json['severity'] = $this->severity;
        }
        if (isset($this->code)) {
            $json['code'] = $this->code;
        }
        if (isset($this->details)) {
            $json['details'] = $this->details;
        }
        if (isset($this->diagnostics)) {
            $json['diagnostics'] = $this->diagnostics;
        }
        if (0 < count($this->location)) {
            $json['location'] = [];
            foreach ($this->location as $location) {
                $json['location'][] = $location;
            }
        }
        if (0 < count($this->expression)) {
            $json['expression'] = [];
            foreach ($this->expression as $expression) {
                $json['expression'][] = $expression;
            }
        }
        return $json;
    }

    /**
     * @param boolean $returnSXE
     * @param \SimpleXMLElement $sxe
     * @return string|\SimpleXMLElement
     */
    public function xmlSerialize($returnSXE = false, $sxe = null)
    {
        if (null === $sxe) {
            $sxe = new \SimpleXMLElement('<OperationOutcomeIssue xmlns="http://hl7.org/fhir"></OperationOutcomeIssue>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->severity)) {
            $this->severity->xmlSerialize(true, $sxe->addChild('severity'));
        }
        if (isset($this->code)) {
            $this->code->xmlSerialize(true, $sxe->addChild('code'));
        }
        if (isset($this->details)) {
            $this->details->xmlSerialize(true, $sxe->addChild('details'));
        }
        if (isset($this->diagnostics)) {
            $this->diagnostics->xmlSerialize(true, $sxe->addChild('diagnostics'));
        }
        if (0 < count($this->location)) {
            foreach ($this->location as $location) {
                $location->xmlSerialize(true, $sxe->addChild('location'));
            }
        }
        if (0 < count($this->expression)) {
            foreach ($this->expression as $expression) {
                $expression->xmlSerialize(true, $sxe->addChild('expression'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
