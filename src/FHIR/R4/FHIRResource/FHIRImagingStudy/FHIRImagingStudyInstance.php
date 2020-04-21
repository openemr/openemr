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
class FHIRImagingStudyInstance extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * The DICOM SOP Instance UID for this image or other DICOM content.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public $uid = null;

    /**
     * DICOM instance  type.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding
     */
    public $sopClass = null;

    /**
     * The number of instance in the series.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt
     */
    public $number = null;

    /**
     * The description of the instance.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $title = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'ImagingStudy.Instance';

    /**
     * The DICOM SOP Instance UID for this image or other DICOM content.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * The DICOM SOP Instance UID for this image or other DICOM content.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRId $uid
     * @return $this
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
        return $this;
    }

    /**
     * DICOM instance  type.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding
     */
    public function getSopClass()
    {
        return $this->sopClass;
    }

    /**
     * DICOM instance  type.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding $sopClass
     * @return $this
     */
    public function setSopClass($sopClass)
    {
        $this->sopClass = $sopClass;
        return $this;
    }

    /**
     * The number of instance in the series.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * The number of instance in the series.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt $number
     * @return $this
     */
    public function setNumber($number)
    {
        $this->number = $number;
        return $this;
    }

    /**
     * The description of the instance.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * The description of the instance.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
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
            if (isset($data['sopClass'])) {
                $this->setSopClass($data['sopClass']);
            }
            if (isset($data['number'])) {
                $this->setNumber($data['number']);
            }
            if (isset($data['title'])) {
                $this->setTitle($data['title']);
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
        if (isset($this->sopClass)) {
            $json['sopClass'] = $this->sopClass;
        }
        if (isset($this->number)) {
            $json['number'] = $this->number;
        }
        if (isset($this->title)) {
            $json['title'] = $this->title;
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
            $sxe = new \SimpleXMLElement('<ImagingStudyInstance xmlns="http://hl7.org/fhir"></ImagingStudyInstance>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->uid)) {
            $this->uid->xmlSerialize(true, $sxe->addChild('uid'));
        }
        if (isset($this->sopClass)) {
            $this->sopClass->xmlSerialize(true, $sxe->addChild('sopClass'));
        }
        if (isset($this->number)) {
            $this->number->xmlSerialize(true, $sxe->addChild('number'));
        }
        if (isset($this->title)) {
            $this->title->xmlSerialize(true, $sxe->addChild('title'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
