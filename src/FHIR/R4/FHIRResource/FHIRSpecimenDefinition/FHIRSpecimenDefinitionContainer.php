<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRSpecimenDefinition;

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
 * A kind of specimen with associated set of requirements.
 */
class FHIRSpecimenDefinitionContainer extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The type of material of the container.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $material = null;

    /**
     * The type of container used to contain this kind of specimen.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $type = null;

    /**
     * Color of container cap.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $cap = null;

    /**
     * The textual description of the kind of container.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $description = null;

    /**
     * The capacity (volume or other measure) of this kind of container.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public $capacity = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public $minimumVolumeQuantity = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $minimumVolumeString = null;

    /**
     * Substance introduced in the kind of container to preserve, maintain or enhance the specimen. Examples: Formalin, Citrate, EDTA.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRSpecimenDefinition\FHIRSpecimenDefinitionAdditive[]
     */
    public $additive = [];

    /**
     * Special processing that should be applied to the container for this kind of specimen.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $preparation = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'SpecimenDefinition.Container';

    /**
     * The type of material of the container.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getMaterial()
    {
        return $this->material;
    }

    /**
     * The type of material of the container.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $material
     * @return $this
     */
    public function setMaterial($material)
    {
        $this->material = $material;
        return $this;
    }

    /**
     * The type of container used to contain this kind of specimen.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * The type of container used to contain this kind of specimen.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Color of container cap.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getCap()
    {
        return $this->cap;
    }

    /**
     * Color of container cap.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $cap
     * @return $this
     */
    public function setCap($cap)
    {
        $this->cap = $cap;
        return $this;
    }

    /**
     * The textual description of the kind of container.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * The textual description of the kind of container.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * The capacity (volume or other measure) of this kind of container.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public function getCapacity()
    {
        return $this->capacity;
    }

    /**
     * The capacity (volume or other measure) of this kind of container.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity $capacity
     * @return $this
     */
    public function setCapacity($capacity)
    {
        $this->capacity = $capacity;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public function getMinimumVolumeQuantity()
    {
        return $this->minimumVolumeQuantity;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity $minimumVolumeQuantity
     * @return $this
     */
    public function setMinimumVolumeQuantity($minimumVolumeQuantity)
    {
        $this->minimumVolumeQuantity = $minimumVolumeQuantity;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getMinimumVolumeString()
    {
        return $this->minimumVolumeString;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $minimumVolumeString
     * @return $this
     */
    public function setMinimumVolumeString($minimumVolumeString)
    {
        $this->minimumVolumeString = $minimumVolumeString;
        return $this;
    }

    /**
     * Substance introduced in the kind of container to preserve, maintain or enhance the specimen. Examples: Formalin, Citrate, EDTA.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRSpecimenDefinition\FHIRSpecimenDefinitionAdditive[]
     */
    public function getAdditive()
    {
        return $this->additive;
    }

    /**
     * Substance introduced in the kind of container to preserve, maintain or enhance the specimen. Examples: Formalin, Citrate, EDTA.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRSpecimenDefinition\FHIRSpecimenDefinitionAdditive $additive
     * @return $this
     */
    public function addAdditive($additive)
    {
        $this->additive[] = $additive;
        return $this;
    }

    /**
     * Special processing that should be applied to the container for this kind of specimen.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getPreparation()
    {
        return $this->preparation;
    }

    /**
     * Special processing that should be applied to the container for this kind of specimen.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $preparation
     * @return $this
     */
    public function setPreparation($preparation)
    {
        $this->preparation = $preparation;
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
            if (isset($data['material'])) {
                $this->setMaterial($data['material']);
            }
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['cap'])) {
                $this->setCap($data['cap']);
            }
            if (isset($data['description'])) {
                $this->setDescription($data['description']);
            }
            if (isset($data['capacity'])) {
                $this->setCapacity($data['capacity']);
            }
            if (isset($data['minimumVolumeQuantity'])) {
                $this->setMinimumVolumeQuantity($data['minimumVolumeQuantity']);
            }
            if (isset($data['minimumVolumeString'])) {
                $this->setMinimumVolumeString($data['minimumVolumeString']);
            }
            if (isset($data['additive'])) {
                if (is_array($data['additive'])) {
                    foreach ($data['additive'] as $d) {
                        $this->addAdditive($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"additive" must be array of objects or null, ' . gettype($data['additive']) . ' seen.');
                }
            }
            if (isset($data['preparation'])) {
                $this->setPreparation($data['preparation']);
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
        if (isset($this->material)) {
            $json['material'] = $this->material;
        }
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->cap)) {
            $json['cap'] = $this->cap;
        }
        if (isset($this->description)) {
            $json['description'] = $this->description;
        }
        if (isset($this->capacity)) {
            $json['capacity'] = $this->capacity;
        }
        if (isset($this->minimumVolumeQuantity)) {
            $json['minimumVolumeQuantity'] = $this->minimumVolumeQuantity;
        }
        if (isset($this->minimumVolumeString)) {
            $json['minimumVolumeString'] = $this->minimumVolumeString;
        }
        if (0 < count($this->additive)) {
            $json['additive'] = [];
            foreach ($this->additive as $additive) {
                $json['additive'][] = $additive;
            }
        }
        if (isset($this->preparation)) {
            $json['preparation'] = $this->preparation;
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
            $sxe = new \SimpleXMLElement('<SpecimenDefinitionContainer xmlns="http://hl7.org/fhir"></SpecimenDefinitionContainer>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->material)) {
            $this->material->xmlSerialize(true, $sxe->addChild('material'));
        }
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->cap)) {
            $this->cap->xmlSerialize(true, $sxe->addChild('cap'));
        }
        if (isset($this->description)) {
            $this->description->xmlSerialize(true, $sxe->addChild('description'));
        }
        if (isset($this->capacity)) {
            $this->capacity->xmlSerialize(true, $sxe->addChild('capacity'));
        }
        if (isset($this->minimumVolumeQuantity)) {
            $this->minimumVolumeQuantity->xmlSerialize(true, $sxe->addChild('minimumVolumeQuantity'));
        }
        if (isset($this->minimumVolumeString)) {
            $this->minimumVolumeString->xmlSerialize(true, $sxe->addChild('minimumVolumeString'));
        }
        if (0 < count($this->additive)) {
            foreach ($this->additive as $additive) {
                $additive->xmlSerialize(true, $sxe->addChild('additive'));
            }
        }
        if (isset($this->preparation)) {
            $this->preparation->xmlSerialize(true, $sxe->addChild('preparation'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
