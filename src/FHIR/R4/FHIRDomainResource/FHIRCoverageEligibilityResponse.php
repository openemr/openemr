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
 * This resource provides eligibility and plan details from the processing of an CoverageEligibilityRequest resource.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRCoverageEligibilityResponse extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * A unique identifier assigned to this coverage eligiblity request.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * The status of the resource instance.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRFinancialResourceStatusCodes
     */
    public $status = null;

    /**
     * Code to specify whether requesting: prior authorization requirements for some service categories or billing codes; benefits for coverages specified or discovered; discovery and return of coverages for the patient; and/or validation that the specified coverage is in-force at the date/period specified or 'now' if not specified.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIREligibilityResponsePurpose[]
     */
    public $purpose = [];

    /**
     * The party who is the beneficiary of the supplied coverage and for whom eligibility is sought.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $patient = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDate
     */
    public $servicedDate = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public $servicedPeriod = null;

    /**
     * The date this resource was created.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $created = null;

    /**
     * The provider which is responsible for the request.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $requestor = null;

    /**
     * Reference to the original request resource.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $request = null;

    /**
     * The outcome of the request processing.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRRemittanceOutcome
     */
    public $outcome = null;

    /**
     * A human readable description of the status of the adjudication.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $disposition = null;

    /**
     * The Insurer who issued the coverage in question and is the author of the response.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $insurer = null;

    /**
     * Financial instruments for reimbursement for the health care products and services.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRCoverageEligibilityResponse\FHIRCoverageEligibilityResponseInsurance[]
     */
    public $insurance = [];

    /**
     * A reference from the Insurer to which these services pertain to be used on further communication and as proof that the request occurred.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $preAuthRef = null;

    /**
     * A code for the form to be used for printing the content.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $form = null;

    /**
     * Errors encountered during the processing of the request.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRCoverageEligibilityResponse\FHIRCoverageEligibilityResponseError[]
     */
    public $error = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'CoverageEligibilityResponse';

    /**
     * A unique identifier assigned to this coverage eligiblity request.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * A unique identifier assigned to this coverage eligiblity request.
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
     * Code to specify whether requesting: prior authorization requirements for some service categories or billing codes; benefits for coverages specified or discovered; discovery and return of coverages for the patient; and/or validation that the specified coverage is in-force at the date/period specified or 'now' if not specified.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIREligibilityResponsePurpose[]
     */
    public function getPurpose()
    {
        return $this->purpose;
    }

    /**
     * Code to specify whether requesting: prior authorization requirements for some service categories or billing codes; benefits for coverages specified or discovered; discovery and return of coverages for the patient; and/or validation that the specified coverage is in-force at the date/period specified or 'now' if not specified.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIREligibilityResponsePurpose $purpose
     * @return $this
     */
    public function addPurpose($purpose)
    {
        $this->purpose[] = $purpose;
        return $this;
    }

    /**
     * The party who is the beneficiary of the supplied coverage and for whom eligibility is sought.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getPatient()
    {
        return $this->patient;
    }

    /**
     * The party who is the beneficiary of the supplied coverage and for whom eligibility is sought.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $patient
     * @return $this
     */
    public function setPatient($patient)
    {
        $this->patient = $patient;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDate
     */
    public function getServicedDate()
    {
        return $this->servicedDate;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDate $servicedDate
     * @return $this
     */
    public function setServicedDate($servicedDate)
    {
        $this->servicedDate = $servicedDate;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public function getServicedPeriod()
    {
        return $this->servicedPeriod;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod $servicedPeriod
     * @return $this
     */
    public function setServicedPeriod($servicedPeriod)
    {
        $this->servicedPeriod = $servicedPeriod;
        return $this;
    }

    /**
     * The date this resource was created.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * The date this resource was created.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $created
     * @return $this
     */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }

    /**
     * The provider which is responsible for the request.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getRequestor()
    {
        return $this->requestor;
    }

    /**
     * The provider which is responsible for the request.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $requestor
     * @return $this
     */
    public function setRequestor($requestor)
    {
        $this->requestor = $requestor;
        return $this;
    }

    /**
     * Reference to the original request resource.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Reference to the original request resource.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $request
     * @return $this
     */
    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * The outcome of the request processing.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRRemittanceOutcome
     */
    public function getOutcome()
    {
        return $this->outcome;
    }

    /**
     * The outcome of the request processing.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRRemittanceOutcome $outcome
     * @return $this
     */
    public function setOutcome($outcome)
    {
        $this->outcome = $outcome;
        return $this;
    }

    /**
     * A human readable description of the status of the adjudication.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getDisposition()
    {
        return $this->disposition;
    }

    /**
     * A human readable description of the status of the adjudication.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $disposition
     * @return $this
     */
    public function setDisposition($disposition)
    {
        $this->disposition = $disposition;
        return $this;
    }

    /**
     * The Insurer who issued the coverage in question and is the author of the response.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getInsurer()
    {
        return $this->insurer;
    }

    /**
     * The Insurer who issued the coverage in question and is the author of the response.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $insurer
     * @return $this
     */
    public function setInsurer($insurer)
    {
        $this->insurer = $insurer;
        return $this;
    }

    /**
     * Financial instruments for reimbursement for the health care products and services.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRCoverageEligibilityResponse\FHIRCoverageEligibilityResponseInsurance[]
     */
    public function getInsurance()
    {
        return $this->insurance;
    }

    /**
     * Financial instruments for reimbursement for the health care products and services.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRCoverageEligibilityResponse\FHIRCoverageEligibilityResponseInsurance $insurance
     * @return $this
     */
    public function addInsurance($insurance)
    {
        $this->insurance[] = $insurance;
        return $this;
    }

    /**
     * A reference from the Insurer to which these services pertain to be used on further communication and as proof that the request occurred.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getPreAuthRef()
    {
        return $this->preAuthRef;
    }

    /**
     * A reference from the Insurer to which these services pertain to be used on further communication and as proof that the request occurred.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $preAuthRef
     * @return $this
     */
    public function setPreAuthRef($preAuthRef)
    {
        $this->preAuthRef = $preAuthRef;
        return $this;
    }

    /**
     * A code for the form to be used for printing the content.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * A code for the form to be used for printing the content.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $form
     * @return $this
     */
    public function setForm($form)
    {
        $this->form = $form;
        return $this;
    }

    /**
     * Errors encountered during the processing of the request.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRCoverageEligibilityResponse\FHIRCoverageEligibilityResponseError[]
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Errors encountered during the processing of the request.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRCoverageEligibilityResponse\FHIRCoverageEligibilityResponseError $error
     * @return $this
     */
    public function addError($error)
    {
        $this->error[] = $error;
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
            if (isset($data['purpose'])) {
                if (is_array($data['purpose'])) {
                    foreach ($data['purpose'] as $d) {
                        $this->addPurpose($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"purpose" must be array of objects or null, ' . gettype($data['purpose']) . ' seen.');
                }
            }
            if (isset($data['patient'])) {
                $this->setPatient($data['patient']);
            }
            if (isset($data['servicedDate'])) {
                $this->setServicedDate($data['servicedDate']);
            }
            if (isset($data['servicedPeriod'])) {
                $this->setServicedPeriod($data['servicedPeriod']);
            }
            if (isset($data['created'])) {
                $this->setCreated($data['created']);
            }
            if (isset($data['requestor'])) {
                $this->setRequestor($data['requestor']);
            }
            if (isset($data['request'])) {
                $this->setRequest($data['request']);
            }
            if (isset($data['outcome'])) {
                $this->setOutcome($data['outcome']);
            }
            if (isset($data['disposition'])) {
                $this->setDisposition($data['disposition']);
            }
            if (isset($data['insurer'])) {
                $this->setInsurer($data['insurer']);
            }
            if (isset($data['insurance'])) {
                if (is_array($data['insurance'])) {
                    foreach ($data['insurance'] as $d) {
                        $this->addInsurance($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"insurance" must be array of objects or null, ' . gettype($data['insurance']) . ' seen.');
                }
            }
            if (isset($data['preAuthRef'])) {
                $this->setPreAuthRef($data['preAuthRef']);
            }
            if (isset($data['form'])) {
                $this->setForm($data['form']);
            }
            if (isset($data['error'])) {
                if (is_array($data['error'])) {
                    foreach ($data['error'] as $d) {
                        $this->addError($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"error" must be array of objects or null, ' . gettype($data['error']) . ' seen.');
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
        if (isset($this->status)) {
            $json['status'] = $this->status;
        }
        if (0 < count($this->purpose)) {
            $json['purpose'] = [];
            foreach ($this->purpose as $purpose) {
                $json['purpose'][] = $purpose;
            }
        }
        if (isset($this->patient)) {
            $json['patient'] = $this->patient;
        }
        if (isset($this->servicedDate)) {
            $json['servicedDate'] = $this->servicedDate;
        }
        if (isset($this->servicedPeriod)) {
            $json['servicedPeriod'] = $this->servicedPeriod;
        }
        if (isset($this->created)) {
            $json['created'] = $this->created;
        }
        if (isset($this->requestor)) {
            $json['requestor'] = $this->requestor;
        }
        if (isset($this->request)) {
            $json['request'] = $this->request;
        }
        if (isset($this->outcome)) {
            $json['outcome'] = $this->outcome;
        }
        if (isset($this->disposition)) {
            $json['disposition'] = $this->disposition;
        }
        if (isset($this->insurer)) {
            $json['insurer'] = $this->insurer;
        }
        if (0 < count($this->insurance)) {
            $json['insurance'] = [];
            foreach ($this->insurance as $insurance) {
                $json['insurance'][] = $insurance;
            }
        }
        if (isset($this->preAuthRef)) {
            $json['preAuthRef'] = $this->preAuthRef;
        }
        if (isset($this->form)) {
            $json['form'] = $this->form;
        }
        if (0 < count($this->error)) {
            $json['error'] = [];
            foreach ($this->error as $error) {
                $json['error'][] = $error;
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
            $sxe = new \SimpleXMLElement('<CoverageEligibilityResponse xmlns="http://hl7.org/fhir"></CoverageEligibilityResponse>');
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
        if (0 < count($this->purpose)) {
            foreach ($this->purpose as $purpose) {
                $purpose->xmlSerialize(true, $sxe->addChild('purpose'));
            }
        }
        if (isset($this->patient)) {
            $this->patient->xmlSerialize(true, $sxe->addChild('patient'));
        }
        if (isset($this->servicedDate)) {
            $this->servicedDate->xmlSerialize(true, $sxe->addChild('servicedDate'));
        }
        if (isset($this->servicedPeriod)) {
            $this->servicedPeriod->xmlSerialize(true, $sxe->addChild('servicedPeriod'));
        }
        if (isset($this->created)) {
            $this->created->xmlSerialize(true, $sxe->addChild('created'));
        }
        if (isset($this->requestor)) {
            $this->requestor->xmlSerialize(true, $sxe->addChild('requestor'));
        }
        if (isset($this->request)) {
            $this->request->xmlSerialize(true, $sxe->addChild('request'));
        }
        if (isset($this->outcome)) {
            $this->outcome->xmlSerialize(true, $sxe->addChild('outcome'));
        }
        if (isset($this->disposition)) {
            $this->disposition->xmlSerialize(true, $sxe->addChild('disposition'));
        }
        if (isset($this->insurer)) {
            $this->insurer->xmlSerialize(true, $sxe->addChild('insurer'));
        }
        if (0 < count($this->insurance)) {
            foreach ($this->insurance as $insurance) {
                $insurance->xmlSerialize(true, $sxe->addChild('insurance'));
            }
        }
        if (isset($this->preAuthRef)) {
            $this->preAuthRef->xmlSerialize(true, $sxe->addChild('preAuthRef'));
        }
        if (isset($this->form)) {
            $this->form->xmlSerialize(true, $sxe->addChild('form'));
        }
        if (0 < count($this->error)) {
            foreach ($this->error as $error) {
                $error->xmlSerialize(true, $sxe->addChild('error'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
