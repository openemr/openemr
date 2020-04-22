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
 * Describes validation requirements, source(s), status and dates for one or more elements.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRVerificationResult extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * A resource that was validated.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $target = [];

    /**
     * The fhirpath location(s) within the resource that was validated.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public $targetLocation = [];

    /**
     * The frequency with which the target must be validated (none; initial; periodic).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $need = null;

    /**
     * The validation status of the target (attested; validated; in process; requires revalidation; validation failed; revalidation failed).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRStatus
     */
    public $status = null;

    /**
     * When the validation status was updated.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $statusDate = null;

    /**
     * What the target is validated against (nothing; primary source; multiple sources).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $validationType = null;

    /**
     * The primary process by which the target is validated (edit check; value set; primary source; multiple sources; standalone; in context).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $validationProcess = [];

    /**
     * Frequency of revalidation.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRTiming
     */
    public $frequency = null;

    /**
     * The date/time validation was last completed (including failed validations).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $lastPerformed = null;

    /**
     * The date when target is next validated, if appropriate.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDate
     */
    public $nextScheduled = null;

    /**
     * The result if validation fails (fatal; warning; record only; none).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $failureAction = null;

    /**
     * Information about the primary source(s) involved in validation.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRVerificationResult\FHIRVerificationResultPrimarySource[]
     */
    public $primarySource = [];

    /**
     * Information about the entity attesting to information.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRVerificationResult\FHIRVerificationResultAttestation
     */
    public $attestation = null;

    /**
     * Information about the entity validating information.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRVerificationResult\FHIRVerificationResultValidator[]
     */
    public $validator = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'VerificationResult';

    /**
     * A resource that was validated.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * A resource that was validated.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $target
     * @return $this
     */
    public function addTarget($target)
    {
        $this->target[] = $target;
        return $this;
    }

    /**
     * The fhirpath location(s) within the resource that was validated.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public function getTargetLocation()
    {
        return $this->targetLocation;
    }

    /**
     * The fhirpath location(s) within the resource that was validated.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $targetLocation
     * @return $this
     */
    public function addTargetLocation($targetLocation)
    {
        $this->targetLocation[] = $targetLocation;
        return $this;
    }

    /**
     * The frequency with which the target must be validated (none; initial; periodic).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getNeed()
    {
        return $this->need;
    }

    /**
     * The frequency with which the target must be validated (none; initial; periodic).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $need
     * @return $this
     */
    public function setNeed($need)
    {
        $this->need = $need;
        return $this;
    }

    /**
     * The validation status of the target (attested; validated; in process; requires revalidation; validation failed; revalidation failed).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * The validation status of the target (attested; validated; in process; requires revalidation; validation failed; revalidation failed).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRStatus $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * When the validation status was updated.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getStatusDate()
    {
        return $this->statusDate;
    }

    /**
     * When the validation status was updated.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $statusDate
     * @return $this
     */
    public function setStatusDate($statusDate)
    {
        $this->statusDate = $statusDate;
        return $this;
    }

    /**
     * What the target is validated against (nothing; primary source; multiple sources).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getValidationType()
    {
        return $this->validationType;
    }

    /**
     * What the target is validated against (nothing; primary source; multiple sources).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $validationType
     * @return $this
     */
    public function setValidationType($validationType)
    {
        $this->validationType = $validationType;
        return $this;
    }

    /**
     * The primary process by which the target is validated (edit check; value set; primary source; multiple sources; standalone; in context).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getValidationProcess()
    {
        return $this->validationProcess;
    }

    /**
     * The primary process by which the target is validated (edit check; value set; primary source; multiple sources; standalone; in context).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $validationProcess
     * @return $this
     */
    public function addValidationProcess($validationProcess)
    {
        $this->validationProcess[] = $validationProcess;
        return $this;
    }

    /**
     * Frequency of revalidation.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRTiming
     */
    public function getFrequency()
    {
        return $this->frequency;
    }

    /**
     * Frequency of revalidation.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRTiming $frequency
     * @return $this
     */
    public function setFrequency($frequency)
    {
        $this->frequency = $frequency;
        return $this;
    }

    /**
     * The date/time validation was last completed (including failed validations).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getLastPerformed()
    {
        return $this->lastPerformed;
    }

    /**
     * The date/time validation was last completed (including failed validations).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $lastPerformed
     * @return $this
     */
    public function setLastPerformed($lastPerformed)
    {
        $this->lastPerformed = $lastPerformed;
        return $this;
    }

    /**
     * The date when target is next validated, if appropriate.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDate
     */
    public function getNextScheduled()
    {
        return $this->nextScheduled;
    }

    /**
     * The date when target is next validated, if appropriate.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDate $nextScheduled
     * @return $this
     */
    public function setNextScheduled($nextScheduled)
    {
        $this->nextScheduled = $nextScheduled;
        return $this;
    }

    /**
     * The result if validation fails (fatal; warning; record only; none).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getFailureAction()
    {
        return $this->failureAction;
    }

    /**
     * The result if validation fails (fatal; warning; record only; none).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $failureAction
     * @return $this
     */
    public function setFailureAction($failureAction)
    {
        $this->failureAction = $failureAction;
        return $this;
    }

    /**
     * Information about the primary source(s) involved in validation.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRVerificationResult\FHIRVerificationResultPrimarySource[]
     */
    public function getPrimarySource()
    {
        return $this->primarySource;
    }

    /**
     * Information about the primary source(s) involved in validation.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRVerificationResult\FHIRVerificationResultPrimarySource $primarySource
     * @return $this
     */
    public function addPrimarySource($primarySource)
    {
        $this->primarySource[] = $primarySource;
        return $this;
    }

    /**
     * Information about the entity attesting to information.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRVerificationResult\FHIRVerificationResultAttestation
     */
    public function getAttestation()
    {
        return $this->attestation;
    }

    /**
     * Information about the entity attesting to information.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRVerificationResult\FHIRVerificationResultAttestation $attestation
     * @return $this
     */
    public function setAttestation($attestation)
    {
        $this->attestation = $attestation;
        return $this;
    }

    /**
     * Information about the entity validating information.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRVerificationResult\FHIRVerificationResultValidator[]
     */
    public function getValidator()
    {
        return $this->validator;
    }

    /**
     * Information about the entity validating information.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRVerificationResult\FHIRVerificationResultValidator $validator
     * @return $this
     */
    public function addValidator($validator)
    {
        $this->validator[] = $validator;
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
            if (isset($data['target'])) {
                if (is_array($data['target'])) {
                    foreach ($data['target'] as $d) {
                        $this->addTarget($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"target" must be array of objects or null, ' . gettype($data['target']) . ' seen.');
                }
            }
            if (isset($data['targetLocation'])) {
                if (is_array($data['targetLocation'])) {
                    foreach ($data['targetLocation'] as $d) {
                        $this->addTargetLocation($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"targetLocation" must be array of objects or null, ' . gettype($data['targetLocation']) . ' seen.');
                }
            }
            if (isset($data['need'])) {
                $this->setNeed($data['need']);
            }
            if (isset($data['status'])) {
                $this->setStatus($data['status']);
            }
            if (isset($data['statusDate'])) {
                $this->setStatusDate($data['statusDate']);
            }
            if (isset($data['validationType'])) {
                $this->setValidationType($data['validationType']);
            }
            if (isset($data['validationProcess'])) {
                if (is_array($data['validationProcess'])) {
                    foreach ($data['validationProcess'] as $d) {
                        $this->addValidationProcess($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"validationProcess" must be array of objects or null, ' . gettype($data['validationProcess']) . ' seen.');
                }
            }
            if (isset($data['frequency'])) {
                $this->setFrequency($data['frequency']);
            }
            if (isset($data['lastPerformed'])) {
                $this->setLastPerformed($data['lastPerformed']);
            }
            if (isset($data['nextScheduled'])) {
                $this->setNextScheduled($data['nextScheduled']);
            }
            if (isset($data['failureAction'])) {
                $this->setFailureAction($data['failureAction']);
            }
            if (isset($data['primarySource'])) {
                if (is_array($data['primarySource'])) {
                    foreach ($data['primarySource'] as $d) {
                        $this->addPrimarySource($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"primarySource" must be array of objects or null, ' . gettype($data['primarySource']) . ' seen.');
                }
            }
            if (isset($data['attestation'])) {
                $this->setAttestation($data['attestation']);
            }
            if (isset($data['validator'])) {
                if (is_array($data['validator'])) {
                    foreach ($data['validator'] as $d) {
                        $this->addValidator($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"validator" must be array of objects or null, ' . gettype($data['validator']) . ' seen.');
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
    public function jsonSerialize()
    {
        $json = parent::jsonSerialize();
        $json['resourceType'] = $this->_fhirElementName;
        if (0 < count($this->target)) {
            $json['target'] = [];
            foreach ($this->target as $target) {
                $json['target'][] = $target;
            }
        }
        if (0 < count($this->targetLocation)) {
            $json['targetLocation'] = [];
            foreach ($this->targetLocation as $targetLocation) {
                $json['targetLocation'][] = $targetLocation;
            }
        }
        if (isset($this->need)) {
            $json['need'] = $this->need;
        }
        if (isset($this->status)) {
            $json['status'] = $this->status;
        }
        if (isset($this->statusDate)) {
            $json['statusDate'] = $this->statusDate;
        }
        if (isset($this->validationType)) {
            $json['validationType'] = $this->validationType;
        }
        if (0 < count($this->validationProcess)) {
            $json['validationProcess'] = [];
            foreach ($this->validationProcess as $validationProcess) {
                $json['validationProcess'][] = $validationProcess;
            }
        }
        if (isset($this->frequency)) {
            $json['frequency'] = $this->frequency;
        }
        if (isset($this->lastPerformed)) {
            $json['lastPerformed'] = $this->lastPerformed;
        }
        if (isset($this->nextScheduled)) {
            $json['nextScheduled'] = $this->nextScheduled;
        }
        if (isset($this->failureAction)) {
            $json['failureAction'] = $this->failureAction;
        }
        if (0 < count($this->primarySource)) {
            $json['primarySource'] = [];
            foreach ($this->primarySource as $primarySource) {
                $json['primarySource'][] = $primarySource;
            }
        }
        if (isset($this->attestation)) {
            $json['attestation'] = $this->attestation;
        }
        if (0 < count($this->validator)) {
            $json['validator'] = [];
            foreach ($this->validator as $validator) {
                $json['validator'][] = $validator;
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
            $sxe = new \SimpleXMLElement('<VerificationResult xmlns="http://hl7.org/fhir"></VerificationResult>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->target)) {
            foreach ($this->target as $target) {
                $target->xmlSerialize(true, $sxe->addChild('target'));
            }
        }
        if (0 < count($this->targetLocation)) {
            foreach ($this->targetLocation as $targetLocation) {
                $targetLocation->xmlSerialize(true, $sxe->addChild('targetLocation'));
            }
        }
        if (isset($this->need)) {
            $this->need->xmlSerialize(true, $sxe->addChild('need'));
        }
        if (isset($this->status)) {
            $this->status->xmlSerialize(true, $sxe->addChild('status'));
        }
        if (isset($this->statusDate)) {
            $this->statusDate->xmlSerialize(true, $sxe->addChild('statusDate'));
        }
        if (isset($this->validationType)) {
            $this->validationType->xmlSerialize(true, $sxe->addChild('validationType'));
        }
        if (0 < count($this->validationProcess)) {
            foreach ($this->validationProcess as $validationProcess) {
                $validationProcess->xmlSerialize(true, $sxe->addChild('validationProcess'));
            }
        }
        if (isset($this->frequency)) {
            $this->frequency->xmlSerialize(true, $sxe->addChild('frequency'));
        }
        if (isset($this->lastPerformed)) {
            $this->lastPerformed->xmlSerialize(true, $sxe->addChild('lastPerformed'));
        }
        if (isset($this->nextScheduled)) {
            $this->nextScheduled->xmlSerialize(true, $sxe->addChild('nextScheduled'));
        }
        if (isset($this->failureAction)) {
            $this->failureAction->xmlSerialize(true, $sxe->addChild('failureAction'));
        }
        if (0 < count($this->primarySource)) {
            foreach ($this->primarySource as $primarySource) {
                $primarySource->xmlSerialize(true, $sxe->addChild('primarySource'));
            }
        }
        if (isset($this->attestation)) {
            $this->attestation->xmlSerialize(true, $sxe->addChild('attestation'));
        }
        if (0 < count($this->validator)) {
            foreach ($this->validator as $validator) {
                $validator->xmlSerialize(true, $sxe->addChild('validator'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
