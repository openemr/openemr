<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRPlanDefinition;

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
 * This resource allows for the definition of various types of plans as a sharable, consumable, and executable artifact. The resource is general enough to support the description of a broad range of clinical artifacts such as clinical decision support rules, order sets and protocols.
 */
class FHIRPlanDefinitionTarget extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The parameter whose value is to be tracked, e.g. body weight, blood pressure, or hemoglobin A1c level.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $measure = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public $detailQuantity = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRRange
     */
    public $detailRange = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $detailCodeableConcept = null;

    /**
     * Indicates the timeframe after the start of the goal in which the goal should be met.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public $due = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'PlanDefinition.Target';

    /**
     * The parameter whose value is to be tracked, e.g. body weight, blood pressure, or hemoglobin A1c level.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getMeasure()
    {
        return $this->measure;
    }

    /**
     * The parameter whose value is to be tracked, e.g. body weight, blood pressure, or hemoglobin A1c level.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $measure
     * @return $this
     */
    public function setMeasure($measure)
    {
        $this->measure = $measure;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public function getDetailQuantity()
    {
        return $this->detailQuantity;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity $detailQuantity
     * @return $this
     */
    public function setDetailQuantity($detailQuantity)
    {
        $this->detailQuantity = $detailQuantity;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRRange
     */
    public function getDetailRange()
    {
        return $this->detailRange;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRRange $detailRange
     * @return $this
     */
    public function setDetailRange($detailRange)
    {
        $this->detailRange = $detailRange;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getDetailCodeableConcept()
    {
        return $this->detailCodeableConcept;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $detailCodeableConcept
     * @return $this
     */
    public function setDetailCodeableConcept($detailCodeableConcept)
    {
        $this->detailCodeableConcept = $detailCodeableConcept;
        return $this;
    }

    /**
     * Indicates the timeframe after the start of the goal in which the goal should be met.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public function getDue()
    {
        return $this->due;
    }

    /**
     * Indicates the timeframe after the start of the goal in which the goal should be met.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity\FHIRDuration $due
     * @return $this
     */
    public function setDue($due)
    {
        $this->due = $due;
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
            if (isset($data['measure'])) {
                $this->setMeasure($data['measure']);
            }
            if (isset($data['detailQuantity'])) {
                $this->setDetailQuantity($data['detailQuantity']);
            }
            if (isset($data['detailRange'])) {
                $this->setDetailRange($data['detailRange']);
            }
            if (isset($data['detailCodeableConcept'])) {
                $this->setDetailCodeableConcept($data['detailCodeableConcept']);
            }
            if (isset($data['due'])) {
                $this->setDue($data['due']);
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
        if (isset($this->measure)) {
            $json['measure'] = $this->measure;
        }
        if (isset($this->detailQuantity)) {
            $json['detailQuantity'] = $this->detailQuantity;
        }
        if (isset($this->detailRange)) {
            $json['detailRange'] = $this->detailRange;
        }
        if (isset($this->detailCodeableConcept)) {
            $json['detailCodeableConcept'] = $this->detailCodeableConcept;
        }
        if (isset($this->due)) {
            $json['due'] = $this->due;
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
            $sxe = new \SimpleXMLElement('<PlanDefinitionTarget xmlns="http://hl7.org/fhir"></PlanDefinitionTarget>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->measure)) {
            $this->measure->xmlSerialize(true, $sxe->addChild('measure'));
        }
        if (isset($this->detailQuantity)) {
            $this->detailQuantity->xmlSerialize(true, $sxe->addChild('detailQuantity'));
        }
        if (isset($this->detailRange)) {
            $this->detailRange->xmlSerialize(true, $sxe->addChild('detailRange'));
        }
        if (isset($this->detailCodeableConcept)) {
            $this->detailCodeableConcept->xmlSerialize(true, $sxe->addChild('detailCodeableConcept'));
        }
        if (isset($this->due)) {
            $this->due->xmlSerialize(true, $sxe->addChild('due'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
