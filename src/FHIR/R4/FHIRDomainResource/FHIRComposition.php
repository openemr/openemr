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
 * A set of healthcare-related information that is assembled together into a single logical package that provides a single coherent statement of meaning, establishes its own context and that has clinical attestation with regard to who is making the statement. A Composition defines the structure and narrative content necessary for a document. However, a Composition alone does not constitute a document. Rather, the Composition must be the first entry in a Bundle where Bundle.type=document, and any other resources referenced from Composition must be included as subsequent entries in the Bundle (for example Patient, Practitioner, Encounter, etc.).
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRComposition extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * A version-independent identifier for the Composition. This identifier stays constant as the composition is changed over time.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier
     */
    public $identifier = null;

    /**
     * The workflow/clinical status of this composition. The status is a marker for the clinical standing of the document.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCompositionStatus
     */
    public $status = null;

    /**
     * Specifies the particular kind of composition (e.g. History and Physical, Discharge Summary, Progress Note). This usually equates to the purpose of making the composition.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $type = null;

    /**
     * A categorization for the type of the composition - helps for indexing and searching. This may be implied by or derived from the code specified in the Composition Type.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $category = [];

    /**
     * Who or what the composition is about. The composition can be about a person, (patient or healthcare practitioner), a device (e.g. a machine) or even a group of subjects (such as a document about a herd of livestock, or a set of patients that share a common exposure).
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $subject = null;

    /**
     * Describes the clinical encounter or type of care this documentation is associated with.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $encounter = null;

    /**
     * The composition editing time, when the composition was last logically changed by the author.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $date = null;

    /**
     * Identifies who is responsible for the information in the composition, not necessarily who typed it in.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $author = [];

    /**
     * Official human-readable label for the composition.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $title = null;

    /**
     * The code specifying the level of confidentiality of the Composition.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRVConfidentialityClassification
     */
    public $confidentiality = null;

    /**
     * A participant who has attested to the accuracy of the composition/document.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRComposition\FHIRCompositionAttester[]
     */
    public $attester = [];

    /**
     * Identifies the organization or group who is responsible for ongoing maintenance of and access to the composition/document information.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $custodian = null;

    /**
     * Relationships that this composition has with other compositions or documents that already exist.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRComposition\FHIRCompositionRelatesTo[]
     */
    public $relatesTo = [];

    /**
     * The clinical service, such as a colonoscopy or an appendectomy, being documented.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRComposition\FHIRCompositionEvent[]
     */
    public $event = [];

    /**
     * The root of the sections that make up the composition.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRComposition\FHIRCompositionSection[]
     */
    public $section = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'Composition';

    /**
     * A version-independent identifier for the Composition. This identifier stays constant as the composition is changed over time.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * A version-independent identifier for the Composition. This identifier stays constant as the composition is changed over time.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * The workflow/clinical status of this composition. The status is a marker for the clinical standing of the document.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCompositionStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * The workflow/clinical status of this composition. The status is a marker for the clinical standing of the document.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCompositionStatus $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Specifies the particular kind of composition (e.g. History and Physical, Discharge Summary, Progress Note). This usually equates to the purpose of making the composition.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Specifies the particular kind of composition (e.g. History and Physical, Discharge Summary, Progress Note). This usually equates to the purpose of making the composition.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * A categorization for the type of the composition - helps for indexing and searching. This may be implied by or derived from the code specified in the Composition Type.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * A categorization for the type of the composition - helps for indexing and searching. This may be implied by or derived from the code specified in the Composition Type.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $category
     * @return $this
     */
    public function addCategory($category)
    {
        $this->category[] = $category;
        return $this;
    }

    /**
     * Who or what the composition is about. The composition can be about a person, (patient or healthcare practitioner), a device (e.g. a machine) or even a group of subjects (such as a document about a herd of livestock, or a set of patients that share a common exposure).
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Who or what the composition is about. The composition can be about a person, (patient or healthcare practitioner), a device (e.g. a machine) or even a group of subjects (such as a document about a herd of livestock, or a set of patients that share a common exposure).
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Describes the clinical encounter or type of care this documentation is associated with.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getEncounter()
    {
        return $this->encounter;
    }

    /**
     * Describes the clinical encounter or type of care this documentation is associated with.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $encounter
     * @return $this
     */
    public function setEncounter($encounter)
    {
        $this->encounter = $encounter;
        return $this;
    }

    /**
     * The composition editing time, when the composition was last logically changed by the author.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * The composition editing time, when the composition was last logically changed by the author.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $date
     * @return $this
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * Identifies who is responsible for the information in the composition, not necessarily who typed it in.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Identifies who is responsible for the information in the composition, not necessarily who typed it in.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $author
     * @return $this
     */
    public function addAuthor($author)
    {
        $this->author[] = $author;
        return $this;
    }

    /**
     * Official human-readable label for the composition.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Official human-readable label for the composition.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * The code specifying the level of confidentiality of the Composition.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRVConfidentialityClassification
     */
    public function getConfidentiality()
    {
        return $this->confidentiality;
    }

    /**
     * The code specifying the level of confidentiality of the Composition.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRVConfidentialityClassification $confidentiality
     * @return $this
     */
    public function setConfidentiality($confidentiality)
    {
        $this->confidentiality = $confidentiality;
        return $this;
    }

    /**
     * A participant who has attested to the accuracy of the composition/document.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRComposition\FHIRCompositionAttester[]
     */
    public function getAttester()
    {
        return $this->attester;
    }

    /**
     * A participant who has attested to the accuracy of the composition/document.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRComposition\FHIRCompositionAttester $attester
     * @return $this
     */
    public function addAttester($attester)
    {
        $this->attester[] = $attester;
        return $this;
    }

    /**
     * Identifies the organization or group who is responsible for ongoing maintenance of and access to the composition/document information.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getCustodian()
    {
        return $this->custodian;
    }

    /**
     * Identifies the organization or group who is responsible for ongoing maintenance of and access to the composition/document information.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $custodian
     * @return $this
     */
    public function setCustodian($custodian)
    {
        $this->custodian = $custodian;
        return $this;
    }

    /**
     * Relationships that this composition has with other compositions or documents that already exist.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRComposition\FHIRCompositionRelatesTo[]
     */
    public function getRelatesTo()
    {
        return $this->relatesTo;
    }

    /**
     * Relationships that this composition has with other compositions or documents that already exist.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRComposition\FHIRCompositionRelatesTo $relatesTo
     * @return $this
     */
    public function addRelatesTo($relatesTo)
    {
        $this->relatesTo[] = $relatesTo;
        return $this;
    }

    /**
     * The clinical service, such as a colonoscopy or an appendectomy, being documented.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRComposition\FHIRCompositionEvent[]
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * The clinical service, such as a colonoscopy or an appendectomy, being documented.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRComposition\FHIRCompositionEvent $event
     * @return $this
     */
    public function addEvent($event)
    {
        $this->event[] = $event;
        return $this;
    }

    /**
     * The root of the sections that make up the composition.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRComposition\FHIRCompositionSection[]
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * The root of the sections that make up the composition.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRComposition\FHIRCompositionSection $section
     * @return $this
     */
    public function addSection($section)
    {
        $this->section[] = $section;
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
                $this->setIdentifier($data['identifier']);
            }
            if (isset($data['status'])) {
                $this->setStatus($data['status']);
            }
            if (isset($data['type'])) {
                $this->setType($data['type']);
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
            if (isset($data['subject'])) {
                $this->setSubject($data['subject']);
            }
            if (isset($data['encounter'])) {
                $this->setEncounter($data['encounter']);
            }
            if (isset($data['date'])) {
                $this->setDate($data['date']);
            }
            if (isset($data['author'])) {
                if (is_array($data['author'])) {
                    foreach ($data['author'] as $d) {
                        $this->addAuthor($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"author" must be array of objects or null, ' . gettype($data['author']) . ' seen.');
                }
            }
            if (isset($data['title'])) {
                $this->setTitle($data['title']);
            }
            if (isset($data['confidentiality'])) {
                $this->setConfidentiality($data['confidentiality']);
            }
            if (isset($data['attester'])) {
                if (is_array($data['attester'])) {
                    foreach ($data['attester'] as $d) {
                        $this->addAttester($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"attester" must be array of objects or null, ' . gettype($data['attester']) . ' seen.');
                }
            }
            if (isset($data['custodian'])) {
                $this->setCustodian($data['custodian']);
            }
            if (isset($data['relatesTo'])) {
                if (is_array($data['relatesTo'])) {
                    foreach ($data['relatesTo'] as $d) {
                        $this->addRelatesTo($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"relatesTo" must be array of objects or null, ' . gettype($data['relatesTo']) . ' seen.');
                }
            }
            if (isset($data['event'])) {
                if (is_array($data['event'])) {
                    foreach ($data['event'] as $d) {
                        $this->addEvent($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"event" must be array of objects or null, ' . gettype($data['event']) . ' seen.');
                }
            }
            if (isset($data['section'])) {
                if (is_array($data['section'])) {
                    foreach ($data['section'] as $d) {
                        $this->addSection($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"section" must be array of objects or null, ' . gettype($data['section']) . ' seen.');
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
        if (isset($this->identifier)) {
            $json['identifier'] = $this->identifier;
        }
        if (isset($this->status)) {
            $json['status'] = $this->status;
        }
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (0 < count($this->category)) {
            $json['category'] = [];
            foreach ($this->category as $category) {
                $json['category'][] = $category;
            }
        }
        if (isset($this->subject)) {
            $json['subject'] = $this->subject;
        }
        if (isset($this->encounter)) {
            $json['encounter'] = $this->encounter;
        }
        if (isset($this->date)) {
            $json['date'] = $this->date;
        }
        if (0 < count($this->author)) {
            $json['author'] = [];
            foreach ($this->author as $author) {
                $json['author'][] = $author;
            }
        }
        if (isset($this->title)) {
            $json['title'] = $this->title;
        }
        if (isset($this->confidentiality)) {
            $json['confidentiality'] = $this->confidentiality;
        }
        if (0 < count($this->attester)) {
            $json['attester'] = [];
            foreach ($this->attester as $attester) {
                $json['attester'][] = $attester;
            }
        }
        if (isset($this->custodian)) {
            $json['custodian'] = $this->custodian;
        }
        if (0 < count($this->relatesTo)) {
            $json['relatesTo'] = [];
            foreach ($this->relatesTo as $relatesTo) {
                $json['relatesTo'][] = $relatesTo;
            }
        }
        if (0 < count($this->event)) {
            $json['event'] = [];
            foreach ($this->event as $event) {
                $json['event'][] = $event;
            }
        }
        if (0 < count($this->section)) {
            $json['section'] = [];
            foreach ($this->section as $section) {
                $json['section'][] = $section;
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
            $sxe = new \SimpleXMLElement('<Composition xmlns="http://hl7.org/fhir"></Composition>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->identifier)) {
            $this->identifier->xmlSerialize(true, $sxe->addChild('identifier'));
        }
        if (isset($this->status)) {
            $this->status->xmlSerialize(true, $sxe->addChild('status'));
        }
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (0 < count($this->category)) {
            foreach ($this->category as $category) {
                $category->xmlSerialize(true, $sxe->addChild('category'));
            }
        }
        if (isset($this->subject)) {
            $this->subject->xmlSerialize(true, $sxe->addChild('subject'));
        }
        if (isset($this->encounter)) {
            $this->encounter->xmlSerialize(true, $sxe->addChild('encounter'));
        }
        if (isset($this->date)) {
            $this->date->xmlSerialize(true, $sxe->addChild('date'));
        }
        if (0 < count($this->author)) {
            foreach ($this->author as $author) {
                $author->xmlSerialize(true, $sxe->addChild('author'));
            }
        }
        if (isset($this->title)) {
            $this->title->xmlSerialize(true, $sxe->addChild('title'));
        }
        if (isset($this->confidentiality)) {
            $this->confidentiality->xmlSerialize(true, $sxe->addChild('confidentiality'));
        }
        if (0 < count($this->attester)) {
            foreach ($this->attester as $attester) {
                $attester->xmlSerialize(true, $sxe->addChild('attester'));
            }
        }
        if (isset($this->custodian)) {
            $this->custodian->xmlSerialize(true, $sxe->addChild('custodian'));
        }
        if (0 < count($this->relatesTo)) {
            foreach ($this->relatesTo as $relatesTo) {
                $relatesTo->xmlSerialize(true, $sxe->addChild('relatesTo'));
            }
        }
        if (0 < count($this->event)) {
            foreach ($this->event as $event) {
                $event->xmlSerialize(true, $sxe->addChild('event'));
            }
        }
        if (0 < count($this->section)) {
            foreach ($this->section as $section) {
                $section->xmlSerialize(true, $sxe->addChild('section'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
