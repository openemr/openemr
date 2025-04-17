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
 * The regulatory authorization of a medicinal product.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRMedicinalProductAuthorization extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * Business identifier for the marketing authorization, as assigned by a regulator.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * The medicinal product that is being authorized.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $subject = null;

    /**
     * The country in which the marketing authorization has been granted.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $country = [];

    /**
     * Jurisdiction within a country.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $jurisdiction = [];

    /**
     * The status of the marketing authorization.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $status = null;

    /**
     * The date at which the given status has become applicable.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $statusDate = null;

    /**
     * The date when a suspended the marketing or the marketing authorization of the product is anticipated to be restored.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $restoreDate = null;

    /**
     * The beginning of the time period in which the marketing authorization is in the specific status shall be specified A complete date consisting of day, month and year shall be specified using the ISO 8601 date format.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public $validityPeriod = null;

    /**
     * A period of time after authorization before generic product applicatiosn can be submitted.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public $dataExclusivityPeriod = null;

    /**
     * The date when the first authorization was granted by a Medicines Regulatory Agency.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $dateOfFirstAuthorization = null;

    /**
     * Date of first marketing authorization for a company's new medicinal product in any country in the World.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $internationalBirthDate = null;

    /**
     * The legal framework against which this authorization is granted.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $legalBasis = null;

    /**
     * Authorization in areas within a country.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProductAuthorization\FHIRMedicinalProductAuthorizationJurisdictionalAuthorization[]
     */
    public $jurisdictionalAuthorization = [];

    /**
     * Marketing Authorization Holder.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $holder = null;

    /**
     * Medicines Regulatory Agency.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $regulator = null;

    /**
     * The regulatory procedure for granting or amending a marketing authorization.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProductAuthorization\FHIRMedicinalProductAuthorizationProcedure
     */
    public $procedure = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'MedicinalProductAuthorization';

    /**
     * Business identifier for the marketing authorization, as assigned by a regulator.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Business identifier for the marketing authorization, as assigned by a regulator.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * The medicinal product that is being authorized.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * The medicinal product that is being authorized.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * The country in which the marketing authorization has been granted.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * The country in which the marketing authorization has been granted.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $country
     * @return $this
     */
    public function addCountry($country)
    {
        $this->country[] = $country;
        return $this;
    }

    /**
     * Jurisdiction within a country.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getJurisdiction()
    {
        return $this->jurisdiction;
    }

    /**
     * Jurisdiction within a country.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $jurisdiction
     * @return $this
     */
    public function addJurisdiction($jurisdiction)
    {
        $this->jurisdiction[] = $jurisdiction;
        return $this;
    }

    /**
     * The status of the marketing authorization.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * The status of the marketing authorization.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * The date at which the given status has become applicable.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getStatusDate()
    {
        return $this->statusDate;
    }

    /**
     * The date at which the given status has become applicable.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $statusDate
     * @return $this
     */
    public function setStatusDate($statusDate)
    {
        $this->statusDate = $statusDate;
        return $this;
    }

    /**
     * The date when a suspended the marketing or the marketing authorization of the product is anticipated to be restored.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getRestoreDate()
    {
        return $this->restoreDate;
    }

    /**
     * The date when a suspended the marketing or the marketing authorization of the product is anticipated to be restored.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $restoreDate
     * @return $this
     */
    public function setRestoreDate($restoreDate)
    {
        $this->restoreDate = $restoreDate;
        return $this;
    }

    /**
     * The beginning of the time period in which the marketing authorization is in the specific status shall be specified A complete date consisting of day, month and year shall be specified using the ISO 8601 date format.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public function getValidityPeriod()
    {
        return $this->validityPeriod;
    }

    /**
     * The beginning of the time period in which the marketing authorization is in the specific status shall be specified A complete date consisting of day, month and year shall be specified using the ISO 8601 date format.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod $validityPeriod
     * @return $this
     */
    public function setValidityPeriod($validityPeriod)
    {
        $this->validityPeriod = $validityPeriod;
        return $this;
    }

    /**
     * A period of time after authorization before generic product applicatiosn can be submitted.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public function getDataExclusivityPeriod()
    {
        return $this->dataExclusivityPeriod;
    }

    /**
     * A period of time after authorization before generic product applicatiosn can be submitted.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod $dataExclusivityPeriod
     * @return $this
     */
    public function setDataExclusivityPeriod($dataExclusivityPeriod)
    {
        $this->dataExclusivityPeriod = $dataExclusivityPeriod;
        return $this;
    }

    /**
     * The date when the first authorization was granted by a Medicines Regulatory Agency.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getDateOfFirstAuthorization()
    {
        return $this->dateOfFirstAuthorization;
    }

    /**
     * The date when the first authorization was granted by a Medicines Regulatory Agency.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $dateOfFirstAuthorization
     * @return $this
     */
    public function setDateOfFirstAuthorization($dateOfFirstAuthorization)
    {
        $this->dateOfFirstAuthorization = $dateOfFirstAuthorization;
        return $this;
    }

    /**
     * Date of first marketing authorization for a company's new medicinal product in any country in the World.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getInternationalBirthDate()
    {
        return $this->internationalBirthDate;
    }

    /**
     * Date of first marketing authorization for a company's new medicinal product in any country in the World.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $internationalBirthDate
     * @return $this
     */
    public function setInternationalBirthDate($internationalBirthDate)
    {
        $this->internationalBirthDate = $internationalBirthDate;
        return $this;
    }

    /**
     * The legal framework against which this authorization is granted.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getLegalBasis()
    {
        return $this->legalBasis;
    }

    /**
     * The legal framework against which this authorization is granted.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $legalBasis
     * @return $this
     */
    public function setLegalBasis($legalBasis)
    {
        $this->legalBasis = $legalBasis;
        return $this;
    }

    /**
     * Authorization in areas within a country.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProductAuthorization\FHIRMedicinalProductAuthorizationJurisdictionalAuthorization[]
     */
    public function getJurisdictionalAuthorization()
    {
        return $this->jurisdictionalAuthorization;
    }

    /**
     * Authorization in areas within a country.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProductAuthorization\FHIRMedicinalProductAuthorizationJurisdictionalAuthorization $jurisdictionalAuthorization
     * @return $this
     */
    public function addJurisdictionalAuthorization($jurisdictionalAuthorization)
    {
        $this->jurisdictionalAuthorization[] = $jurisdictionalAuthorization;
        return $this;
    }

    /**
     * Marketing Authorization Holder.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getHolder()
    {
        return $this->holder;
    }

    /**
     * Marketing Authorization Holder.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $holder
     * @return $this
     */
    public function setHolder($holder)
    {
        $this->holder = $holder;
        return $this;
    }

    /**
     * Medicines Regulatory Agency.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getRegulator()
    {
        return $this->regulator;
    }

    /**
     * Medicines Regulatory Agency.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $regulator
     * @return $this
     */
    public function setRegulator($regulator)
    {
        $this->regulator = $regulator;
        return $this;
    }

    /**
     * The regulatory procedure for granting or amending a marketing authorization.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProductAuthorization\FHIRMedicinalProductAuthorizationProcedure
     */
    public function getProcedure()
    {
        return $this->procedure;
    }

    /**
     * The regulatory procedure for granting or amending a marketing authorization.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRMedicinalProductAuthorization\FHIRMedicinalProductAuthorizationProcedure $procedure
     * @return $this
     */
    public function setProcedure($procedure)
    {
        $this->procedure = $procedure;
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
            if (isset($data['subject'])) {
                $this->setSubject($data['subject']);
            }
            if (isset($data['country'])) {
                if (is_array($data['country'])) {
                    foreach ($data['country'] as $d) {
                        $this->addCountry($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"country" must be array of objects or null, ' . gettype($data['country']) . ' seen.');
                }
            }
            if (isset($data['jurisdiction'])) {
                if (is_array($data['jurisdiction'])) {
                    foreach ($data['jurisdiction'] as $d) {
                        $this->addJurisdiction($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"jurisdiction" must be array of objects or null, ' . gettype($data['jurisdiction']) . ' seen.');
                }
            }
            if (isset($data['status'])) {
                $this->setStatus($data['status']);
            }
            if (isset($data['statusDate'])) {
                $this->setStatusDate($data['statusDate']);
            }
            if (isset($data['restoreDate'])) {
                $this->setRestoreDate($data['restoreDate']);
            }
            if (isset($data['validityPeriod'])) {
                $this->setValidityPeriod($data['validityPeriod']);
            }
            if (isset($data['dataExclusivityPeriod'])) {
                $this->setDataExclusivityPeriod($data['dataExclusivityPeriod']);
            }
            if (isset($data['dateOfFirstAuthorization'])) {
                $this->setDateOfFirstAuthorization($data['dateOfFirstAuthorization']);
            }
            if (isset($data['internationalBirthDate'])) {
                $this->setInternationalBirthDate($data['internationalBirthDate']);
            }
            if (isset($data['legalBasis'])) {
                $this->setLegalBasis($data['legalBasis']);
            }
            if (isset($data['jurisdictionalAuthorization'])) {
                if (is_array($data['jurisdictionalAuthorization'])) {
                    foreach ($data['jurisdictionalAuthorization'] as $d) {
                        $this->addJurisdictionalAuthorization($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"jurisdictionalAuthorization" must be array of objects or null, ' . gettype($data['jurisdictionalAuthorization']) . ' seen.');
                }
            }
            if (isset($data['holder'])) {
                $this->setHolder($data['holder']);
            }
            if (isset($data['regulator'])) {
                $this->setRegulator($data['regulator']);
            }
            if (isset($data['procedure'])) {
                $this->setProcedure($data['procedure']);
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
        if (isset($this->subject)) {
            $json['subject'] = $this->subject;
        }
        if (0 < count($this->country)) {
            $json['country'] = [];
            foreach ($this->country as $country) {
                $json['country'][] = $country;
            }
        }
        if (0 < count($this->jurisdiction)) {
            $json['jurisdiction'] = [];
            foreach ($this->jurisdiction as $jurisdiction) {
                $json['jurisdiction'][] = $jurisdiction;
            }
        }
        if (isset($this->status)) {
            $json['status'] = $this->status;
        }
        if (isset($this->statusDate)) {
            $json['statusDate'] = $this->statusDate;
        }
        if (isset($this->restoreDate)) {
            $json['restoreDate'] = $this->restoreDate;
        }
        if (isset($this->validityPeriod)) {
            $json['validityPeriod'] = $this->validityPeriod;
        }
        if (isset($this->dataExclusivityPeriod)) {
            $json['dataExclusivityPeriod'] = $this->dataExclusivityPeriod;
        }
        if (isset($this->dateOfFirstAuthorization)) {
            $json['dateOfFirstAuthorization'] = $this->dateOfFirstAuthorization;
        }
        if (isset($this->internationalBirthDate)) {
            $json['internationalBirthDate'] = $this->internationalBirthDate;
        }
        if (isset($this->legalBasis)) {
            $json['legalBasis'] = $this->legalBasis;
        }
        if (0 < count($this->jurisdictionalAuthorization)) {
            $json['jurisdictionalAuthorization'] = [];
            foreach ($this->jurisdictionalAuthorization as $jurisdictionalAuthorization) {
                $json['jurisdictionalAuthorization'][] = $jurisdictionalAuthorization;
            }
        }
        if (isset($this->holder)) {
            $json['holder'] = $this->holder;
        }
        if (isset($this->regulator)) {
            $json['regulator'] = $this->regulator;
        }
        if (isset($this->procedure)) {
            $json['procedure'] = $this->procedure;
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
            $sxe = new \SimpleXMLElement('<MedicinalProductAuthorization xmlns="http://hl7.org/fhir"></MedicinalProductAuthorization>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->identifier)) {
            foreach ($this->identifier as $identifier) {
                $identifier->xmlSerialize(true, $sxe->addChild('identifier'));
            }
        }
        if (isset($this->subject)) {
            $this->subject->xmlSerialize(true, $sxe->addChild('subject'));
        }
        if (0 < count($this->country)) {
            foreach ($this->country as $country) {
                $country->xmlSerialize(true, $sxe->addChild('country'));
            }
        }
        if (0 < count($this->jurisdiction)) {
            foreach ($this->jurisdiction as $jurisdiction) {
                $jurisdiction->xmlSerialize(true, $sxe->addChild('jurisdiction'));
            }
        }
        if (isset($this->status)) {
            $this->status->xmlSerialize(true, $sxe->addChild('status'));
        }
        if (isset($this->statusDate)) {
            $this->statusDate->xmlSerialize(true, $sxe->addChild('statusDate'));
        }
        if (isset($this->restoreDate)) {
            $this->restoreDate->xmlSerialize(true, $sxe->addChild('restoreDate'));
        }
        if (isset($this->validityPeriod)) {
            $this->validityPeriod->xmlSerialize(true, $sxe->addChild('validityPeriod'));
        }
        if (isset($this->dataExclusivityPeriod)) {
            $this->dataExclusivityPeriod->xmlSerialize(true, $sxe->addChild('dataExclusivityPeriod'));
        }
        if (isset($this->dateOfFirstAuthorization)) {
            $this->dateOfFirstAuthorization->xmlSerialize(true, $sxe->addChild('dateOfFirstAuthorization'));
        }
        if (isset($this->internationalBirthDate)) {
            $this->internationalBirthDate->xmlSerialize(true, $sxe->addChild('internationalBirthDate'));
        }
        if (isset($this->legalBasis)) {
            $this->legalBasis->xmlSerialize(true, $sxe->addChild('legalBasis'));
        }
        if (0 < count($this->jurisdictionalAuthorization)) {
            foreach ($this->jurisdictionalAuthorization as $jurisdictionalAuthorization) {
                $jurisdictionalAuthorization->xmlSerialize(true, $sxe->addChild('jurisdictionalAuthorization'));
            }
        }
        if (isset($this->holder)) {
            $this->holder->xmlSerialize(true, $sxe->addChild('holder'));
        }
        if (isset($this->regulator)) {
            $this->regulator->xmlSerialize(true, $sxe->addChild('regulator'));
        }
        if (isset($this->procedure)) {
            $this->procedure->xmlSerialize(true, $sxe->addChild('procedure'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
