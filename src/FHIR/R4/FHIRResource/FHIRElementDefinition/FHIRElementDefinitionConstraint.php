<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRElementDefinition;

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
 * Captures constraints on each element within the resource, profile, or extension.
 * If the element is present, it must have a value for at least one of the defined elements, an @id referenced from the Narrative, or extensions
 */
class FHIRElementDefinitionConstraint extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Allows identification of which elements have their cardinalities impacted by the constraint.  Will not be referenced for constraints that do not affect cardinality.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public $key = null;

    /**
     * Description of why this constraint is necessary or appropriate.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $requirements = null;

    /**
     * Identifies the impact constraint violation has on the conformance of the instance.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRConstraintSeverity
     */
    public $severity = null;

    /**
     * Text that can be used to describe the constraint in messages identifying that the constraint has been violated.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $human = null;

    /**
     * A [FHIRPath](fhirpath.html) expression of constraint that can be executed to see if this constraint is met.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $expression = null;

    /**
     * An XPath expression of constraint that can be executed to see if this constraint is met.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $xpath = null;

    /**
     * A reference to the original source of the constraint, for traceability purposes.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical
     */
    public $source = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'ElementDefinition.Constraint';

    /**
     * Allows identification of which elements have their cardinalities impacted by the constraint.  Will not be referenced for constraints that do not affect cardinality.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Allows identification of which elements have their cardinalities impacted by the constraint.  Will not be referenced for constraints that do not affect cardinality.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRId $key
     * @return $this
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * Description of why this constraint is necessary or appropriate.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getRequirements()
    {
        return $this->requirements;
    }

    /**
     * Description of why this constraint is necessary or appropriate.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $requirements
     * @return $this
     */
    public function setRequirements($requirements)
    {
        $this->requirements = $requirements;
        return $this;
    }

    /**
     * Identifies the impact constraint violation has on the conformance of the instance.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRConstraintSeverity
     */
    public function getSeverity()
    {
        return $this->severity;
    }

    /**
     * Identifies the impact constraint violation has on the conformance of the instance.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRConstraintSeverity $severity
     * @return $this
     */
    public function setSeverity($severity)
    {
        $this->severity = $severity;
        return $this;
    }

    /**
     * Text that can be used to describe the constraint in messages identifying that the constraint has been violated.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getHuman()
    {
        return $this->human;
    }

    /**
     * Text that can be used to describe the constraint in messages identifying that the constraint has been violated.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $human
     * @return $this
     */
    public function setHuman($human)
    {
        $this->human = $human;
        return $this;
    }

    /**
     * A [FHIRPath](fhirpath.html) expression of constraint that can be executed to see if this constraint is met.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * A [FHIRPath](fhirpath.html) expression of constraint that can be executed to see if this constraint is met.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $expression
     * @return $this
     */
    public function setExpression($expression)
    {
        $this->expression = $expression;
        return $this;
    }

    /**
     * An XPath expression of constraint that can be executed to see if this constraint is met.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getXpath()
    {
        return $this->xpath;
    }

    /**
     * An XPath expression of constraint that can be executed to see if this constraint is met.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $xpath
     * @return $this
     */
    public function setXpath($xpath)
    {
        $this->xpath = $xpath;
        return $this;
    }

    /**
     * A reference to the original source of the constraint, for traceability purposes.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * A reference to the original source of the constraint, for traceability purposes.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical $source
     * @return $this
     */
    public function setSource($source)
    {
        $this->source = $source;
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
            if (isset($data['key'])) {
                $this->setKey($data['key']);
            }
            if (isset($data['requirements'])) {
                $this->setRequirements($data['requirements']);
            }
            if (isset($data['severity'])) {
                $this->setSeverity($data['severity']);
            }
            if (isset($data['human'])) {
                $this->setHuman($data['human']);
            }
            if (isset($data['expression'])) {
                $this->setExpression($data['expression']);
            }
            if (isset($data['xpath'])) {
                $this->setXpath($data['xpath']);
            }
            if (isset($data['source'])) {
                $this->setSource($data['source']);
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
    public function jsonSerialize()
    {
        $json = parent::jsonSerialize();
        if (isset($this->key)) {
            $json['key'] = $this->key;
        }
        if (isset($this->requirements)) {
            $json['requirements'] = $this->requirements;
        }
        if (isset($this->severity)) {
            $json['severity'] = $this->severity;
        }
        if (isset($this->human)) {
            $json['human'] = $this->human;
        }
        if (isset($this->expression)) {
            $json['expression'] = $this->expression;
        }
        if (isset($this->xpath)) {
            $json['xpath'] = $this->xpath;
        }
        if (isset($this->source)) {
            $json['source'] = $this->source;
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
            $sxe = new \SimpleXMLElement('<ElementDefinitionConstraint xmlns="http://hl7.org/fhir"></ElementDefinitionConstraint>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->key)) {
            $this->key->xmlSerialize(true, $sxe->addChild('key'));
        }
        if (isset($this->requirements)) {
            $this->requirements->xmlSerialize(true, $sxe->addChild('requirements'));
        }
        if (isset($this->severity)) {
            $this->severity->xmlSerialize(true, $sxe->addChild('severity'));
        }
        if (isset($this->human)) {
            $this->human->xmlSerialize(true, $sxe->addChild('human'));
        }
        if (isset($this->expression)) {
            $this->expression->xmlSerialize(true, $sxe->addChild('expression'));
        }
        if (isset($this->xpath)) {
            $this->xpath->xmlSerialize(true, $sxe->addChild('xpath'));
        }
        if (isset($this->source)) {
            $this->source->xmlSerialize(true, $sxe->addChild('source'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
