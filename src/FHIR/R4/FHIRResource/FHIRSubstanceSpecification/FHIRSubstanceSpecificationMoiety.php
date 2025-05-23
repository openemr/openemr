<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceSpecification;

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
 * The detailed description of a substance, typically at a level beyond what is used for prescribing.
 */
class FHIRSubstanceSpecificationMoiety extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Role that the moiety is playing.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $role = null;

    /**
     * Identifier by which this moiety substance is known.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier
     */
    public $identifier = null;

    /**
     * Textual name for this moiety substance.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $name = null;

    /**
     * Stereochemistry type.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $stereochemistry = null;

    /**
     * Optical activity type.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $opticalActivity = null;

    /**
     * Molecular formula.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $molecularFormula = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public $amountQuantity = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $amountString = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'SubstanceSpecification.Moiety';

    /**
     * Role that the moiety is playing.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Role that the moiety is playing.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $role
     * @return $this
     */
    public function setRole($role)
    {
        $this->role = $role;
        return $this;
    }

    /**
     * Identifier by which this moiety substance is known.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Identifier by which this moiety substance is known.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * Textual name for this moiety substance.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Textual name for this moiety substance.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Stereochemistry type.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getStereochemistry()
    {
        return $this->stereochemistry;
    }

    /**
     * Stereochemistry type.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $stereochemistry
     * @return $this
     */
    public function setStereochemistry($stereochemistry)
    {
        $this->stereochemistry = $stereochemistry;
        return $this;
    }

    /**
     * Optical activity type.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getOpticalActivity()
    {
        return $this->opticalActivity;
    }

    /**
     * Optical activity type.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $opticalActivity
     * @return $this
     */
    public function setOpticalActivity($opticalActivity)
    {
        $this->opticalActivity = $opticalActivity;
        return $this;
    }

    /**
     * Molecular formula.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getMolecularFormula()
    {
        return $this->molecularFormula;
    }

    /**
     * Molecular formula.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $molecularFormula
     * @return $this
     */
    public function setMolecularFormula($molecularFormula)
    {
        $this->molecularFormula = $molecularFormula;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public function getAmountQuantity()
    {
        return $this->amountQuantity;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity $amountQuantity
     * @return $this
     */
    public function setAmountQuantity($amountQuantity)
    {
        $this->amountQuantity = $amountQuantity;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getAmountString()
    {
        return $this->amountString;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $amountString
     * @return $this
     */
    public function setAmountString($amountString)
    {
        $this->amountString = $amountString;
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
            if (isset($data['role'])) {
                $this->setRole($data['role']);
            }
            if (isset($data['identifier'])) {
                $this->setIdentifier($data['identifier']);
            }
            if (isset($data['name'])) {
                $this->setName($data['name']);
            }
            if (isset($data['stereochemistry'])) {
                $this->setStereochemistry($data['stereochemistry']);
            }
            if (isset($data['opticalActivity'])) {
                $this->setOpticalActivity($data['opticalActivity']);
            }
            if (isset($data['molecularFormula'])) {
                $this->setMolecularFormula($data['molecularFormula']);
            }
            if (isset($data['amountQuantity'])) {
                $this->setAmountQuantity($data['amountQuantity']);
            }
            if (isset($data['amountString'])) {
                $this->setAmountString($data['amountString']);
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
        if (isset($this->role)) {
            $json['role'] = $this->role;
        }
        if (isset($this->identifier)) {
            $json['identifier'] = $this->identifier;
        }
        if (isset($this->name)) {
            $json['name'] = $this->name;
        }
        if (isset($this->stereochemistry)) {
            $json['stereochemistry'] = $this->stereochemistry;
        }
        if (isset($this->opticalActivity)) {
            $json['opticalActivity'] = $this->opticalActivity;
        }
        if (isset($this->molecularFormula)) {
            $json['molecularFormula'] = $this->molecularFormula;
        }
        if (isset($this->amountQuantity)) {
            $json['amountQuantity'] = $this->amountQuantity;
        }
        if (isset($this->amountString)) {
            $json['amountString'] = $this->amountString;
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
            $sxe = new \SimpleXMLElement('<SubstanceSpecificationMoiety xmlns="http://hl7.org/fhir"></SubstanceSpecificationMoiety>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->role)) {
            $this->role->xmlSerialize(true, $sxe->addChild('role'));
        }
        if (isset($this->identifier)) {
            $this->identifier->xmlSerialize(true, $sxe->addChild('identifier'));
        }
        if (isset($this->name)) {
            $this->name->xmlSerialize(true, $sxe->addChild('name'));
        }
        if (isset($this->stereochemistry)) {
            $this->stereochemistry->xmlSerialize(true, $sxe->addChild('stereochemistry'));
        }
        if (isset($this->opticalActivity)) {
            $this->opticalActivity->xmlSerialize(true, $sxe->addChild('opticalActivity'));
        }
        if (isset($this->molecularFormula)) {
            $this->molecularFormula->xmlSerialize(true, $sxe->addChild('molecularFormula'));
        }
        if (isset($this->amountQuantity)) {
            $this->amountQuantity->xmlSerialize(true, $sxe->addChild('amountQuantity'));
        }
        if (isset($this->amountString)) {
            $this->amountString->xmlSerialize(true, $sxe->addChild('amountString'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
