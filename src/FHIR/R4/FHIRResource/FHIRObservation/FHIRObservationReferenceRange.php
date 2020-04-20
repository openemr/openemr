<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRObservation;

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
 * Measurements and simple assertions made about a patient, device or other subject.
 */
class FHIRObservationReferenceRange extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The value of the low bound of the reference range.  The low bound of the reference range endpoint is inclusive of the value (e.g.  reference range is >=5 - <=9). If the low bound is omitted,  it is assumed to be meaningless (e.g. reference range is <=2.3).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public $low = null;

    /**
     * The value of the high bound of the reference range.  The high bound of the reference range endpoint is inclusive of the value (e.g.  reference range is >=5 - <=9). If the high bound is omitted,  it is assumed to be meaningless (e.g. reference range is >= 2.3).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public $high = null;

    /**
     * Codes to indicate the what part of the targeted reference population it applies to. For example, the normal or therapeutic range.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $type = null;

    /**
     * Codes to indicate the target population this reference range applies to.  For example, a reference range may be based on the normal population or a particular sex or race.  Multiple `appliesTo`  are interpreted as an "AND" of the target populations.  For example, to represent a target population of African American females, both a code of female and a code for African American would be used.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $appliesTo = [];

    /**
     * The age at which this reference range is applicable. This is a neonatal age (e.g. number of weeks at term) if the meaning says so.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRRange
     */
    public $age = null;

    /**
     * Text based reference range in an observation which may be used when a quantitative range is not appropriate for an observation.  An example would be a reference value of "Negative" or a list or table of "normals".
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $text = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Observation.ReferenceRange';

    /**
     * The value of the low bound of the reference range.  The low bound of the reference range endpoint is inclusive of the value (e.g.  reference range is >=5 - <=9). If the low bound is omitted,  it is assumed to be meaningless (e.g. reference range is <=2.3).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public function getLow()
    {
        return $this->low;
    }

    /**
     * The value of the low bound of the reference range.  The low bound of the reference range endpoint is inclusive of the value (e.g.  reference range is >=5 - <=9). If the low bound is omitted,  it is assumed to be meaningless (e.g. reference range is <=2.3).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity $low
     * @return $this
     */
    public function setLow($low)
    {
        $this->low = $low;
        return $this;
    }

    /**
     * The value of the high bound of the reference range.  The high bound of the reference range endpoint is inclusive of the value (e.g.  reference range is >=5 - <=9). If the high bound is omitted,  it is assumed to be meaningless (e.g. reference range is >= 2.3).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity
     */
    public function getHigh()
    {
        return $this->high;
    }

    /**
     * The value of the high bound of the reference range.  The high bound of the reference range endpoint is inclusive of the value (e.g.  reference range is >=5 - <=9). If the high bound is omitted,  it is assumed to be meaningless (e.g. reference range is >= 2.3).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity $high
     * @return $this
     */
    public function setHigh($high)
    {
        $this->high = $high;
        return $this;
    }

    /**
     * Codes to indicate the what part of the targeted reference population it applies to. For example, the normal or therapeutic range.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Codes to indicate the what part of the targeted reference population it applies to. For example, the normal or therapeutic range.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Codes to indicate the target population this reference range applies to.  For example, a reference range may be based on the normal population or a particular sex or race.  Multiple `appliesTo`  are interpreted as an "AND" of the target populations.  For example, to represent a target population of African American females, both a code of female and a code for African American would be used.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getAppliesTo()
    {
        return $this->appliesTo;
    }

    /**
     * Codes to indicate the target population this reference range applies to.  For example, a reference range may be based on the normal population or a particular sex or race.  Multiple `appliesTo`  are interpreted as an "AND" of the target populations.  For example, to represent a target population of African American females, both a code of female and a code for African American would be used.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $appliesTo
     * @return $this
     */
    public function addAppliesTo($appliesTo)
    {
        $this->appliesTo[] = $appliesTo;
        return $this;
    }

    /**
     * The age at which this reference range is applicable. This is a neonatal age (e.g. number of weeks at term) if the meaning says so.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRRange
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * The age at which this reference range is applicable. This is a neonatal age (e.g. number of weeks at term) if the meaning says so.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRRange $age
     * @return $this
     */
    public function setAge($age)
    {
        $this->age = $age;
        return $this;
    }

    /**
     * Text based reference range in an observation which may be used when a quantitative range is not appropriate for an observation.  An example would be a reference value of "Negative" or a list or table of "normals".
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Text based reference range in an observation which may be used when a quantitative range is not appropriate for an observation.  An example would be a reference value of "Negative" or a list or table of "normals".
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $text
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;
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
            if (isset($data['low'])) {
                $this->setLow($data['low']);
            }
            if (isset($data['high'])) {
                $this->setHigh($data['high']);
            }
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['appliesTo'])) {
                if (is_array($data['appliesTo'])) {
                    foreach ($data['appliesTo'] as $d) {
                        $this->addAppliesTo($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"appliesTo" must be array of objects or null, ' . gettype($data['appliesTo']) . ' seen.');
                }
            }
            if (isset($data['age'])) {
                $this->setAge($data['age']);
            }
            if (isset($data['text'])) {
                $this->setText($data['text']);
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
        if (isset($this->low)) {
            $json['low'] = $this->low;
        }
        if (isset($this->high)) {
            $json['high'] = $this->high;
        }
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (0 < count($this->appliesTo)) {
            $json['appliesTo'] = [];
            foreach ($this->appliesTo as $appliesTo) {
                $json['appliesTo'][] = $appliesTo;
            }
        }
        if (isset($this->age)) {
            $json['age'] = $this->age;
        }
        if (isset($this->text)) {
            $json['text'] = $this->text;
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
            $sxe = new \SimpleXMLElement('<ObservationReferenceRange xmlns="http://hl7.org/fhir"></ObservationReferenceRange>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->low)) {
            $this->low->xmlSerialize(true, $sxe->addChild('low'));
        }
        if (isset($this->high)) {
            $this->high->xmlSerialize(true, $sxe->addChild('high'));
        }
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (0 < count($this->appliesTo)) {
            foreach ($this->appliesTo as $appliesTo) {
                $appliesTo->xmlSerialize(true, $sxe->addChild('appliesTo'));
            }
        }
        if (isset($this->age)) {
            $this->age->xmlSerialize(true, $sxe->addChild('age'));
        }
        if (isset($this->text)) {
            $this->text->xmlSerialize(true, $sxe->addChild('text'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
