<?php

namespace OpenEMR\FHIR\R4\FHIRElement;

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

use OpenEMR\FHIR\R4\FHIRElement;

/**
 * An identifier - identifies some entity uniquely and unambiguously. Typically this is used for business identifiers.
 * If the element is present, it must have a value for at least one of the defined elements, an @id referenced from the Narrative, or extensions
 */
class FHIRIdentifier extends FHIRElement implements \JsonSerializable
{
    /**
     * The purpose of this identifier.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifierUse
     */
    public $use = null;

    /**
     * A coded type for the identifier that can be used to determine which identifier to use for a specific purpose.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $type = null;

    /**
     * Establishes the namespace for the value - that is, a URL that describes a set values that are unique.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public $system = null;

    /**
     * The portion of the identifier typically relevant to the user and which is unique within the context of the system.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $value = null;

    /**
     * Time period during which identifier is/was valid for use.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public $period = null;

    /**
     * Organization that issued/manages the identifier.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $assigner = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Identifier';

    /**
     * The purpose of this identifier.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifierUse
     */
    public function getUse()
    {
        return $this->use;
    }

    /**
     * The purpose of this identifier.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifierUse $use
     * @return $this
     */
    public function setUse($use)
    {
        $this->use = $use;
        return $this;
    }

    /**
     * A coded type for the identifier that can be used to determine which identifier to use for a specific purpose.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * A coded type for the identifier that can be used to determine which identifier to use for a specific purpose.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Establishes the namespace for the value - that is, a URL that describes a set values that are unique.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public function getSystem()
    {
        return $this->system;
    }

    /**
     * Establishes the namespace for the value - that is, a URL that describes a set values that are unique.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUri $system
     * @return $this
     */
    public function setSystem($system)
    {
        $this->system = $system;
        return $this;
    }

    /**
     * The portion of the identifier typically relevant to the user and which is unique within the context of the system.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * The portion of the identifier typically relevant to the user and which is unique within the context of the system.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Time period during which identifier is/was valid for use.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * Time period during which identifier is/was valid for use.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod $period
     * @return $this
     */
    public function setPeriod($period)
    {
        $this->period = $period;
        return $this;
    }

    /**
     * Organization that issued/manages the identifier.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getAssigner()
    {
        return $this->assigner;
    }

    /**
     * Organization that issued/manages the identifier.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $assigner
     * @return $this
     */
    public function setAssigner($assigner)
    {
        $this->assigner = $assigner;
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
            if (isset($data['use'])) {
                $this->setUse($data['use']);
            }
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['system'])) {
                $this->setSystem($data['system']);
            }
            if (isset($data['value'])) {
                $this->setValue($data['value']);
            }
            if (isset($data['period'])) {
                $this->setPeriod($data['period']);
            }
            if (isset($data['assigner'])) {
                $this->setAssigner($data['assigner']);
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
        return (string)$this->getValue();
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $json = parent::jsonSerialize();
        if (isset($this->use)) {
            $json['use'] = $this->use;
        }
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->system)) {
            $json['system'] = $this->system;
        }
        if (isset($this->value)) {
            $json['value'] = $this->value;
        }
        if (isset($this->period)) {
            $json['period'] = $this->period;
        }
        if (isset($this->assigner)) {
            $json['assigner'] = $this->assigner;
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
            $sxe = new \SimpleXMLElement('<Identifier xmlns="http://hl7.org/fhir"></Identifier>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->use)) {
            $this->use->xmlSerialize(true, $sxe->addChild('use'));
        }
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->system)) {
            $this->system->xmlSerialize(true, $sxe->addChild('system'));
        }
        if (isset($this->value)) {
            $this->value->xmlSerialize(true, $sxe->addChild('value'));
        }
        if (isset($this->period)) {
            $this->period->xmlSerialize(true, $sxe->addChild('period'));
        }
        if (isset($this->assigner)) {
            $this->assigner->xmlSerialize(true, $sxe->addChild('assigner'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
