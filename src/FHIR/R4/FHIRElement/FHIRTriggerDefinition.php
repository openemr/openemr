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
 * A description of a triggering event. Triggering events can be named events, data events, or periodic, as determined by the type element.
 * If the element is present, it must have a value for at least one of the defined elements, an @id referenced from the Narrative, or extensions
 */
class FHIRTriggerDefinition extends FHIRElement implements \JsonSerializable
{
    /**
     * The type of triggering event.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRTriggerType
     */
    public $type = null;

    /**
     * A formal name for the event. This may be an absolute URI that identifies the event formally (e.g. from a trigger registry), or a simple relative URI that identifies the event in a local context.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $name = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRTiming
     */
    public $timingTiming = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $timingReference = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDate
     */
    public $timingDate = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $timingDateTime = null;

    /**
     * The triggering data of the event (if this is a data trigger). If more than one data is requirement is specified, then all the data requirements must be true.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDataRequirement[]
     */
    public $data = [];

    /**
     * A boolean-valued expression that is evaluated in the context of the container of the trigger definition and returns whether or not the trigger fires.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRExpression
     */
    public $condition = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'TriggerDefinition';

    /**
     * The type of triggering event.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRTriggerType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * The type of triggering event.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRTriggerType $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * A formal name for the event. This may be an absolute URI that identifies the event formally (e.g. from a trigger registry), or a simple relative URI that identifies the event in a local context.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * A formal name for the event. This may be an absolute URI that identifies the event formally (e.g. from a trigger registry), or a simple relative URI that identifies the event in a local context.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRTiming
     */
    public function getTimingTiming()
    {
        return $this->timingTiming;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRTiming $timingTiming
     * @return $this
     */
    public function setTimingTiming($timingTiming)
    {
        $this->timingTiming = $timingTiming;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getTimingReference()
    {
        return $this->timingReference;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $timingReference
     * @return $this
     */
    public function setTimingReference($timingReference)
    {
        $this->timingReference = $timingReference;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDate
     */
    public function getTimingDate()
    {
        return $this->timingDate;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDate $timingDate
     * @return $this
     */
    public function setTimingDate($timingDate)
    {
        $this->timingDate = $timingDate;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getTimingDateTime()
    {
        return $this->timingDateTime;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $timingDateTime
     * @return $this
     */
    public function setTimingDateTime($timingDateTime)
    {
        $this->timingDateTime = $timingDateTime;
        return $this;
    }

    /**
     * The triggering data of the event (if this is a data trigger). If more than one data is requirement is specified, then all the data requirements must be true.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDataRequirement[]
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * The triggering data of the event (if this is a data trigger). If more than one data is requirement is specified, then all the data requirements must be true.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDataRequirement $data
     * @return $this
     */
    public function addData($data)
    {
        $this->data[] = $data;
        return $this;
    }

    /**
     * A boolean-valued expression that is evaluated in the context of the container of the trigger definition and returns whether or not the trigger fires.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRExpression
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * A boolean-valued expression that is evaluated in the context of the container of the trigger definition and returns whether or not the trigger fires.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRExpression $condition
     * @return $this
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;
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
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['name'])) {
                $this->setName($data['name']);
            }
            if (isset($data['timingTiming'])) {
                $this->setTimingTiming($data['timingTiming']);
            }
            if (isset($data['timingReference'])) {
                $this->setTimingReference($data['timingReference']);
            }
            if (isset($data['timingDate'])) {
                $this->setTimingDate($data['timingDate']);
            }
            if (isset($data['timingDateTime'])) {
                $this->setTimingDateTime($data['timingDateTime']);
            }
            if (isset($data['data'])) {
                if (is_array($data['data'])) {
                    foreach ($data['data'] as $d) {
                        $this->addData($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"data" must be array of objects or null, ' . gettype($data['data']) . ' seen.');
                }
            }
            if (isset($data['condition'])) {
                $this->setCondition($data['condition']);
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
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->name)) {
            $json['name'] = $this->name;
        }
        if (isset($this->timingTiming)) {
            $json['timingTiming'] = $this->timingTiming;
        }
        if (isset($this->timingReference)) {
            $json['timingReference'] = $this->timingReference;
        }
        if (isset($this->timingDate)) {
            $json['timingDate'] = $this->timingDate;
        }
        if (isset($this->timingDateTime)) {
            $json['timingDateTime'] = $this->timingDateTime;
        }
        if (0 < count($this->data)) {
            $json['data'] = [];
            foreach ($this->data as $data) {
                $json['data'][] = $data;
            }
        }
        if (isset($this->condition)) {
            $json['condition'] = $this->condition;
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
            $sxe = new \SimpleXMLElement('<TriggerDefinition xmlns="http://hl7.org/fhir"></TriggerDefinition>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->name)) {
            $this->name->xmlSerialize(true, $sxe->addChild('name'));
        }
        if (isset($this->timingTiming)) {
            $this->timingTiming->xmlSerialize(true, $sxe->addChild('timingTiming'));
        }
        if (isset($this->timingReference)) {
            $this->timingReference->xmlSerialize(true, $sxe->addChild('timingReference'));
        }
        if (isset($this->timingDate)) {
            $this->timingDate->xmlSerialize(true, $sxe->addChild('timingDate'));
        }
        if (isset($this->timingDateTime)) {
            $this->timingDateTime->xmlSerialize(true, $sxe->addChild('timingDateTime'));
        }
        if (0 < count($this->data)) {
            foreach ($this->data as $data) {
                $data->xmlSerialize(true, $sxe->addChild('data'));
            }
        }
        if (isset($this->condition)) {
            $this->condition->xmlSerialize(true, $sxe->addChild('condition'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
