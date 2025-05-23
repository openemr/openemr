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
 * A person who is directly or indirectly involved in the provisioning of healthcare.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRPractitioner extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * An identifier that applies to this person in this role.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * Whether this practitioner's record is in active use.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $active = null;

    /**
     * The name(s) associated with the practitioner.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRHumanName[]
     */
    public $name = [];

    /**
     * A contact detail for the practitioner, e.g. a telephone number or an email address.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRContactPoint[]
     */
    public $telecom = [];

    /**
     * Address(es) of the practitioner that are not role specific (typically home address).
Work addresses are not typically entered in this property as they are usually role dependent.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRAddress[]
     */
    public $address = [];

    /**
     * Administrative Gender - the gender that the person is considered to have for administration and record keeping purposes.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRAdministrativeGender
     */
    public $gender = null;

    /**
     * The date of birth for the practitioner.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDate
     */
    public $birthDate = null;

    /**
     * Image of the person.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRAttachment[]
     */
    public $photo = [];

    /**
     * The official certifications, training, and licenses that authorize or otherwise pertain to the provision of care by the practitioner.  For example, a medical license issued by a medical board authorizing the practitioner to practice medicine within a certian locality.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRPractitioner\FHIRPractitionerQualification[]
     */
    public $qualification = [];

    /**
     * A language the practitioner can use in patient communication.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $communication = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'Practitioner';

    /**
     * An identifier that applies to this person in this role.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * An identifier that applies to this person in this role.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * Whether this practitioner's record is in active use.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Whether this practitioner's record is in active use.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $active
     * @return $this
     */
    public function setActive($active)
    {
        $this->active = $active;
        return $this;
    }

    /**
     * The name(s) associated with the practitioner.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRHumanName[]
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * The name(s) associated with the practitioner.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRHumanName $name
     * @return $this
     */
    public function addName($name)
    {
        $this->name[] = $name;
        return $this;
    }

    /**
     * A contact detail for the practitioner, e.g. a telephone number or an email address.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRContactPoint[]
     */
    public function getTelecom()
    {
        return $this->telecom;
    }

    /**
     * A contact detail for the practitioner, e.g. a telephone number or an email address.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRContactPoint $telecom
     * @return $this
     */
    public function addTelecom($telecom)
    {
        $this->telecom[] = $telecom;
        return $this;
    }

    /**
     * Address(es) of the practitioner that are not role specific (typically home address).
Work addresses are not typically entered in this property as they are usually role dependent.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRAddress[]
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Address(es) of the practitioner that are not role specific (typically home address).
Work addresses are not typically entered in this property as they are usually role dependent.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRAddress $address
     * @return $this
     */
    public function addAddress($address)
    {
        $this->address[] = $address;
        return $this;
    }

    /**
     * Administrative Gender - the gender that the person is considered to have for administration and record keeping purposes.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRAdministrativeGender
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Administrative Gender - the gender that the person is considered to have for administration and record keeping purposes.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRAdministrativeGender $gender
     * @return $this
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
        return $this;
    }

    /**
     * The date of birth for the practitioner.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDate
     */
    public function getBirthDate()
    {
        return $this->birthDate;
    }

    /**
     * The date of birth for the practitioner.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDate $birthDate
     * @return $this
     */
    public function setBirthDate($birthDate)
    {
        $this->birthDate = $birthDate;
        return $this;
    }

    /**
     * Image of the person.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRAttachment[]
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * Image of the person.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRAttachment $photo
     * @return $this
     */
    public function addPhoto($photo)
    {
        $this->photo[] = $photo;
        return $this;
    }

    /**
     * The official certifications, training, and licenses that authorize or otherwise pertain to the provision of care by the practitioner.  For example, a medical license issued by a medical board authorizing the practitioner to practice medicine within a certian locality.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRPractitioner\FHIRPractitionerQualification[]
     */
    public function getQualification()
    {
        return $this->qualification;
    }

    /**
     * The official certifications, training, and licenses that authorize or otherwise pertain to the provision of care by the practitioner.  For example, a medical license issued by a medical board authorizing the practitioner to practice medicine within a certian locality.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRPractitioner\FHIRPractitionerQualification $qualification
     * @return $this
     */
    public function addQualification($qualification)
    {
        $this->qualification[] = $qualification;
        return $this;
    }

    /**
     * A language the practitioner can use in patient communication.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getCommunication()
    {
        return $this->communication;
    }

    /**
     * A language the practitioner can use in patient communication.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $communication
     * @return $this
     */
    public function addCommunication($communication)
    {
        $this->communication[] = $communication;
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
            if (isset($data['name'])) {
                if (is_array($data['name'])) {
                    foreach ($data['name'] as $d) {
                        $this->addName($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"name" must be array of objects or null, ' . gettype($data['name']) . ' seen.');
                }
            }
            if (isset($data['telecom'])) {
                if (is_array($data['telecom'])) {
                    foreach ($data['telecom'] as $d) {
                        $this->addTelecom($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"telecom" must be array of objects or null, ' . gettype($data['telecom']) . ' seen.');
                }
            }
            if (isset($data['address'])) {
                if (is_array($data['address'])) {
                    foreach ($data['address'] as $d) {
                        $this->addAddress($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"address" must be array of objects or null, ' . gettype($data['address']) . ' seen.');
                }
            }
            if (isset($data['gender'])) {
                $this->setGender($data['gender']);
            }
            if (isset($data['birthDate'])) {
                $this->setBirthDate($data['birthDate']);
            }
            if (isset($data['photo'])) {
                if (is_array($data['photo'])) {
                    foreach ($data['photo'] as $d) {
                        $this->addPhoto($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"photo" must be array of objects or null, ' . gettype($data['photo']) . ' seen.');
                }
            }
            if (isset($data['qualification'])) {
                if (is_array($data['qualification'])) {
                    foreach ($data['qualification'] as $d) {
                        $this->addQualification($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"qualification" must be array of objects or null, ' . gettype($data['qualification']) . ' seen.');
                }
            }
            if (isset($data['communication'])) {
                if (is_array($data['communication'])) {
                    foreach ($data['communication'] as $d) {
                        $this->addCommunication($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"communication" must be array of objects or null, ' . gettype($data['communication']) . ' seen.');
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
        if (0 < count($this->identifier)) {
            $json['identifier'] = [];
            foreach ($this->identifier as $identifier) {
                $json['identifier'][] = $identifier;
            }
        }
        if (isset($this->active)) {
            $json['active'] = $this->active;
        }
        if (0 < count($this->name)) {
            $json['name'] = [];
            foreach ($this->name as $name) {
                $json['name'][] = $name;
            }
        }
        if (0 < count($this->telecom)) {
            $json['telecom'] = [];
            foreach ($this->telecom as $telecom) {
                $json['telecom'][] = $telecom;
            }
        }
        if (0 < count($this->address)) {
            $json['address'] = [];
            foreach ($this->address as $address) {
                $json['address'][] = $address;
            }
        }
        if (isset($this->gender)) {
            $json['gender'] = $this->gender;
        }
        if (isset($this->birthDate)) {
            $json['birthDate'] = $this->birthDate;
        }
        if (0 < count($this->photo)) {
            $json['photo'] = [];
            foreach ($this->photo as $photo) {
                $json['photo'][] = $photo;
            }
        }
        if (0 < count($this->qualification)) {
            $json['qualification'] = [];
            foreach ($this->qualification as $qualification) {
                $json['qualification'][] = $qualification;
            }
        }
        if (0 < count($this->communication)) {
            $json['communication'] = [];
            foreach ($this->communication as $communication) {
                $json['communication'][] = $communication;
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
            $sxe = new \SimpleXMLElement('<Practitioner xmlns="http://hl7.org/fhir"></Practitioner>');
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
        if (0 < count($this->name)) {
            foreach ($this->name as $name) {
                $name->xmlSerialize(true, $sxe->addChild('name'));
            }
        }
        if (0 < count($this->telecom)) {
            foreach ($this->telecom as $telecom) {
                $telecom->xmlSerialize(true, $sxe->addChild('telecom'));
            }
        }
        if (0 < count($this->address)) {
            foreach ($this->address as $address) {
                $address->xmlSerialize(true, $sxe->addChild('address'));
            }
        }
        if (isset($this->gender)) {
            $this->gender->xmlSerialize(true, $sxe->addChild('gender'));
        }
        if (isset($this->birthDate)) {
            $this->birthDate->xmlSerialize(true, $sxe->addChild('birthDate'));
        }
        if (0 < count($this->photo)) {
            foreach ($this->photo as $photo) {
                $photo->xmlSerialize(true, $sxe->addChild('photo'));
            }
        }
        if (0 < count($this->qualification)) {
            foreach ($this->qualification as $qualification) {
                $qualification->xmlSerialize(true, $sxe->addChild('qualification'));
            }
        }
        if (0 < count($this->communication)) {
            foreach ($this->communication as $communication) {
                $communication->xmlSerialize(true, $sxe->addChild('communication'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
