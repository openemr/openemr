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
 * A container for slots of time that may be available for booking appointments.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRSchedule extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * External Ids for this item.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * Whether this schedule record is in active use or should not be used (such as was entered in error).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $active = null;

    /**
     * A broad categorization of the service that is to be performed during this appointment.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $serviceCategory = [];

    /**
     * The specific service that is to be performed during this appointment.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $serviceType = [];

    /**
     * The specialty of a practitioner that would be required to perform the service requested in this appointment.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $specialty = [];

    /**
     * Slots that reference this schedule resource provide the availability details to these referenced resource(s).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $actor = [];

    /**
     * The period of time that the slots that reference this Schedule resource cover (even if none exist). These  cover the amount of time that an organization's planning horizon; the interval for which they are currently accepting appointments. This does not define a "template" for planning outside these dates.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public $planningHorizon = null;

    /**
     * Comments on the availability to describe any extended information. Such as custom constraints on the slots that may be associated.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $comment = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Schedule';

    /**
     * External Ids for this item.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * External Ids for this item.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * Whether this schedule record is in active use or should not be used (such as was entered in error).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Whether this schedule record is in active use or should not be used (such as was entered in error).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $active
     * @return $this
     */
    public function setActive($active)
    {
        $this->active = $active;
        return $this;
    }

    /**
     * A broad categorization of the service that is to be performed during this appointment.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getServiceCategory()
    {
        return $this->serviceCategory;
    }

    /**
     * A broad categorization of the service that is to be performed during this appointment.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $serviceCategory
     * @return $this
     */
    public function addServiceCategory($serviceCategory)
    {
        $this->serviceCategory[] = $serviceCategory;
        return $this;
    }

    /**
     * The specific service that is to be performed during this appointment.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getServiceType()
    {
        return $this->serviceType;
    }

    /**
     * The specific service that is to be performed during this appointment.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $serviceType
     * @return $this
     */
    public function addServiceType($serviceType)
    {
        $this->serviceType[] = $serviceType;
        return $this;
    }

    /**
     * The specialty of a practitioner that would be required to perform the service requested in this appointment.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getSpecialty()
    {
        return $this->specialty;
    }

    /**
     * The specialty of a practitioner that would be required to perform the service requested in this appointment.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $specialty
     * @return $this
     */
    public function addSpecialty($specialty)
    {
        $this->specialty[] = $specialty;
        return $this;
    }

    /**
     * Slots that reference this schedule resource provide the availability details to these referenced resource(s).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getActor()
    {
        return $this->actor;
    }

    /**
     * Slots that reference this schedule resource provide the availability details to these referenced resource(s).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $actor
     * @return $this
     */
    public function addActor($actor)
    {
        $this->actor[] = $actor;
        return $this;
    }

    /**
     * The period of time that the slots that reference this Schedule resource cover (even if none exist). These  cover the amount of time that an organization's planning horizon; the interval for which they are currently accepting appointments. This does not define a "template" for planning outside these dates.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public function getPlanningHorizon()
    {
        return $this->planningHorizon;
    }

    /**
     * The period of time that the slots that reference this Schedule resource cover (even if none exist). These  cover the amount of time that an organization's planning horizon; the interval for which they are currently accepting appointments. This does not define a "template" for planning outside these dates.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod $planningHorizon
     * @return $this
     */
    public function setPlanningHorizon($planningHorizon)
    {
        $this->planningHorizon = $planningHorizon;
        return $this;
    }

    /**
     * Comments on the availability to describe any extended information. Such as custom constraints on the slots that may be associated.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Comments on the availability to describe any extended information. Such as custom constraints on the slots that may be associated.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $comment
     * @return $this
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
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
                if (is_array($data['identifier'])) {
                    foreach ($data['identifier'] as $d) {
                        $this->addIdentifier($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"identifier" must be array of objects or null, ' . gettype($data['identifier']) . ' seen.');
                }
            }
            if (isset($data['active'])) {
                $this->setActive($data['active']);
            }
            if (isset($data['serviceCategory'])) {
                if (is_array($data['serviceCategory'])) {
                    foreach ($data['serviceCategory'] as $d) {
                        $this->addServiceCategory($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"serviceCategory" must be array of objects or null, ' . gettype($data['serviceCategory']) . ' seen.');
                }
            }
            if (isset($data['serviceType'])) {
                if (is_array($data['serviceType'])) {
                    foreach ($data['serviceType'] as $d) {
                        $this->addServiceType($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"serviceType" must be array of objects or null, ' . gettype($data['serviceType']) . ' seen.');
                }
            }
            if (isset($data['specialty'])) {
                if (is_array($data['specialty'])) {
                    foreach ($data['specialty'] as $d) {
                        $this->addSpecialty($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"specialty" must be array of objects or null, ' . gettype($data['specialty']) . ' seen.');
                }
            }
            if (isset($data['actor'])) {
                if (is_array($data['actor'])) {
                    foreach ($data['actor'] as $d) {
                        $this->addActor($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"actor" must be array of objects or null, ' . gettype($data['actor']) . ' seen.');
                }
            }
            if (isset($data['planningHorizon'])) {
                $this->setPlanningHorizon($data['planningHorizon']);
            }
            if (isset($data['comment'])) {
                $this->setComment($data['comment']);
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
        if (0 < count($this->identifier)) {
            $json['identifier'] = [];
            foreach ($this->identifier as $identifier) {
                $json['identifier'][] = $identifier;
            }
        }
        if (isset($this->active)) {
            $json['active'] = $this->active;
        }
        if (0 < count($this->serviceCategory)) {
            $json['serviceCategory'] = [];
            foreach ($this->serviceCategory as $serviceCategory) {
                $json['serviceCategory'][] = $serviceCategory;
            }
        }
        if (0 < count($this->serviceType)) {
            $json['serviceType'] = [];
            foreach ($this->serviceType as $serviceType) {
                $json['serviceType'][] = $serviceType;
            }
        }
        if (0 < count($this->specialty)) {
            $json['specialty'] = [];
            foreach ($this->specialty as $specialty) {
                $json['specialty'][] = $specialty;
            }
        }
        if (0 < count($this->actor)) {
            $json['actor'] = [];
            foreach ($this->actor as $actor) {
                $json['actor'][] = $actor;
            }
        }
        if (isset($this->planningHorizon)) {
            $json['planningHorizon'] = $this->planningHorizon;
        }
        if (isset($this->comment)) {
            $json['comment'] = $this->comment;
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
            $sxe = new \SimpleXMLElement('<Schedule xmlns="http://hl7.org/fhir"></Schedule>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->identifier)) {
            foreach ($this->identifier as $identifier) {
                $identifier->xmlSerialize(true, $sxe->addChild('identifier'));
            }
        }
        if (isset($this->active)) {
            $this->active->xmlSerialize(true, $sxe->addChild('active'));
        }
        if (0 < count($this->serviceCategory)) {
            foreach ($this->serviceCategory as $serviceCategory) {
                $serviceCategory->xmlSerialize(true, $sxe->addChild('serviceCategory'));
            }
        }
        if (0 < count($this->serviceType)) {
            foreach ($this->serviceType as $serviceType) {
                $serviceType->xmlSerialize(true, $sxe->addChild('serviceType'));
            }
        }
        if (0 < count($this->specialty)) {
            foreach ($this->specialty as $specialty) {
                $specialty->xmlSerialize(true, $sxe->addChild('specialty'));
            }
        }
        if (0 < count($this->actor)) {
            foreach ($this->actor as $actor) {
                $actor->xmlSerialize(true, $sxe->addChild('actor'));
            }
        }
        if (isset($this->planningHorizon)) {
            $this->planningHorizon->xmlSerialize(true, $sxe->addChild('planningHorizon'));
        }
        if (isset($this->comment)) {
            $this->comment->xmlSerialize(true, $sxe->addChild('comment'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
