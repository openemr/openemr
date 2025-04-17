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
 * Todo.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRSubstancePolymer extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * Todo.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $class = null;

    /**
     * Todo.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $geometry = null;

    /**
     * Todo.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $copolymerConnectivity = [];

    /**
     * Todo.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public $modification = [];

    /**
     * Todo.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstancePolymer\FHIRSubstancePolymerMonomerSet[]
     */
    public $monomerSet = [];

    /**
     * Todo.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstancePolymer\FHIRSubstancePolymerRepeat[]
     */
    public $repeat = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'SubstancePolymer';

    /**
     * Todo.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Todo.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $class
     * @return $this
     */
    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }

    /**
     * Todo.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getGeometry()
    {
        return $this->geometry;
    }

    /**
     * Todo.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $geometry
     * @return $this
     */
    public function setGeometry($geometry)
    {
        $this->geometry = $geometry;
        return $this;
    }

    /**
     * Todo.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getCopolymerConnectivity()
    {
        return $this->copolymerConnectivity;
    }

    /**
     * Todo.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $copolymerConnectivity
     * @return $this
     */
    public function addCopolymerConnectivity($copolymerConnectivity)
    {
        $this->copolymerConnectivity[] = $copolymerConnectivity;
        return $this;
    }

    /**
     * Todo.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public function getModification()
    {
        return $this->modification;
    }

    /**
     * Todo.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $modification
     * @return $this
     */
    public function addModification($modification)
    {
        $this->modification[] = $modification;
        return $this;
    }

    /**
     * Todo.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstancePolymer\FHIRSubstancePolymerMonomerSet[]
     */
    public function getMonomerSet()
    {
        return $this->monomerSet;
    }

    /**
     * Todo.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstancePolymer\FHIRSubstancePolymerMonomerSet $monomerSet
     * @return $this
     */
    public function addMonomerSet($monomerSet)
    {
        $this->monomerSet[] = $monomerSet;
        return $this;
    }

    /**
     * Todo.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstancePolymer\FHIRSubstancePolymerRepeat[]
     */
    public function getRepeat()
    {
        return $this->repeat;
    }

    /**
     * Todo.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRSubstancePolymer\FHIRSubstancePolymerRepeat $repeat
     * @return $this
     */
    public function addRepeat($repeat)
    {
        $this->repeat[] = $repeat;
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
            if (isset($data['class'])) {
                $this->setClass($data['class']);
            }
            if (isset($data['geometry'])) {
                $this->setGeometry($data['geometry']);
            }
            if (isset($data['copolymerConnectivity'])) {
                if (is_array($data['copolymerConnectivity'])) {
                    foreach ($data['copolymerConnectivity'] as $d) {
                        $this->addCopolymerConnectivity($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"copolymerConnectivity" must be array of objects or null, ' . gettype($data['copolymerConnectivity']) . ' seen.');
                }
            }
            if (isset($data['modification'])) {
                if (is_array($data['modification'])) {
                    foreach ($data['modification'] as $d) {
                        $this->addModification($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"modification" must be array of objects or null, ' . gettype($data['modification']) . ' seen.');
                }
            }
            if (isset($data['monomerSet'])) {
                if (is_array($data['monomerSet'])) {
                    foreach ($data['monomerSet'] as $d) {
                        $this->addMonomerSet($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"monomerSet" must be array of objects or null, ' . gettype($data['monomerSet']) . ' seen.');
                }
            }
            if (isset($data['repeat'])) {
                if (is_array($data['repeat'])) {
                    foreach ($data['repeat'] as $d) {
                        $this->addRepeat($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"repeat" must be array of objects or null, ' . gettype($data['repeat']) . ' seen.');
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
        $json['resourceType'] = $this->_fhirElementName;
        if (isset($this->class)) {
            $json['class'] = $this->class;
        }
        if (isset($this->geometry)) {
            $json['geometry'] = $this->geometry;
        }
        if (0 < count($this->copolymerConnectivity)) {
            $json['copolymerConnectivity'] = [];
            foreach ($this->copolymerConnectivity as $copolymerConnectivity) {
                $json['copolymerConnectivity'][] = $copolymerConnectivity;
            }
        }
        if (0 < count($this->modification)) {
            $json['modification'] = [];
            foreach ($this->modification as $modification) {
                $json['modification'][] = $modification;
            }
        }
        if (0 < count($this->monomerSet)) {
            $json['monomerSet'] = [];
            foreach ($this->monomerSet as $monomerSet) {
                $json['monomerSet'][] = $monomerSet;
            }
        }
        if (0 < count($this->repeat)) {
            $json['repeat'] = [];
            foreach ($this->repeat as $repeat) {
                $json['repeat'][] = $repeat;
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
            $sxe = new \SimpleXMLElement('<SubstancePolymer xmlns="http://hl7.org/fhir"></SubstancePolymer>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->class)) {
            $this->class->xmlSerialize(true, $sxe->addChild('class'));
        }
        if (isset($this->geometry)) {
            $this->geometry->xmlSerialize(true, $sxe->addChild('geometry'));
        }
        if (0 < count($this->copolymerConnectivity)) {
            foreach ($this->copolymerConnectivity as $copolymerConnectivity) {
                $copolymerConnectivity->xmlSerialize(true, $sxe->addChild('copolymerConnectivity'));
            }
        }
        if (0 < count($this->modification)) {
            foreach ($this->modification as $modification) {
                $modification->xmlSerialize(true, $sxe->addChild('modification'));
            }
        }
        if (0 < count($this->monomerSet)) {
            foreach ($this->monomerSet as $monomerSet) {
                $monomerSet->xmlSerialize(true, $sxe->addChild('monomerSet'));
            }
        }
        if (0 < count($this->repeat)) {
            foreach ($this->repeat as $repeat) {
                $repeat->xmlSerialize(true, $sxe->addChild('repeat'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
