<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRSubstanceReferenceInformation;

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
 * Todo.
 */
class FHIRSubstanceReferenceInformationTarget extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Todo.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier
     */
    public $target = null;

    /**
     * Todo.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $type = null;

    /**
     * Todo.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $interaction = null;

    /**
     * Todo.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $organism = null;

    /**
     * Todo.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $organismType = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public $amountQuantity = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRRange
     */
    public $amountRange = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $amountString = null;

    /**
     * Todo.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $amountType = null;

    /**
     * Todo.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $source = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'SubstanceReferenceInformation.Target';

    /**
     * Todo.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Todo.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $target
     * @return $this
     */
    public function setTarget($target)
    {
        $this->target = $target;
        return $this;
    }

    /**
     * Todo.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Todo.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Todo.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getInteraction()
    {
        return $this->interaction;
    }

    /**
     * Todo.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $interaction
     * @return $this
     */
    public function setInteraction($interaction)
    {
        $this->interaction = $interaction;
        return $this;
    }

    /**
     * Todo.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getOrganism()
    {
        return $this->organism;
    }

    /**
     * Todo.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $organism
     * @return $this
     */
    public function setOrganism($organism)
    {
        $this->organism = $organism;
        return $this;
    }

    /**
     * Todo.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getOrganismType()
    {
        return $this->organismType;
    }

    /**
     * Todo.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $organismType
     * @return $this
     */
    public function setOrganismType($organismType)
    {
        $this->organismType = $organismType;
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
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRRange
     */
    public function getAmountRange()
    {
        return $this->amountRange;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRRange $amountRange
     * @return $this
     */
    public function setAmountRange($amountRange)
    {
        $this->amountRange = $amountRange;
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
     * Todo.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getAmountType()
    {
        return $this->amountType;
    }

    /**
     * Todo.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $amountType
     * @return $this
     */
    public function setAmountType($amountType)
    {
        $this->amountType = $amountType;
        return $this;
    }

    /**
     * Todo.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Todo.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $source
     * @return $this
     */
    public function addSource($source)
    {
        $this->source[] = $source;
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
            if (isset($data['target'])) {
                $this->setTarget($data['target']);
            }
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['interaction'])) {
                $this->setInteraction($data['interaction']);
            }
            if (isset($data['organism'])) {
                $this->setOrganism($data['organism']);
            }
            if (isset($data['organismType'])) {
                $this->setOrganismType($data['organismType']);
            }
            if (isset($data['amountQuantity'])) {
                $this->setAmountQuantity($data['amountQuantity']);
            }
            if (isset($data['amountRange'])) {
                $this->setAmountRange($data['amountRange']);
            }
            if (isset($data['amountString'])) {
                $this->setAmountString($data['amountString']);
            }
            if (isset($data['amountType'])) {
                $this->setAmountType($data['amountType']);
            }
            if (isset($data['source'])) {
                if (is_array($data['source'])) {
                    foreach ($data['source'] as $d) {
                        $this->addSource($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"source" must be array of objects or null, ' . gettype($data['source']) . ' seen.');
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
        if (isset($this->target)) {
            $json['target'] = $this->target;
        }
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->interaction)) {
            $json['interaction'] = $this->interaction;
        }
        if (isset($this->organism)) {
            $json['organism'] = $this->organism;
        }
        if (isset($this->organismType)) {
            $json['organismType'] = $this->organismType;
        }
        if (isset($this->amountQuantity)) {
            $json['amountQuantity'] = $this->amountQuantity;
        }
        if (isset($this->amountRange)) {
            $json['amountRange'] = $this->amountRange;
        }
        if (isset($this->amountString)) {
            $json['amountString'] = $this->amountString;
        }
        if (isset($this->amountType)) {
            $json['amountType'] = $this->amountType;
        }
        if (0 < count($this->source)) {
            $json['source'] = [];
            foreach ($this->source as $source) {
                $json['source'][] = $source;
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
            $sxe = new \SimpleXMLElement('<SubstanceReferenceInformationTarget xmlns="http://hl7.org/fhir"></SubstanceReferenceInformationTarget>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->target)) {
            $this->target->xmlSerialize(true, $sxe->addChild('target'));
        }
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->interaction)) {
            $this->interaction->xmlSerialize(true, $sxe->addChild('interaction'));
        }
        if (isset($this->organism)) {
            $this->organism->xmlSerialize(true, $sxe->addChild('organism'));
        }
        if (isset($this->organismType)) {
            $this->organismType->xmlSerialize(true, $sxe->addChild('organismType'));
        }
        if (isset($this->amountQuantity)) {
            $this->amountQuantity->xmlSerialize(true, $sxe->addChild('amountQuantity'));
        }
        if (isset($this->amountRange)) {
            $this->amountRange->xmlSerialize(true, $sxe->addChild('amountRange'));
        }
        if (isset($this->amountString)) {
            $this->amountString->xmlSerialize(true, $sxe->addChild('amountString'));
        }
        if (isset($this->amountType)) {
            $this->amountType->xmlSerialize(true, $sxe->addChild('amountType'));
        }
        if (0 < count($this->source)) {
            foreach ($this->source as $source) {
                $source->xmlSerialize(true, $sxe->addChild('source'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
