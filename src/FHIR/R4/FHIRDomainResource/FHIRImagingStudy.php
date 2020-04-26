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
 * Representation of the content produced in a DICOM imaging study. A study comprises a set of series, each of which includes a set of Service-Object Pair Instances (SOP Instances - images or other data) acquired or produced in a common context.  A series is of only one modality (e.g. X-ray, CT, MR, ultrasound), but a study may have multiple series of different modalities.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRImagingStudy extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * Identifiers for the ImagingStudy such as DICOM Study Instance UID, and Accession Number.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * The current state of the ImagingStudy.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRImagingStudyStatus
     */
    public $status = null;

    /**
     * A list of all the series.modality values that are actual acquisition modalities, i.e. those in the DICOM Context Group 29 (value set OID 1.2.840.10008.6.1.19).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding[]
     */
    public $modality = [];

    /**
     * The subject, typically a patient, of the imaging study.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $subject = null;

    /**
     * The healthcare event (e.g. a patient and healthcare provider interaction) during which this ImagingStudy is made.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $encounter = null;

    /**
     * Date and time the study started.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $started = null;

    /**
     * A list of the diagnostic requests that resulted in this imaging study being performed.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $basedOn = [];

    /**
     * The requesting/referring physician.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $referrer = null;

    /**
     * Who read the study and interpreted the images or other content.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $interpreter = [];

    /**
     * The network service providing access (e.g., query, view, or retrieval) for the study. See implementation notes for information about using DICOM endpoints. A study-level endpoint applies to each series in the study, unless overridden by a series-level endpoint with the same Endpoint.connectionType.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $endpoint = [];

    /**
     * Number of Series in the Study. This value given may be larger than the number of series elements this Resource contains due to resource availability, security, or other factors. This element should be present if any series elements are present.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt
     */
    public $numberOfSeries = null;

    /**
     * Number of SOP Instances in Study. This value given may be larger than the number of instance elements this resource contains due to resource availability, security, or other factors. This element should be present if any instance elements are present.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt
     */
    public $numberOfInstances = null;

    /**
     * The procedure which this ImagingStudy was part of.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $procedureReference = null;

    /**
     * The code for the performed procedure type.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $procedureCode = [];

    /**
     * The principal physical location where the ImagingStudy was performed.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $location = null;

    /**
     * Description of clinical condition indicating why the ImagingStudy was requested.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $reasonCode = [];

    /**
     * Indicates another resource whose existence justifies this Study.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $reasonReference = [];

    /**
     * Per the recommended DICOM mapping, this element is derived from the Study Description attribute (0008,1030). Observations or findings about the imaging study should be recorded in another resource, e.g. Observation, and not in this element.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation[]
     */
    public $note = [];

    /**
     * The Imaging Manager description of the study. Institution-generated description or classification of the Study (component) performed.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $description = null;

    /**
     * Each study has one or more series of images or other content.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRImagingStudy\FHIRImagingStudySeries[]
     */
    public $series = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'ImagingStudy';

    /**
     * Identifiers for the ImagingStudy such as DICOM Study Instance UID, and Accession Number.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Identifiers for the ImagingStudy such as DICOM Study Instance UID, and Accession Number.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * The current state of the ImagingStudy.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRImagingStudyStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * The current state of the ImagingStudy.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRImagingStudyStatus $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * A list of all the series.modality values that are actual acquisition modalities, i.e. those in the DICOM Context Group 29 (value set OID 1.2.840.10008.6.1.19).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding[]
     */
    public function getModality()
    {
        return $this->modality;
    }

    /**
     * A list of all the series.modality values that are actual acquisition modalities, i.e. those in the DICOM Context Group 29 (value set OID 1.2.840.10008.6.1.19).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding $modality
     * @return $this
     */
    public function addModality($modality)
    {
        $this->modality[] = $modality;
        return $this;
    }

    /**
     * The subject, typically a patient, of the imaging study.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * The subject, typically a patient, of the imaging study.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * The healthcare event (e.g. a patient and healthcare provider interaction) during which this ImagingStudy is made.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getEncounter()
    {
        return $this->encounter;
    }

    /**
     * The healthcare event (e.g. a patient and healthcare provider interaction) during which this ImagingStudy is made.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $encounter
     * @return $this
     */
    public function setEncounter($encounter)
    {
        $this->encounter = $encounter;
        return $this;
    }

    /**
     * Date and time the study started.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getStarted()
    {
        return $this->started;
    }

    /**
     * Date and time the study started.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $started
     * @return $this
     */
    public function setStarted($started)
    {
        $this->started = $started;
        return $this;
    }

    /**
     * A list of the diagnostic requests that resulted in this imaging study being performed.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getBasedOn()
    {
        return $this->basedOn;
    }

    /**
     * A list of the diagnostic requests that resulted in this imaging study being performed.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $basedOn
     * @return $this
     */
    public function addBasedOn($basedOn)
    {
        $this->basedOn[] = $basedOn;
        return $this;
    }

    /**
     * The requesting/referring physician.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getReferrer()
    {
        return $this->referrer;
    }

    /**
     * The requesting/referring physician.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $referrer
     * @return $this
     */
    public function setReferrer($referrer)
    {
        $this->referrer = $referrer;
        return $this;
    }

    /**
     * Who read the study and interpreted the images or other content.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getInterpreter()
    {
        return $this->interpreter;
    }

    /**
     * Who read the study and interpreted the images or other content.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $interpreter
     * @return $this
     */
    public function addInterpreter($interpreter)
    {
        $this->interpreter[] = $interpreter;
        return $this;
    }

    /**
     * The network service providing access (e.g., query, view, or retrieval) for the study. See implementation notes for information about using DICOM endpoints. A study-level endpoint applies to each series in the study, unless overridden by a series-level endpoint with the same Endpoint.connectionType.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * The network service providing access (e.g., query, view, or retrieval) for the study. See implementation notes for information about using DICOM endpoints. A study-level endpoint applies to each series in the study, unless overridden by a series-level endpoint with the same Endpoint.connectionType.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $endpoint
     * @return $this
     */
    public function addEndpoint($endpoint)
    {
        $this->endpoint[] = $endpoint;
        return $this;
    }

    /**
     * Number of Series in the Study. This value given may be larger than the number of series elements this Resource contains due to resource availability, security, or other factors. This element should be present if any series elements are present.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt
     */
    public function getNumberOfSeries()
    {
        return $this->numberOfSeries;
    }

    /**
     * Number of Series in the Study. This value given may be larger than the number of series elements this Resource contains due to resource availability, security, or other factors. This element should be present if any series elements are present.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt $numberOfSeries
     * @return $this
     */
    public function setNumberOfSeries($numberOfSeries)
    {
        $this->numberOfSeries = $numberOfSeries;
        return $this;
    }

    /**
     * Number of SOP Instances in Study. This value given may be larger than the number of instance elements this resource contains due to resource availability, security, or other factors. This element should be present if any instance elements are present.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt
     */
    public function getNumberOfInstances()
    {
        return $this->numberOfInstances;
    }

    /**
     * Number of SOP Instances in Study. This value given may be larger than the number of instance elements this resource contains due to resource availability, security, or other factors. This element should be present if any instance elements are present.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt $numberOfInstances
     * @return $this
     */
    public function setNumberOfInstances($numberOfInstances)
    {
        $this->numberOfInstances = $numberOfInstances;
        return $this;
    }

    /**
     * The procedure which this ImagingStudy was part of.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getProcedureReference()
    {
        return $this->procedureReference;
    }

    /**
     * The procedure which this ImagingStudy was part of.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $procedureReference
     * @return $this
     */
    public function setProcedureReference($procedureReference)
    {
        $this->procedureReference = $procedureReference;
        return $this;
    }

    /**
     * The code for the performed procedure type.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getProcedureCode()
    {
        return $this->procedureCode;
    }

    /**
     * The code for the performed procedure type.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $procedureCode
     * @return $this
     */
    public function addProcedureCode($procedureCode)
    {
        $this->procedureCode[] = $procedureCode;
        return $this;
    }

    /**
     * The principal physical location where the ImagingStudy was performed.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * The principal physical location where the ImagingStudy was performed.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $location
     * @return $this
     */
    public function setLocation($location)
    {
        $this->location = $location;
        return $this;
    }

    /**
     * Description of clinical condition indicating why the ImagingStudy was requested.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getReasonCode()
    {
        return $this->reasonCode;
    }

    /**
     * Description of clinical condition indicating why the ImagingStudy was requested.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $reasonCode
     * @return $this
     */
    public function addReasonCode($reasonCode)
    {
        $this->reasonCode[] = $reasonCode;
        return $this;
    }

    /**
     * Indicates another resource whose existence justifies this Study.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getReasonReference()
    {
        return $this->reasonReference;
    }

    /**
     * Indicates another resource whose existence justifies this Study.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $reasonReference
     * @return $this
     */
    public function addReasonReference($reasonReference)
    {
        $this->reasonReference[] = $reasonReference;
        return $this;
    }

    /**
     * Per the recommended DICOM mapping, this element is derived from the Study Description attribute (0008,1030). Observations or findings about the imaging study should be recorded in another resource, e.g. Observation, and not in this element.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation[]
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Per the recommended DICOM mapping, this element is derived from the Study Description attribute (0008,1030). Observations or findings about the imaging study should be recorded in another resource, e.g. Observation, and not in this element.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation $note
     * @return $this
     */
    public function addNote($note)
    {
        $this->note[] = $note;
        return $this;
    }

    /**
     * The Imaging Manager description of the study. Institution-generated description or classification of the Study (component) performed.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * The Imaging Manager description of the study. Institution-generated description or classification of the Study (component) performed.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Each study has one or more series of images or other content.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRImagingStudy\FHIRImagingStudySeries[]
     */
    public function getSeries()
    {
        return $this->series;
    }

    /**
     * Each study has one or more series of images or other content.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRImagingStudy\FHIRImagingStudySeries $series
     * @return $this
     */
    public function addSeries($series)
    {
        $this->series[] = $series;
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
            if (isset($data['modality'])) {
                if (is_array($data['modality'])) {
                    foreach ($data['modality'] as $d) {
                        $this->addModality($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"modality" must be array of objects or null, ' . gettype($data['modality']) . ' seen.');
                }
            }
            if (isset($data['subject'])) {
                $this->setSubject($data['subject']);
            }
            if (isset($data['encounter'])) {
                $this->setEncounter($data['encounter']);
            }
            if (isset($data['started'])) {
                $this->setStarted($data['started']);
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
            if (isset($data['referrer'])) {
                $this->setReferrer($data['referrer']);
            }
            if (isset($data['interpreter'])) {
                if (is_array($data['interpreter'])) {
                    foreach ($data['interpreter'] as $d) {
                        $this->addInterpreter($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"interpreter" must be array of objects or null, ' . gettype($data['interpreter']) . ' seen.');
                }
            }
            if (isset($data['endpoint'])) {
                if (is_array($data['endpoint'])) {
                    foreach ($data['endpoint'] as $d) {
                        $this->addEndpoint($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"endpoint" must be array of objects or null, ' . gettype($data['endpoint']) . ' seen.');
                }
            }
            if (isset($data['numberOfSeries'])) {
                $this->setNumberOfSeries($data['numberOfSeries']);
            }
            if (isset($data['numberOfInstances'])) {
                $this->setNumberOfInstances($data['numberOfInstances']);
            }
            if (isset($data['procedureReference'])) {
                $this->setProcedureReference($data['procedureReference']);
            }
            if (isset($data['procedureCode'])) {
                if (is_array($data['procedureCode'])) {
                    foreach ($data['procedureCode'] as $d) {
                        $this->addProcedureCode($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"procedureCode" must be array of objects or null, ' . gettype($data['procedureCode']) . ' seen.');
                }
            }
            if (isset($data['location'])) {
                $this->setLocation($data['location']);
            }
            if (isset($data['reasonCode'])) {
                if (is_array($data['reasonCode'])) {
                    foreach ($data['reasonCode'] as $d) {
                        $this->addReasonCode($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"reasonCode" must be array of objects or null, ' . gettype($data['reasonCode']) . ' seen.');
                }
            }
            if (isset($data['reasonReference'])) {
                if (is_array($data['reasonReference'])) {
                    foreach ($data['reasonReference'] as $d) {
                        $this->addReasonReference($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"reasonReference" must be array of objects or null, ' . gettype($data['reasonReference']) . ' seen.');
                }
            }
            if (isset($data['note'])) {
                if (is_array($data['note'])) {
                    foreach ($data['note'] as $d) {
                        $this->addNote($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"note" must be array of objects or null, ' . gettype($data['note']) . ' seen.');
                }
            }
            if (isset($data['description'])) {
                $this->setDescription($data['description']);
            }
            if (isset($data['series'])) {
                if (is_array($data['series'])) {
                    foreach ($data['series'] as $d) {
                        $this->addSeries($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"series" must be array of objects or null, ' . gettype($data['series']) . ' seen.');
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
        if (isset($this->status)) {
            $json['status'] = $this->status;
        }
        if (0 < count($this->modality)) {
            $json['modality'] = [];
            foreach ($this->modality as $modality) {
                $json['modality'][] = $modality;
            }
        }
        if (isset($this->subject)) {
            $json['subject'] = $this->subject;
        }
        if (isset($this->encounter)) {
            $json['encounter'] = $this->encounter;
        }
        if (isset($this->started)) {
            $json['started'] = $this->started;
        }
        if (0 < count($this->basedOn)) {
            $json['basedOn'] = [];
            foreach ($this->basedOn as $basedOn) {
                $json['basedOn'][] = $basedOn;
            }
        }
        if (isset($this->referrer)) {
            $json['referrer'] = $this->referrer;
        }
        if (0 < count($this->interpreter)) {
            $json['interpreter'] = [];
            foreach ($this->interpreter as $interpreter) {
                $json['interpreter'][] = $interpreter;
            }
        }
        if (0 < count($this->endpoint)) {
            $json['endpoint'] = [];
            foreach ($this->endpoint as $endpoint) {
                $json['endpoint'][] = $endpoint;
            }
        }
        if (isset($this->numberOfSeries)) {
            $json['numberOfSeries'] = $this->numberOfSeries;
        }
        if (isset($this->numberOfInstances)) {
            $json['numberOfInstances'] = $this->numberOfInstances;
        }
        if (isset($this->procedureReference)) {
            $json['procedureReference'] = $this->procedureReference;
        }
        if (0 < count($this->procedureCode)) {
            $json['procedureCode'] = [];
            foreach ($this->procedureCode as $procedureCode) {
                $json['procedureCode'][] = $procedureCode;
            }
        }
        if (isset($this->location)) {
            $json['location'] = $this->location;
        }
        if (0 < count($this->reasonCode)) {
            $json['reasonCode'] = [];
            foreach ($this->reasonCode as $reasonCode) {
                $json['reasonCode'][] = $reasonCode;
            }
        }
        if (0 < count($this->reasonReference)) {
            $json['reasonReference'] = [];
            foreach ($this->reasonReference as $reasonReference) {
                $json['reasonReference'][] = $reasonReference;
            }
        }
        if (0 < count($this->note)) {
            $json['note'] = [];
            foreach ($this->note as $note) {
                $json['note'][] = $note;
            }
        }
        if (isset($this->description)) {
            $json['description'] = $this->description;
        }
        if (0 < count($this->series)) {
            $json['series'] = [];
            foreach ($this->series as $series) {
                $json['series'][] = $series;
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
            $sxe = new \SimpleXMLElement('<ImagingStudy xmlns="http://hl7.org/fhir"></ImagingStudy>');
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
        if (0 < count($this->modality)) {
            foreach ($this->modality as $modality) {
                $modality->xmlSerialize(true, $sxe->addChild('modality'));
            }
        }
        if (isset($this->subject)) {
            $this->subject->xmlSerialize(true, $sxe->addChild('subject'));
        }
        if (isset($this->encounter)) {
            $this->encounter->xmlSerialize(true, $sxe->addChild('encounter'));
        }
        if (isset($this->started)) {
            $this->started->xmlSerialize(true, $sxe->addChild('started'));
        }
        if (0 < count($this->basedOn)) {
            foreach ($this->basedOn as $basedOn) {
                $basedOn->xmlSerialize(true, $sxe->addChild('basedOn'));
            }
        }
        if (isset($this->referrer)) {
            $this->referrer->xmlSerialize(true, $sxe->addChild('referrer'));
        }
        if (0 < count($this->interpreter)) {
            foreach ($this->interpreter as $interpreter) {
                $interpreter->xmlSerialize(true, $sxe->addChild('interpreter'));
            }
        }
        if (0 < count($this->endpoint)) {
            foreach ($this->endpoint as $endpoint) {
                $endpoint->xmlSerialize(true, $sxe->addChild('endpoint'));
            }
        }
        if (isset($this->numberOfSeries)) {
            $this->numberOfSeries->xmlSerialize(true, $sxe->addChild('numberOfSeries'));
        }
        if (isset($this->numberOfInstances)) {
            $this->numberOfInstances->xmlSerialize(true, $sxe->addChild('numberOfInstances'));
        }
        if (isset($this->procedureReference)) {
            $this->procedureReference->xmlSerialize(true, $sxe->addChild('procedureReference'));
        }
        if (0 < count($this->procedureCode)) {
            foreach ($this->procedureCode as $procedureCode) {
                $procedureCode->xmlSerialize(true, $sxe->addChild('procedureCode'));
            }
        }
        if (isset($this->location)) {
            $this->location->xmlSerialize(true, $sxe->addChild('location'));
        }
        if (0 < count($this->reasonCode)) {
            foreach ($this->reasonCode as $reasonCode) {
                $reasonCode->xmlSerialize(true, $sxe->addChild('reasonCode'));
            }
        }
        if (0 < count($this->reasonReference)) {
            foreach ($this->reasonReference as $reasonReference) {
                $reasonReference->xmlSerialize(true, $sxe->addChild('reasonReference'));
            }
        }
        if (0 < count($this->note)) {
            foreach ($this->note as $note) {
                $note->xmlSerialize(true, $sxe->addChild('note'));
            }
        }
        if (isset($this->description)) {
            $this->description->xmlSerialize(true, $sxe->addChild('description'));
        }
        if (0 < count($this->series)) {
            foreach ($this->series as $series) {
                $series->xmlSerialize(true, $sxe->addChild('series'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
