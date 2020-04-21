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
 * This resource provides the insurance enrollment details to the insurer regarding a specified coverage.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIREnrollmentRequest extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * The Response business identifier.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * The status of the resource instance.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRFinancialResourceStatusCodes
     */
    public $status = null;

    /**
     * The date when this resource was created.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $created = null;

    /**
     * The Insurer who is target  of the request.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $insurer = null;

    /**
     * The practitioner who is responsible for the services rendered to the patient.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $provider = null;

    /**
     * Patient Resource.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $candidate = null;

    /**
     * Reference to the program or plan identification, underwriter or payor.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $coverage = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'EnrollmentRequest';

    /**
     * The Response business identifier.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * The Response business identifier.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * The status of the resource instance.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRFinancialResourceStatusCodes
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * The status of the resource instance.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRFinancialResourceStatusCodes $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * The date when this resource was created.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * The date when this resource was created.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $created
     * @return $this
     */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }

    /**
     * The Insurer who is target  of the request.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getInsurer()
    {
        return $this->insurer;
    }

    /**
     * The Insurer who is target  of the request.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $insurer
     * @return $this
     */
    public function setInsurer($insurer)
    {
        $this->insurer = $insurer;
        return $this;
    }

    /**
     * The practitioner who is responsible for the services rendered to the patient.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * The practitioner who is responsible for the services rendered to the patient.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $provider
     * @return $this
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
        return $this;
    }

    /**
     * Patient Resource.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getCandidate()
    {
        return $this->candidate;
    }

    /**
     * Patient Resource.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $candidate
     * @return $this
     */
    public function setCandidate($candidate)
    {
        $this->candidate = $candidate;
        return $this;
    }

    /**
     * Reference to the program or plan identification, underwriter or payor.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getCoverage()
    {
        return $this->coverage;
    }

    /**
     * Reference to the program or plan identification, underwriter or payor.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $coverage
     * @return $this
     */
    public function setCoverage($coverage)
    {
        $this->coverage = $coverage;
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
            if (isset($data['created'])) {
                $this->setCreated($data['created']);
            }
            if (isset($data['insurer'])) {
                $this->setInsurer($data['insurer']);
            }
            if (isset($data['provider'])) {
                $this->setProvider($data['provider']);
            }
            if (isset($data['candidate'])) {
                $this->setCandidate($data['candidate']);
            }
            if (isset($data['coverage'])) {
                $this->setCoverage($data['coverage']);
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
        if (0 < count($this->identifier)) {
            $json['identifier'] = [];
            foreach ($this->identifier as $identifier) {
                $json['identifier'][] = $identifier;
            }
        }
        if (isset($this->status)) {
            $json['status'] = $this->status;
        }
        if (isset($this->created)) {
            $json['created'] = $this->created;
        }
        if (isset($this->insurer)) {
            $json['insurer'] = $this->insurer;
        }
        if (isset($this->provider)) {
            $json['provider'] = $this->provider;
        }
        if (isset($this->candidate)) {
            $json['candidate'] = $this->candidate;
        }
        if (isset($this->coverage)) {
            $json['coverage'] = $this->coverage;
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
            $sxe = new \SimpleXMLElement('<EnrollmentRequest xmlns="http://hl7.org/fhir"></EnrollmentRequest>');
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
        if (isset($this->created)) {
            $this->created->xmlSerialize(true, $sxe->addChild('created'));
        }
        if (isset($this->insurer)) {
            $this->insurer->xmlSerialize(true, $sxe->addChild('insurer'));
        }
        if (isset($this->provider)) {
            $this->provider->xmlSerialize(true, $sxe->addChild('provider'));
        }
        if (isset($this->candidate)) {
            $this->candidate->xmlSerialize(true, $sxe->addChild('candidate'));
        }
        if (isset($this->coverage)) {
            $this->coverage->xmlSerialize(true, $sxe->addChild('coverage'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
