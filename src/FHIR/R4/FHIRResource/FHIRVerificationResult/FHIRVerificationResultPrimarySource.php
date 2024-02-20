<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRVerificationResult;

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
 * Describes validation requirements, source(s), status and dates for one or more elements.
 */
class FHIRVerificationResultPrimarySource extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Reference to the primary source.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $who = null;

    /**
     * Type of primary source (License Board; Primary Education; Continuing Education; Postal Service; Relationship owner; Registration Authority; legal source; issuing source; authoritative source).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $type = [];

    /**
     * Method for communicating with the primary source (manual; API; Push).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $communicationMethod = [];

    /**
     * Status of the validation of the target against the primary source (successful; failed; unknown).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $validationStatus = null;

    /**
     * When the target was validated against the primary source.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $validationDate = null;

    /**
     * Ability of the primary source to push updates/alerts (yes; no; undetermined).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $canPushUpdates = null;

    /**
     * Type of alerts/updates the primary source can send (specific requested changes; any changes; as defined by source).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $pushTypeAvailable = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'VerificationResult.PrimarySource';

    /**
     * Reference to the primary source.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getWho()
    {
        return $this->who;
    }

    /**
     * Reference to the primary source.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $who
     * @return $this
     */
    public function setWho($who)
    {
        $this->who = $who;
        return $this;
    }

    /**
     * Type of primary source (License Board; Primary Education; Continuing Education; Postal Service; Relationship owner; Registration Authority; legal source; issuing source; authoritative source).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Type of primary source (License Board; Primary Education; Continuing Education; Postal Service; Relationship owner; Registration Authority; legal source; issuing source; authoritative source).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function addType($type)
    {
        $this->type[] = $type;
        return $this;
    }

    /**
     * Method for communicating with the primary source (manual; API; Push).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getCommunicationMethod()
    {
        return $this->communicationMethod;
    }

    /**
     * Method for communicating with the primary source (manual; API; Push).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $communicationMethod
     * @return $this
     */
    public function addCommunicationMethod($communicationMethod)
    {
        $this->communicationMethod[] = $communicationMethod;
        return $this;
    }

    /**
     * Status of the validation of the target against the primary source (successful; failed; unknown).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getValidationStatus()
    {
        return $this->validationStatus;
    }

    /**
     * Status of the validation of the target against the primary source (successful; failed; unknown).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $validationStatus
     * @return $this
     */
    public function setValidationStatus($validationStatus)
    {
        $this->validationStatus = $validationStatus;
        return $this;
    }

    /**
     * When the target was validated against the primary source.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getValidationDate()
    {
        return $this->validationDate;
    }

    /**
     * When the target was validated against the primary source.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $validationDate
     * @return $this
     */
    public function setValidationDate($validationDate)
    {
        $this->validationDate = $validationDate;
        return $this;
    }

    /**
     * Ability of the primary source to push updates/alerts (yes; no; undetermined).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getCanPushUpdates()
    {
        return $this->canPushUpdates;
    }

    /**
     * Ability of the primary source to push updates/alerts (yes; no; undetermined).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $canPushUpdates
     * @return $this
     */
    public function setCanPushUpdates($canPushUpdates)
    {
        $this->canPushUpdates = $canPushUpdates;
        return $this;
    }

    /**
     * Type of alerts/updates the primary source can send (specific requested changes; any changes; as defined by source).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getPushTypeAvailable()
    {
        return $this->pushTypeAvailable;
    }

    /**
     * Type of alerts/updates the primary source can send (specific requested changes; any changes; as defined by source).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $pushTypeAvailable
     * @return $this
     */
    public function addPushTypeAvailable($pushTypeAvailable)
    {
        $this->pushTypeAvailable[] = $pushTypeAvailable;
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
            if (isset($data['who'])) {
                $this->setWho($data['who']);
            }
            if (isset($data['type'])) {
                if (is_array($data['type'])) {
                    foreach ($data['type'] as $d) {
                        $this->addType($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"type" must be array of objects or null, ' . gettype($data['type']) . ' seen.');
                }
            }
            if (isset($data['communicationMethod'])) {
                if (is_array($data['communicationMethod'])) {
                    foreach ($data['communicationMethod'] as $d) {
                        $this->addCommunicationMethod($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"communicationMethod" must be array of objects or null, ' . gettype($data['communicationMethod']) . ' seen.');
                }
            }
            if (isset($data['validationStatus'])) {
                $this->setValidationStatus($data['validationStatus']);
            }
            if (isset($data['validationDate'])) {
                $this->setValidationDate($data['validationDate']);
            }
            if (isset($data['canPushUpdates'])) {
                $this->setCanPushUpdates($data['canPushUpdates']);
            }
            if (isset($data['pushTypeAvailable'])) {
                if (is_array($data['pushTypeAvailable'])) {
                    foreach ($data['pushTypeAvailable'] as $d) {
                        $this->addPushTypeAvailable($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"pushTypeAvailable" must be array of objects or null, ' . gettype($data['pushTypeAvailable']) . ' seen.');
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
        if (isset($this->who)) {
            $json['who'] = $this->who;
        }
        if (0 < count($this->type)) {
            $json['type'] = [];
            foreach ($this->type as $type) {
                $json['type'][] = $type;
            }
        }
        if (0 < count($this->communicationMethod)) {
            $json['communicationMethod'] = [];
            foreach ($this->communicationMethod as $communicationMethod) {
                $json['communicationMethod'][] = $communicationMethod;
            }
        }
        if (isset($this->validationStatus)) {
            $json['validationStatus'] = $this->validationStatus;
        }
        if (isset($this->validationDate)) {
            $json['validationDate'] = $this->validationDate;
        }
        if (isset($this->canPushUpdates)) {
            $json['canPushUpdates'] = $this->canPushUpdates;
        }
        if (0 < count($this->pushTypeAvailable)) {
            $json['pushTypeAvailable'] = [];
            foreach ($this->pushTypeAvailable as $pushTypeAvailable) {
                $json['pushTypeAvailable'][] = $pushTypeAvailable;
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
            $sxe = new \SimpleXMLElement('<VerificationResultPrimarySource xmlns="http://hl7.org/fhir"></VerificationResultPrimarySource>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->who)) {
            $this->who->xmlSerialize(true, $sxe->addChild('who'));
        }
        if (0 < count($this->type)) {
            foreach ($this->type as $type) {
                $type->xmlSerialize(true, $sxe->addChild('type'));
            }
        }
        if (0 < count($this->communicationMethod)) {
            foreach ($this->communicationMethod as $communicationMethod) {
                $communicationMethod->xmlSerialize(true, $sxe->addChild('communicationMethod'));
            }
        }
        if (isset($this->validationStatus)) {
            $this->validationStatus->xmlSerialize(true, $sxe->addChild('validationStatus'));
        }
        if (isset($this->validationDate)) {
            $this->validationDate->xmlSerialize(true, $sxe->addChild('validationDate'));
        }
        if (isset($this->canPushUpdates)) {
            $this->canPushUpdates->xmlSerialize(true, $sxe->addChild('canPushUpdates'));
        }
        if (0 < count($this->pushTypeAvailable)) {
            foreach ($this->pushTypeAvailable as $pushTypeAvailable) {
                $pushTypeAvailable->xmlSerialize(true, $sxe->addChild('pushTypeAvailable'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
