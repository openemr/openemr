<?php

namespace OpenEMR\FHIR\R4\FHIRDomainResource;

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

use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;

/**
 * A kind of specimen with associated set of requirements.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRSpecimenDefinition extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * A business identifier associated with the kind of specimen.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier
     */
    public $identifier = null;

    /**
     * The kind of material to be collected.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $typeCollected = null;

    /**
     * Preparation of the patient for specimen collection.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $patientPreparation = [];

    /**
     * Time aspect of specimen collection (duration or offset).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $timeAspect = null;

    /**
     * The action to be performed for collecting the specimen.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $collection = [];

    /**
     * Specimen conditioned in a container as expected by the testing laboratory.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRSpecimenDefinition\FHIRSpecimenDefinitionTypeTested[]
     */
    public $typeTested = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'SpecimenDefinition';

    /**
     * A business identifier associated with the kind of specimen.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * A business identifier associated with the kind of specimen.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * The kind of material to be collected.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getTypeCollected()
    {
        return $this->typeCollected;
    }

    /**
     * The kind of material to be collected.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $typeCollected
     * @return $this
     */
    public function setTypeCollected($typeCollected)
    {
        $this->typeCollected = $typeCollected;
        return $this;
    }

    /**
     * Preparation of the patient for specimen collection.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getPatientPreparation()
    {
        return $this->patientPreparation;
    }

    /**
     * Preparation of the patient for specimen collection.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $patientPreparation
     * @return $this
     */
    public function addPatientPreparation($patientPreparation)
    {
        $this->patientPreparation[] = $patientPreparation;
        return $this;
    }

    /**
     * Time aspect of specimen collection (duration or offset).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getTimeAspect()
    {
        return $this->timeAspect;
    }

    /**
     * Time aspect of specimen collection (duration or offset).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $timeAspect
     * @return $this
     */
    public function setTimeAspect($timeAspect)
    {
        $this->timeAspect = $timeAspect;
        return $this;
    }

    /**
     * The action to be performed for collecting the specimen.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * The action to be performed for collecting the specimen.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $collection
     * @return $this
     */
    public function addCollection($collection)
    {
        $this->collection[] = $collection;
        return $this;
    }

    /**
     * Specimen conditioned in a container as expected by the testing laboratory.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRSpecimenDefinition\FHIRSpecimenDefinitionTypeTested[]
     */
    public function getTypeTested()
    {
        return $this->typeTested;
    }

    /**
     * Specimen conditioned in a container as expected by the testing laboratory.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRSpecimenDefinition\FHIRSpecimenDefinitionTypeTested $typeTested
     * @return $this
     */
    public function addTypeTested($typeTested)
    {
        $this->typeTested[] = $typeTested;
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
            if (isset($data['identifier'])) {
                $this->setIdentifier($data['identifier']);
            }
            if (isset($data['typeCollected'])) {
                $this->setTypeCollected($data['typeCollected']);
            }
            if (isset($data['patientPreparation'])) {
                if (is_array($data['patientPreparation'])) {
                    foreach ($data['patientPreparation'] as $d) {
                        $this->addPatientPreparation($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"patientPreparation" must be array of objects or null, ' . gettype($data['patientPreparation']) . ' seen.');
                }
            }
            if (isset($data['timeAspect'])) {
                $this->setTimeAspect($data['timeAspect']);
            }
            if (isset($data['collection'])) {
                if (is_array($data['collection'])) {
                    foreach ($data['collection'] as $d) {
                        $this->addCollection($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"collection" must be array of objects or null, ' . gettype($data['collection']) . ' seen.');
                }
            }
            if (isset($data['typeTested'])) {
                if (is_array($data['typeTested'])) {
                    foreach ($data['typeTested'] as $d) {
                        $this->addTypeTested($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"typeTested" must be array of objects or null, ' . gettype($data['typeTested']) . ' seen.');
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
    public function jsonSerialize()
    {
        $json = parent::jsonSerialize();
        $json['resourceType'] = $this->_fhirElementName;
        if (isset($this->identifier)) {
            $json['identifier'] = $this->identifier;
        }
        if (isset($this->typeCollected)) {
            $json['typeCollected'] = $this->typeCollected;
        }
        if (0 < count($this->patientPreparation)) {
            $json['patientPreparation'] = [];
            foreach ($this->patientPreparation as $patientPreparation) {
                $json['patientPreparation'][] = $patientPreparation;
            }
        }
        if (isset($this->timeAspect)) {
            $json['timeAspect'] = $this->timeAspect;
        }
        if (0 < count($this->collection)) {
            $json['collection'] = [];
            foreach ($this->collection as $collection) {
                $json['collection'][] = $collection;
            }
        }
        if (0 < count($this->typeTested)) {
            $json['typeTested'] = [];
            foreach ($this->typeTested as $typeTested) {
                $json['typeTested'][] = $typeTested;
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
            $sxe = new \SimpleXMLElement('<SpecimenDefinition xmlns="http://hl7.org/fhir"></SpecimenDefinition>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->identifier)) {
            $this->identifier->xmlSerialize(true, $sxe->addChild('identifier'));
        }
        if (isset($this->typeCollected)) {
            $this->typeCollected->xmlSerialize(true, $sxe->addChild('typeCollected'));
        }
        if (0 < count($this->patientPreparation)) {
            foreach ($this->patientPreparation as $patientPreparation) {
                $patientPreparation->xmlSerialize(true, $sxe->addChild('patientPreparation'));
            }
        }
        if (isset($this->timeAspect)) {
            $this->timeAspect->xmlSerialize(true, $sxe->addChild('timeAspect'));
        }
        if (0 < count($this->collection)) {
            foreach ($this->collection as $collection) {
                $collection->xmlSerialize(true, $sxe->addChild('collection'));
            }
        }
        if (0 < count($this->typeTested)) {
            foreach ($this->typeTested as $typeTested) {
                $typeTested->xmlSerialize(true, $sxe->addChild('typeTested'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
