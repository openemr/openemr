<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRImagingStudy;

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
 * Representation of the content produced in a DICOM imaging study. A study comprises a set of series, each of which includes a set of Service-Object Pair Instances (SOP Instances - images or other data) acquired or produced in a common context.  A series is of only one modality (e.g. X-ray, CT, MR, ultrasound), but a study may have multiple series of different modalities.
 */
class FHIRImagingStudySeries extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The DICOM Series Instance UID for the series.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public $uid = null;

    /**
     * The numeric identifier of this series in the study.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt
     */
    public $number = null;

    /**
     * The modality of this series sequence.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding
     */
    public $modality = null;

    /**
     * A description of the series.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $description = null;

    /**
     * Number of SOP Instances in the Study. The value given may be larger than the number of instance elements this resource contains due to resource availability, security, or other factors. This element should be present if any instance elements are present.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt
     */
    public $numberOfInstances = null;

    /**
     * The network service providing access (e.g., query, view, or retrieval) for this series. See implementation notes for information about using DICOM endpoints. A series-level endpoint, if present, has precedence over a study-level endpoint with the same Endpoint.connectionType.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $endpoint = [];

    /**
     * The anatomic structures examined. See DICOM Part 16 Annex L (http://dicom.nema.org/medical/dicom/current/output/chtml/part16/chapter_L.html) for DICOM to SNOMED-CT mappings. The bodySite may indicate the laterality of body part imaged; if so, it shall be consistent with any content of ImagingStudy.series.laterality.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding
     */
    public $bodySite = null;

    /**
     * The laterality of the (possibly paired) anatomic structures examined. E.g., the left knee, both lungs, or unpaired abdomen. If present, shall be consistent with any laterality information indicated in ImagingStudy.series.bodySite.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding
     */
    public $laterality = null;

    /**
     * The specimen imaged, e.g., for whole slide imaging of a biopsy.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $specimen = [];

    /**
     * The date and time the series was started.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $started = null;

    /**
     * Indicates who or what performed the series and how they were involved.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRImagingStudy\FHIRImagingStudyPerformer[]
     */
    public $performer = [];

    /**
     * A single SOP instance within the series, e.g. an image, or presentation state.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRImagingStudy\FHIRImagingStudyInstance[]
     */
    public $instance = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'ImagingStudy.Series';

    /**
     * The DICOM Series Instance UID for the series.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * The DICOM Series Instance UID for the series.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRId $uid
     * @return $this
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
        return $this;
    }

    /**
     * The numeric identifier of this series in the study.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * The numeric identifier of this series in the study.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt $number
     * @return $this
     */
    public function setNumber($number)
    {
        $this->number = $number;
        return $this;
    }

    /**
     * The modality of this series sequence.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding
     */
    public function getModality()
    {
        return $this->modality;
    }

    /**
     * The modality of this series sequence.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding $modality
     * @return $this
     */
    public function setModality($modality)
    {
        $this->modality = $modality;
        return $this;
    }

    /**
     * A description of the series.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * A description of the series.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Number of SOP Instances in the Study. The value given may be larger than the number of instance elements this resource contains due to resource availability, security, or other factors. This element should be present if any instance elements are present.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt
     */
    public function getNumberOfInstances()
    {
        return $this->numberOfInstances;
    }

    /**
     * Number of SOP Instances in the Study. The value given may be larger than the number of instance elements this resource contains due to resource availability, security, or other factors. This element should be present if any instance elements are present.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt $numberOfInstances
     * @return $this
     */
    public function setNumberOfInstances($numberOfInstances)
    {
        $this->numberOfInstances = $numberOfInstances;
        return $this;
    }

    /**
     * The network service providing access (e.g., query, view, or retrieval) for this series. See implementation notes for information about using DICOM endpoints. A series-level endpoint, if present, has precedence over a study-level endpoint with the same Endpoint.connectionType.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * The network service providing access (e.g., query, view, or retrieval) for this series. See implementation notes for information about using DICOM endpoints. A series-level endpoint, if present, has precedence over a study-level endpoint with the same Endpoint.connectionType.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $endpoint
     * @return $this
     */
    public function addEndpoint($endpoint)
    {
        $this->endpoint[] = $endpoint;
        return $this;
    }

    /**
     * The anatomic structures examined. See DICOM Part 16 Annex L (http://dicom.nema.org/medical/dicom/current/output/chtml/part16/chapter_L.html) for DICOM to SNOMED-CT mappings. The bodySite may indicate the laterality of body part imaged; if so, it shall be consistent with any content of ImagingStudy.series.laterality.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding
     */
    public function getBodySite()
    {
        return $this->bodySite;
    }

    /**
     * The anatomic structures examined. See DICOM Part 16 Annex L (http://dicom.nema.org/medical/dicom/current/output/chtml/part16/chapter_L.html) for DICOM to SNOMED-CT mappings. The bodySite may indicate the laterality of body part imaged; if so, it shall be consistent with any content of ImagingStudy.series.laterality.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding $bodySite
     * @return $this
     */
    public function setBodySite($bodySite)
    {
        $this->bodySite = $bodySite;
        return $this;
    }

    /**
     * The laterality of the (possibly paired) anatomic structures examined. E.g., the left knee, both lungs, or unpaired abdomen. If present, shall be consistent with any laterality information indicated in ImagingStudy.series.bodySite.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding
     */
    public function getLaterality()
    {
        return $this->laterality;
    }

    /**
     * The laterality of the (possibly paired) anatomic structures examined. E.g., the left knee, both lungs, or unpaired abdomen. If present, shall be consistent with any laterality information indicated in ImagingStudy.series.bodySite.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding $laterality
     * @return $this
     */
    public function setLaterality($laterality)
    {
        $this->laterality = $laterality;
        return $this;
    }

    /**
     * The specimen imaged, e.g., for whole slide imaging of a biopsy.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getSpecimen()
    {
        return $this->specimen;
    }

    /**
     * The specimen imaged, e.g., for whole slide imaging of a biopsy.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $specimen
     * @return $this
     */
    public function addSpecimen($specimen)
    {
        $this->specimen[] = $specimen;
        return $this;
    }

    /**
     * The date and time the series was started.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getStarted()
    {
        return $this->started;
    }

    /**
     * The date and time the series was started.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $started
     * @return $this
     */
    public function setStarted($started)
    {
        $this->started = $started;
        return $this;
    }

    /**
     * Indicates who or what performed the series and how they were involved.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRImagingStudy\FHIRImagingStudyPerformer[]
     */
    public function getPerformer()
    {
        return $this->performer;
    }

    /**
     * Indicates who or what performed the series and how they were involved.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRImagingStudy\FHIRImagingStudyPerformer $performer
     * @return $this
     */
    public function addPerformer($performer)
    {
        $this->performer[] = $performer;
        return $this;
    }

    /**
     * A single SOP instance within the series, e.g. an image, or presentation state.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRImagingStudy\FHIRImagingStudyInstance[]
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * A single SOP instance within the series, e.g. an image, or presentation state.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRImagingStudy\FHIRImagingStudyInstance $instance
     * @return $this
     */
    public function addInstance($instance)
    {
        $this->instance[] = $instance;
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
            if (isset($data['uid'])) {
                $this->setUid($data['uid']);
            }
            if (isset($data['number'])) {
                $this->setNumber($data['number']);
            }
            if (isset($data['modality'])) {
                $this->setModality($data['modality']);
            }
            if (isset($data['description'])) {
                $this->setDescription($data['description']);
            }
            if (isset($data['numberOfInstances'])) {
                $this->setNumberOfInstances($data['numberOfInstances']);
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
            if (isset($data['bodySite'])) {
                $this->setBodySite($data['bodySite']);
            }
            if (isset($data['laterality'])) {
                $this->setLaterality($data['laterality']);
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
            if (isset($data['started'])) {
                $this->setStarted($data['started']);
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
            if (isset($data['instance'])) {
                if (is_array($data['instance'])) {
                    foreach ($data['instance'] as $d) {
                        $this->addInstance($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"instance" must be array of objects or null, ' . gettype($data['instance']) . ' seen.');
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
        if (isset($this->uid)) {
            $json['uid'] = $this->uid;
        }
        if (isset($this->number)) {
            $json['number'] = $this->number;
        }
        if (isset($this->modality)) {
            $json['modality'] = $this->modality;
        }
        if (isset($this->description)) {
            $json['description'] = $this->description;
        }
        if (isset($this->numberOfInstances)) {
            $json['numberOfInstances'] = $this->numberOfInstances;
        }
        if (0 < count($this->endpoint)) {
            $json['endpoint'] = [];
            foreach ($this->endpoint as $endpoint) {
                $json['endpoint'][] = $endpoint;
            }
        }
        if (isset($this->bodySite)) {
            $json['bodySite'] = $this->bodySite;
        }
        if (isset($this->laterality)) {
            $json['laterality'] = $this->laterality;
        }
        if (0 < count($this->specimen)) {
            $json['specimen'] = [];
            foreach ($this->specimen as $specimen) {
                $json['specimen'][] = $specimen;
            }
        }
        if (isset($this->started)) {
            $json['started'] = $this->started;
        }
        if (0 < count($this->performer)) {
            $json['performer'] = [];
            foreach ($this->performer as $performer) {
                $json['performer'][] = $performer;
            }
        }
        if (0 < count($this->instance)) {
            $json['instance'] = [];
            foreach ($this->instance as $instance) {
                $json['instance'][] = $instance;
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
            $sxe = new \SimpleXMLElement('<ImagingStudySeries xmlns="http://hl7.org/fhir"></ImagingStudySeries>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->uid)) {
            $this->uid->xmlSerialize(true, $sxe->addChild('uid'));
        }
        if (isset($this->number)) {
            $this->number->xmlSerialize(true, $sxe->addChild('number'));
        }
        if (isset($this->modality)) {
            $this->modality->xmlSerialize(true, $sxe->addChild('modality'));
        }
        if (isset($this->description)) {
            $this->description->xmlSerialize(true, $sxe->addChild('description'));
        }
        if (isset($this->numberOfInstances)) {
            $this->numberOfInstances->xmlSerialize(true, $sxe->addChild('numberOfInstances'));
        }
        if (0 < count($this->endpoint)) {
            foreach ($this->endpoint as $endpoint) {
                $endpoint->xmlSerialize(true, $sxe->addChild('endpoint'));
            }
        }
        if (isset($this->bodySite)) {
            $this->bodySite->xmlSerialize(true, $sxe->addChild('bodySite'));
        }
        if (isset($this->laterality)) {
            $this->laterality->xmlSerialize(true, $sxe->addChild('laterality'));
        }
        if (0 < count($this->specimen)) {
            foreach ($this->specimen as $specimen) {
                $specimen->xmlSerialize(true, $sxe->addChild('specimen'));
            }
        }
        if (isset($this->started)) {
            $this->started->xmlSerialize(true, $sxe->addChild('started'));
        }
        if (0 < count($this->performer)) {
            foreach ($this->performer as $performer) {
                $performer->xmlSerialize(true, $sxe->addChild('performer'));
            }
        }
        if (0 < count($this->instance)) {
            foreach ($this->instance as $instance) {
                $instance->xmlSerialize(true, $sxe->addChild('instance'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
