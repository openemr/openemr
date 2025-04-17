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
 * A financial tool for tracking value accrued for a particular purpose.  In the healthcare field, used to track charges for a patient, cost centers, etc.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRAccount extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * Unique identifier used to reference the account.  Might or might not be intended for human use (e.g. credit card number).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * Indicates whether the account is presently used/usable or not.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRAccountStatus
     */
    public $status = null;

    /**
     * Categorizes the account for reporting and searching purposes.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $type = null;

    /**
     * Name used for the account when displaying it to humans in reports, etc.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $name = null;

    /**
     * Identifies the entity which incurs the expenses. While the immediate recipients of services or goods might be entities related to the subject, the expenses were ultimately incurred by the subject of the Account.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $subject = [];

    /**
     * The date range of services associated with this account.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public $servicePeriod = null;

    /**
     * The party(s) that are responsible for covering the payment of this account, and what order should they be applied to the account.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRAccount\FHIRAccountCoverage[]
     */
    public $coverage = [];

    /**
     * Indicates the service area, hospital, department, etc. with responsibility for managing the Account.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $owner = null;

    /**
     * Provides additional information about what the account tracks and how it is used.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $description = null;

    /**
     * The parties responsible for balancing the account if other payment options fall short.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRAccount\FHIRAccountGuarantor[]
     */
    public $guarantor = [];

    /**
     * Reference to a parent Account.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $partOf = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Account';

    /**
     * Unique identifier used to reference the account.  Might or might not be intended for human use (e.g. credit card number).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Unique identifier used to reference the account.  Might or might not be intended for human use (e.g. credit card number).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * Indicates whether the account is presently used/usable or not.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRAccountStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Indicates whether the account is presently used/usable or not.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRAccountStatus $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Categorizes the account for reporting and searching purposes.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Categorizes the account for reporting and searching purposes.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Name used for the account when displaying it to humans in reports, etc.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Name used for the account when displaying it to humans in reports, etc.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Identifies the entity which incurs the expenses. While the immediate recipients of services or goods might be entities related to the subject, the expenses were ultimately incurred by the subject of the Account.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Identifies the entity which incurs the expenses. While the immediate recipients of services or goods might be entities related to the subject, the expenses were ultimately incurred by the subject of the Account.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $subject
     * @return $this
     */
    public function addSubject($subject)
    {
        $this->subject[] = $subject;
        return $this;
    }

    /**
     * The date range of services associated with this account.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public function getServicePeriod()
    {
        return $this->servicePeriod;
    }

    /**
     * The date range of services associated with this account.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod $servicePeriod
     * @return $this
     */
    public function setServicePeriod($servicePeriod)
    {
        $this->servicePeriod = $servicePeriod;
        return $this;
    }

    /**
     * The party(s) that are responsible for covering the payment of this account, and what order should they be applied to the account.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRAccount\FHIRAccountCoverage[]
     */
    public function getCoverage()
    {
        return $this->coverage;
    }

    /**
     * The party(s) that are responsible for covering the payment of this account, and what order should they be applied to the account.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRAccount\FHIRAccountCoverage $coverage
     * @return $this
     */
    public function addCoverage($coverage)
    {
        $this->coverage[] = $coverage;
        return $this;
    }

    /**
     * Indicates the service area, hospital, department, etc. with responsibility for managing the Account.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Indicates the service area, hospital, department, etc. with responsibility for managing the Account.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $owner
     * @return $this
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
        return $this;
    }

    /**
     * Provides additional information about what the account tracks and how it is used.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Provides additional information about what the account tracks and how it is used.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * The parties responsible for balancing the account if other payment options fall short.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRAccount\FHIRAccountGuarantor[]
     */
    public function getGuarantor()
    {
        return $this->guarantor;
    }

    /**
     * The parties responsible for balancing the account if other payment options fall short.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRAccount\FHIRAccountGuarantor $guarantor
     * @return $this
     */
    public function addGuarantor($guarantor)
    {
        $this->guarantor[] = $guarantor;
        return $this;
    }

    /**
     * Reference to a parent Account.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getPartOf()
    {
        return $this->partOf;
    }

    /**
     * Reference to a parent Account.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $partOf
     * @return $this
     */
    public function setPartOf($partOf)
    {
        $this->partOf = $partOf;
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
            if (isset($data['status'])) {
                $this->setStatus($data['status']);
            }
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['name'])) {
                $this->setName($data['name']);
            }
            if (isset($data['subject'])) {
                if (is_array($data['subject'])) {
                    foreach ($data['subject'] as $d) {
                        $this->addSubject($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"subject" must be array of objects or null, ' . gettype($data['subject']) . ' seen.');
                }
            }
            if (isset($data['servicePeriod'])) {
                $this->setServicePeriod($data['servicePeriod']);
            }
            if (isset($data['coverage'])) {
                if (is_array($data['coverage'])) {
                    foreach ($data['coverage'] as $d) {
                        $this->addCoverage($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"coverage" must be array of objects or null, ' . gettype($data['coverage']) . ' seen.');
                }
            }
            if (isset($data['owner'])) {
                $this->setOwner($data['owner']);
            }
            if (isset($data['description'])) {
                $this->setDescription($data['description']);
            }
            if (isset($data['guarantor'])) {
                if (is_array($data['guarantor'])) {
                    foreach ($data['guarantor'] as $d) {
                        $this->addGuarantor($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"guarantor" must be array of objects or null, ' . gettype($data['guarantor']) . ' seen.');
                }
            }
            if (isset($data['partOf'])) {
                $this->setPartOf($data['partOf']);
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
        if (isset($this->status)) {
            $json['status'] = $this->status;
        }
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->name)) {
            $json['name'] = $this->name;
        }
        if (0 < count($this->subject)) {
            $json['subject'] = [];
            foreach ($this->subject as $subject) {
                $json['subject'][] = $subject;
            }
        }
        if (isset($this->servicePeriod)) {
            $json['servicePeriod'] = $this->servicePeriod;
        }
        if (0 < count($this->coverage)) {
            $json['coverage'] = [];
            foreach ($this->coverage as $coverage) {
                $json['coverage'][] = $coverage;
            }
        }
        if (isset($this->owner)) {
            $json['owner'] = $this->owner;
        }
        if (isset($this->description)) {
            $json['description'] = $this->description;
        }
        if (0 < count($this->guarantor)) {
            $json['guarantor'] = [];
            foreach ($this->guarantor as $guarantor) {
                $json['guarantor'][] = $guarantor;
            }
        }
        if (isset($this->partOf)) {
            $json['partOf'] = $this->partOf;
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
            $sxe = new \SimpleXMLElement('<Account xmlns="http://hl7.org/fhir"></Account>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->identifier)) {
            foreach ($this->identifier as $identifier) {
                $identifier->xmlSerialize(true, $sxe->addChild('identifier'));
            }
        }
        if (isset($this->status)) {
            $this->status->xmlSerialize(true, $sxe->addChild('status'));
        }
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->name)) {
            $this->name->xmlSerialize(true, $sxe->addChild('name'));
        }
        if (0 < count($this->subject)) {
            foreach ($this->subject as $subject) {
                $subject->xmlSerialize(true, $sxe->addChild('subject'));
            }
        }
        if (isset($this->servicePeriod)) {
            $this->servicePeriod->xmlSerialize(true, $sxe->addChild('servicePeriod'));
        }
        if (0 < count($this->coverage)) {
            foreach ($this->coverage as $coverage) {
                $coverage->xmlSerialize(true, $sxe->addChild('coverage'));
            }
        }
        if (isset($this->owner)) {
            $this->owner->xmlSerialize(true, $sxe->addChild('owner'));
        }
        if (isset($this->description)) {
            $this->description->xmlSerialize(true, $sxe->addChild('description'));
        }
        if (0 < count($this->guarantor)) {
            foreach ($this->guarantor as $guarantor) {
                $guarantor->xmlSerialize(true, $sxe->addChild('guarantor'));
            }
        }
        if (isset($this->partOf)) {
            $this->partOf->xmlSerialize(true, $sxe->addChild('partOf'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
