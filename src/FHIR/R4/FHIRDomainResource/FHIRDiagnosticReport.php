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
 * The findings and interpretation of diagnostic  tests performed on patients, groups of patients, devices, and locations, and/or specimens derived from these. The report includes clinical context such as requesting and provider information, and some mix of atomic results, images, textual and coded interpretations, and formatted representation of diagnostic reports.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRDiagnosticReport extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * Identifiers assigned to this report by the performer or other systems.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * Details concerning a service requested.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $basedOn = [];

    /**
     * The status of the diagnostic report.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDiagnosticReportStatus
     */
    public $status = null;

    /**
     * A code that classifies the clinical discipline, department or diagnostic service that created the report (e.g. cardiology, biochemistry, hematology, MRI). This is used for searching, sorting and display purposes.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $category = [];

    /**
     * A code or name that describes this diagnostic report.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $code = null;

    /**
     * The subject of the report. Usually, but not always, this is a patient. However, diagnostic services also perform analyses on specimens collected from a variety of other sources.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $subject = null;

    /**
     * The healthcare event  (e.g. a patient and healthcare provider interaction) which this DiagnosticReport is about.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $encounter = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $effectiveDateTime = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public $effectivePeriod = null;

    /**
     * The date and time that this version of the report was made available to providers, typically after the report was reviewed and verified.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRInstant
     */
    public $issued = null;

    /**
     * The diagnostic service that is responsible for issuing the report.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $performer = [];

    /**
     * The practitioner or organization that is responsible for the report's conclusions and interpretations.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $resultsInterpreter = [];

    /**
     * Details about the specimens on which this diagnostic report is based.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $specimen = [];

    /**
     * [Observations](observation.html)  that are part of this diagnostic report.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $result = [];

    /**
     * One or more links to full details of any imaging performed during the diagnostic investigation. Typically, this is imaging performed by DICOM enabled modalities, but this is not required. A fully enabled PACS viewer can use this information to provide views of the source images.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $imagingStudy = [];

    /**
     * A list of key images associated with this report. The images are generally created during the diagnostic process, and may be directly of the patient, or of treated specimens (i.e. slides of interest).
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRDiagnosticReport\FHIRDiagnosticReportMedia[]
     */
    public $media = [];

    /**
     * Concise and clinically contextualized summary conclusion (interpretation/impression) of the diagnostic report.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $conclusion = null;

    /**
     * One or more codes that represent the summary conclusion (interpretation/impression) of the diagnostic report.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $conclusionCode = [];

    /**
     * Rich text representation of the entire result as issued by the diagnostic service. Multiple formats are allowed but they SHALL be semantically equivalent.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRAttachment[]
     */
    public $presentedForm = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'DiagnosticReport';

    /**
     * Identifiers assigned to this report by the performer or other systems.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Identifiers assigned to this report by the performer or other systems.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * Details concerning a service requested.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getBasedOn()
    {
        return $this->basedOn;
    }

    /**
     * Details concerning a service requested.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $basedOn
     * @return $this
     */
    public function addBasedOn($basedOn)
    {
        $this->basedOn[] = $basedOn;
        return $this;
    }

    /**
     * The status of the diagnostic report.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDiagnosticReportStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * The status of the diagnostic report.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDiagnosticReportStatus $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * A code that classifies the clinical discipline, department or diagnostic service that created the report (e.g. cardiology, biochemistry, hematology, MRI). This is used for searching, sorting and display purposes.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * A code that classifies the clinical discipline, department or diagnostic service that created the report (e.g. cardiology, biochemistry, hematology, MRI). This is used for searching, sorting and display purposes.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $category
     * @return $this
     */
    public function addCategory($category)
    {
        $this->category[] = $category;
        return $this;
    }

    /**
     * A code or name that describes this diagnostic report.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * A code or name that describes this diagnostic report.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * The subject of the report. Usually, but not always, this is a patient. However, diagnostic services also perform analyses on specimens collected from a variety of other sources.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * The subject of the report. Usually, but not always, this is a patient. However, diagnostic services also perform analyses on specimens collected from a variety of other sources.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * The healthcare event  (e.g. a patient and healthcare provider interaction) which this DiagnosticReport is about.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getEncounter()
    {
        return $this->encounter;
    }

    /**
     * The healthcare event  (e.g. a patient and healthcare provider interaction) which this DiagnosticReport is about.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $encounter
     * @return $this
     */
    public function setEncounter($encounter)
    {
        $this->encounter = $encounter;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getEffectiveDateTime()
    {
        return $this->effectiveDateTime;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $effectiveDateTime
     * @return $this
     */
    public function setEffectiveDateTime($effectiveDateTime)
    {
        $this->effectiveDateTime = $effectiveDateTime;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public function getEffectivePeriod()
    {
        return $this->effectivePeriod;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod $effectivePeriod
     * @return $this
     */
    public function setEffectivePeriod($effectivePeriod)
    {
        $this->effectivePeriod = $effectivePeriod;
        return $this;
    }

    /**
     * The date and time that this version of the report was made available to providers, typically after the report was reviewed and verified.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRInstant
     */
    public function getIssued()
    {
        return $this->issued;
    }

    /**
     * The date and time that this version of the report was made available to providers, typically after the report was reviewed and verified.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRInstant $issued
     * @return $this
     */
    public function setIssued($issued)
    {
        $this->issued = $issued;
        return $this;
    }

    /**
     * The diagnostic service that is responsible for issuing the report.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getPerformer()
    {
        return $this->performer;
    }

    /**
     * The diagnostic service that is responsible for issuing the report.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $performer
     * @return $this
     */
    public function addPerformer($performer)
    {
        $this->performer[] = $performer;
        return $this;
    }

    /**
     * The practitioner or organization that is responsible for the report's conclusions and interpretations.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getResultsInterpreter()
    {
        return $this->resultsInterpreter;
    }

    /**
     * The practitioner or organization that is responsible for the report's conclusions and interpretations.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $resultsInterpreter
     * @return $this
     */
    public function addResultsInterpreter($resultsInterpreter)
    {
        $this->resultsInterpreter[] = $resultsInterpreter;
        return $this;
    }

    /**
     * Details about the specimens on which this diagnostic report is based.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getSpecimen()
    {
        return $this->specimen;
    }

    /**
     * Details about the specimens on which this diagnostic report is based.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $specimen
     * @return $this
     */
    public function addSpecimen($specimen)
    {
        $this->specimen[] = $specimen;
        return $this;
    }

    /**
     * [Observations](observation.html)  that are part of this diagnostic report.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * [Observations](observation.html)  that are part of this diagnostic report.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $result
     * @return $this
     */
    public function addResult($result)
    {
        $this->result[] = $result;
        return $this;
    }

    /**
     * One or more links to full details of any imaging performed during the diagnostic investigation. Typically, this is imaging performed by DICOM enabled modalities, but this is not required. A fully enabled PACS viewer can use this information to provide views of the source images.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getImagingStudy()
    {
        return $this->imagingStudy;
    }

    /**
     * One or more links to full details of any imaging performed during the diagnostic investigation. Typically, this is imaging performed by DICOM enabled modalities, but this is not required. A fully enabled PACS viewer can use this information to provide views of the source images.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $imagingStudy
     * @return $this
     */
    public function addImagingStudy($imagingStudy)
    {
        $this->imagingStudy[] = $imagingStudy;
        return $this;
    }

    /**
     * A list of key images associated with this report. The images are generally created during the diagnostic process, and may be directly of the patient, or of treated specimens (i.e. slides of interest).
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRDiagnosticReport\FHIRDiagnosticReportMedia[]
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * A list of key images associated with this report. The images are generally created during the diagnostic process, and may be directly of the patient, or of treated specimens (i.e. slides of interest).
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRDiagnosticReport\FHIRDiagnosticReportMedia $media
     * @return $this
     */
    public function addMedia($media)
    {
        $this->media[] = $media;
        return $this;
    }

    /**
     * Concise and clinically contextualized summary conclusion (interpretation/impression) of the diagnostic report.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getConclusion()
    {
        return $this->conclusion;
    }

    /**
     * Concise and clinically contextualized summary conclusion (interpretation/impression) of the diagnostic report.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $conclusion
     * @return $this
     */
    public function setConclusion($conclusion)
    {
        $this->conclusion = $conclusion;
        return $this;
    }

    /**
     * One or more codes that represent the summary conclusion (interpretation/impression) of the diagnostic report.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getConclusionCode()
    {
        return $this->conclusionCode;
    }

    /**
     * One or more codes that represent the summary conclusion (interpretation/impression) of the diagnostic report.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $conclusionCode
     * @return $this
     */
    public function addConclusionCode($conclusionCode)
    {
        $this->conclusionCode[] = $conclusionCode;
        return $this;
    }

    /**
     * Rich text representation of the entire result as issued by the diagnostic service. Multiple formats are allowed but they SHALL be semantically equivalent.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRAttachment[]
     */
    public function getPresentedForm()
    {
        return $this->presentedForm;
    }

    /**
     * Rich text representation of the entire result as issued by the diagnostic service. Multiple formats are allowed but they SHALL be semantically equivalent.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRAttachment $presentedForm
     * @return $this
     */
    public function addPresentedForm($presentedForm)
    {
        $this->presentedForm[] = $presentedForm;
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
            if (isset($data['basedOn'])) {
                if (is_array($data['basedOn'])) {
                    foreach ($data['basedOn'] as $d) {
                        $this->addBasedOn($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"basedOn" must be array of objects or null, ' . gettype($data['basedOn']) . ' seen.');
                }
            }
            if (isset($data['status'])) {
                $this->setStatus($data['status']);
            }
            if (isset($data['category'])) {
                if (is_array($data['category'])) {
                    foreach ($data['category'] as $d) {
                        $this->addCategory($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"category" must be array of objects or null, ' . gettype($data['category']) . ' seen.');
                }
            }
            if (isset($data['code'])) {
                $this->setCode($data['code']);
            }
            if (isset($data['subject'])) {
                $this->setSubject($data['subject']);
            }
            if (isset($data['encounter'])) {
                $this->setEncounter($data['encounter']);
            }
            if (isset($data['effectiveDateTime'])) {
                $this->setEffectiveDateTime($data['effectiveDateTime']);
            }
            if (isset($data['effectivePeriod'])) {
                $this->setEffectivePeriod($data['effectivePeriod']);
            }
            if (isset($data['issued'])) {
                $this->setIssued($data['issued']);
            }
            if (isset($data['performer'])) {
                if (is_array($data['performer'])) {
                    foreach ($data['performer'] as $d) {
                        $this->addPerformer($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"performer" must be array of objects or null, ' . gettype($data['performer']) . ' seen.');
                }
            }
            if (isset($data['resultsInterpreter'])) {
                if (is_array($data['resultsInterpreter'])) {
                    foreach ($data['resultsInterpreter'] as $d) {
                        $this->addResultsInterpreter($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"resultsInterpreter" must be array of objects or null, ' . gettype($data['resultsInterpreter']) . ' seen.');
                }
            }
            if (isset($data['specimen'])) {
                if (is_array($data['specimen'])) {
                    foreach ($data['specimen'] as $d) {
                        $this->addSpecimen($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"specimen" must be array of objects or null, ' . gettype($data['specimen']) . ' seen.');
                }
            }
            if (isset($data['result'])) {
                if (is_array($data['result'])) {
                    foreach ($data['result'] as $d) {
                        $this->addResult($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"result" must be array of objects or null, ' . gettype($data['result']) . ' seen.');
                }
            }
            if (isset($data['imagingStudy'])) {
                if (is_array($data['imagingStudy'])) {
                    foreach ($data['imagingStudy'] as $d) {
                        $this->addImagingStudy($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"imagingStudy" must be array of objects or null, ' . gettype($data['imagingStudy']) . ' seen.');
                }
            }
            if (isset($data['media'])) {
                if (is_array($data['media'])) {
                    foreach ($data['media'] as $d) {
                        $this->addMedia($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"media" must be array of objects or null, ' . gettype($data['media']) . ' seen.');
                }
            }
            if (isset($data['conclusion'])) {
                $this->setConclusion($data['conclusion']);
            }
            if (isset($data['conclusionCode'])) {
                if (is_array($data['conclusionCode'])) {
                    foreach ($data['conclusionCode'] as $d) {
                        $this->addConclusionCode($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"conclusionCode" must be array of objects or null, ' . gettype($data['conclusionCode']) . ' seen.');
                }
            }
            if (isset($data['presentedForm'])) {
                if (is_array($data['presentedForm'])) {
                    foreach ($data['presentedForm'] as $d) {
                        $this->addPresentedForm($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"presentedForm" must be array of objects or null, ' . gettype($data['presentedForm']) . ' seen.');
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
        if (0 < count($this->identifier)) {
            $json['identifier'] = [];
            foreach ($this->identifier as $identifier) {
                $json['identifier'][] = $identifier;
            }
        }
        if (0 < count($this->basedOn)) {
            $json['basedOn'] = [];
            foreach ($this->basedOn as $basedOn) {
                $json['basedOn'][] = $basedOn;
            }
        }
        if (isset($this->status)) {
            $json['status'] = $this->status;
        }
        if (0 < count($this->category)) {
            $json['category'] = [];
            foreach ($this->category as $category) {
                $json['category'][] = $category;
            }
        }
        if (isset($this->code)) {
            $json['code'] = $this->code;
        }
        if (isset($this->subject)) {
            $json['subject'] = $this->subject;
        }
        if (isset($this->encounter)) {
            $json['encounter'] = $this->encounter;
        }
        if (isset($this->effectiveDateTime)) {
            $json['effectiveDateTime'] = $this->effectiveDateTime;
        }
        if (isset($this->effectivePeriod)) {
            $json['effectivePeriod'] = $this->effectivePeriod;
        }
        if (isset($this->issued)) {
            $json['issued'] = $this->issued;
        }
        if (0 < count($this->performer)) {
            $json['performer'] = [];
            foreach ($this->performer as $performer) {
                $json['performer'][] = $performer;
            }
        }
        if (0 < count($this->resultsInterpreter)) {
            $json['resultsInterpreter'] = [];
            foreach ($this->resultsInterpreter as $resultsInterpreter) {
                $json['resultsInterpreter'][] = $resultsInterpreter;
            }
        }
        if (0 < count($this->specimen)) {
            $json['specimen'] = [];
            foreach ($this->specimen as $specimen) {
                $json['specimen'][] = $specimen;
            }
        }
        if (0 < count($this->result)) {
            $json['result'] = [];
            foreach ($this->result as $result) {
                $json['result'][] = $result;
            }
        }
        if (0 < count($this->imagingStudy)) {
            $json['imagingStudy'] = [];
            foreach ($this->imagingStudy as $imagingStudy) {
                $json['imagingStudy'][] = $imagingStudy;
            }
        }
        if (0 < count($this->media)) {
            $json['media'] = [];
            foreach ($this->media as $media) {
                $json['media'][] = $media;
            }
        }
        if (isset($this->conclusion)) {
            $json['conclusion'] = $this->conclusion;
        }
        if (0 < count($this->conclusionCode)) {
            $json['conclusionCode'] = [];
            foreach ($this->conclusionCode as $conclusionCode) {
                $json['conclusionCode'][] = $conclusionCode;
            }
        }
        if (0 < count($this->presentedForm)) {
            $json['presentedForm'] = [];
            foreach ($this->presentedForm as $presentedForm) {
                $json['presentedForm'][] = $presentedForm;
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
            $sxe = new \SimpleXMLElement('<DiagnosticReport xmlns="http://hl7.org/fhir"></DiagnosticReport>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->identifier)) {
            foreach ($this->identifier as $identifier) {
                $identifier->xmlSerialize(true, $sxe->addChild('identifier'));
            }
        }
        if (0 < count($this->basedOn)) {
            foreach ($this->basedOn as $basedOn) {
                $basedOn->xmlSerialize(true, $sxe->addChild('basedOn'));
            }
        }
        if (isset($this->status)) {
            $this->status->xmlSerialize(true, $sxe->addChild('status'));
        }
        if (0 < count($this->category)) {
            foreach ($this->category as $category) {
                $category->xmlSerialize(true, $sxe->addChild('category'));
            }
        }
        if (isset($this->code)) {
            $this->code->xmlSerialize(true, $sxe->addChild('code'));
        }
        if (isset($this->subject)) {
            $this->subject->xmlSerialize(true, $sxe->addChild('subject'));
        }
        if (isset($this->encounter)) {
            $this->encounter->xmlSerialize(true, $sxe->addChild('encounter'));
        }
        if (isset($this->effectiveDateTime)) {
            $this->effectiveDateTime->xmlSerialize(true, $sxe->addChild('effectiveDateTime'));
        }
        if (isset($this->effectivePeriod)) {
            $this->effectivePeriod->xmlSerialize(true, $sxe->addChild('effectivePeriod'));
        }
        if (isset($this->issued)) {
            $this->issued->xmlSerialize(true, $sxe->addChild('issued'));
        }
        if (0 < count($this->performer)) {
            foreach ($this->performer as $performer) {
                $performer->xmlSerialize(true, $sxe->addChild('performer'));
            }
        }
        if (0 < count($this->resultsInterpreter)) {
            foreach ($this->resultsInterpreter as $resultsInterpreter) {
                $resultsInterpreter->xmlSerialize(true, $sxe->addChild('resultsInterpreter'));
            }
        }
        if (0 < count($this->specimen)) {
            foreach ($this->specimen as $specimen) {
                $specimen->xmlSerialize(true, $sxe->addChild('specimen'));
            }
        }
        if (0 < count($this->result)) {
            foreach ($this->result as $result) {
                $result->xmlSerialize(true, $sxe->addChild('result'));
            }
        }
        if (0 < count($this->imagingStudy)) {
            foreach ($this->imagingStudy as $imagingStudy) {
                $imagingStudy->xmlSerialize(true, $sxe->addChild('imagingStudy'));
            }
        }
        if (0 < count($this->media)) {
            foreach ($this->media as $media) {
                $media->xmlSerialize(true, $sxe->addChild('media'));
            }
        }
        if (isset($this->conclusion)) {
            $this->conclusion->xmlSerialize(true, $sxe->addChild('conclusion'));
        }
        if (0 < count($this->conclusionCode)) {
            foreach ($this->conclusionCode as $conclusionCode) {
                $conclusionCode->xmlSerialize(true, $sxe->addChild('conclusionCode'));
            }
        }
        if (0 < count($this->presentedForm)) {
            foreach ($this->presentedForm as $presentedForm) {
                $presentedForm->xmlSerialize(true, $sxe->addChild('presentedForm'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
