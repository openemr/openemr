<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRSpecimen;

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
 * A sample to be used for analysis.
 */
class FHIRSpecimenCollection extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Person who collected the specimen.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $collector = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $collectedDateTime = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public $collectedPeriod = null;

    /**
     * The span of time over which the collection of a specimen occurred.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public $duration = null;

    /**
     * The quantity of specimen collected; for instance the volume of a blood sample, or the physical measurement of an anatomic pathology sample.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public $quantity = null;

    /**
     * A coded value specifying the technique that is used to perform the procedure.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $method = null;

    /**
     * Anatomical location from which the specimen was collected (if subject is a patient). This is the target site.  This element is not used for environmental specimens.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $bodySite = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $fastingStatusCodeableConcept = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public $fastingStatusDuration = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Specimen.Collection';

    /**
     * Person who collected the specimen.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getCollector()
    {
        return $this->collector;
    }

    /**
     * Person who collected the specimen.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $collector
     * @return $this
     */
    public function setCollector($collector)
    {
        $this->collector = $collector;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getCollectedDateTime()
    {
        return $this->collectedDateTime;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $collectedDateTime
     * @return $this
     */
    public function setCollectedDateTime($collectedDateTime)
    {
        $this->collectedDateTime = $collectedDateTime;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public function getCollectedPeriod()
    {
        return $this->collectedPeriod;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod $collectedPeriod
     * @return $this
     */
    public function setCollectedPeriod($collectedPeriod)
    {
        $this->collectedPeriod = $collectedPeriod;
        return $this;
    }

    /**
     * The span of time over which the collection of a specimen occurred.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * The span of time over which the collection of a specimen occurred.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration $duration
     * @return $this
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
        return $this;
    }

    /**
     * The quantity of specimen collected; for instance the volume of a blood sample, or the physical measurement of an anatomic pathology sample.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * The quantity of specimen collected; for instance the volume of a blood sample, or the physical measurement of an anatomic pathology sample.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity $quantity
     * @return $this
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * A coded value specifying the technique that is used to perform the procedure.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * A coded value specifying the technique that is used to perform the procedure.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Anatomical location from which the specimen was collected (if subject is a patient). This is the target site.  This element is not used for environmental specimens.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getBodySite()
    {
        return $this->bodySite;
    }

    /**
     * Anatomical location from which the specimen was collected (if subject is a patient). This is the target site.  This element is not used for environmental specimens.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $bodySite
     * @return $this
     */
    public function setBodySite($bodySite)
    {
        $this->bodySite = $bodySite;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getFastingStatusCodeableConcept()
    {
        return $this->fastingStatusCodeableConcept;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $fastingStatusCodeableConcept
     * @return $this
     */
    public function setFastingStatusCodeableConcept($fastingStatusCodeableConcept)
    {
        $this->fastingStatusCodeableConcept = $fastingStatusCodeableConcept;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public function getFastingStatusDuration()
    {
        return $this->fastingStatusDuration;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration $fastingStatusDuration
     * @return $this
     */
    public function setFastingStatusDuration($fastingStatusDuration)
    {
        $this->fastingStatusDuration = $fastingStatusDuration;
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
            if (isset($data['collector'])) {
                $this->setCollector($data['collector']);
            }
            if (isset($data['collectedDateTime'])) {
                $this->setCollectedDateTime($data['collectedDateTime']);
            }
            if (isset($data['collectedPeriod'])) {
                $this->setCollectedPeriod($data['collectedPeriod']);
            }
            if (isset($data['duration'])) {
                $this->setDuration($data['duration']);
            }
            if (isset($data['quantity'])) {
                $this->setQuantity($data['quantity']);
            }
            if (isset($data['method'])) {
                $this->setMethod($data['method']);
            }
            if (isset($data['bodySite'])) {
                $this->setBodySite($data['bodySite']);
            }
            if (isset($data['fastingStatusCodeableConcept'])) {
                $this->setFastingStatusCodeableConcept($data['fastingStatusCodeableConcept']);
            }
            if (isset($data['fastingStatusDuration'])) {
                $this->setFastingStatusDuration($data['fastingStatusDuration']);
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
        if (isset($this->collector)) {
            $json['collector'] = $this->collector;
        }
        if (isset($this->collectedDateTime)) {
            $json['collectedDateTime'] = $this->collectedDateTime;
        }
        if (isset($this->collectedPeriod)) {
            $json['collectedPeriod'] = $this->collectedPeriod;
        }
        if (isset($this->duration)) {
            $json['duration'] = $this->duration;
        }
        if (isset($this->quantity)) {
            $json['quantity'] = $this->quantity;
        }
        if (isset($this->method)) {
            $json['method'] = $this->method;
        }
        if (isset($this->bodySite)) {
            $json['bodySite'] = $this->bodySite;
        }
        if (isset($this->fastingStatusCodeableConcept)) {
            $json['fastingStatusCodeableConcept'] = $this->fastingStatusCodeableConcept;
        }
        if (isset($this->fastingStatusDuration)) {
            $json['fastingStatusDuration'] = $this->fastingStatusDuration;
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
            $sxe = new \SimpleXMLElement('<SpecimenCollection xmlns="http://hl7.org/fhir"></SpecimenCollection>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->collector)) {
            $this->collector->xmlSerialize(true, $sxe->addChild('collector'));
        }
        if (isset($this->collectedDateTime)) {
            $this->collectedDateTime->xmlSerialize(true, $sxe->addChild('collectedDateTime'));
        }
        if (isset($this->collectedPeriod)) {
            $this->collectedPeriod->xmlSerialize(true, $sxe->addChild('collectedPeriod'));
        }
        if (isset($this->duration)) {
            $this->duration->xmlSerialize(true, $sxe->addChild('duration'));
        }
        if (isset($this->quantity)) {
            $this->quantity->xmlSerialize(true, $sxe->addChild('quantity'));
        }
        if (isset($this->method)) {
            $this->method->xmlSerialize(true, $sxe->addChild('method'));
        }
        if (isset($this->bodySite)) {
            $this->bodySite->xmlSerialize(true, $sxe->addChild('bodySite'));
        }
        if (isset($this->fastingStatusCodeableConcept)) {
            $this->fastingStatusCodeableConcept->xmlSerialize(true, $sxe->addChild('fastingStatusCodeableConcept'));
        }
        if (isset($this->fastingStatusDuration)) {
            $this->fastingStatusDuration->xmlSerialize(true, $sxe->addChild('fastingStatusDuration'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
