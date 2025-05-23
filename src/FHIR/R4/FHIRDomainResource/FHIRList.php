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
 * A list is a curated collection of resources.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRList extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * Identifier for the List assigned for business purposes outside the context of FHIR.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * Indicates the current state of this list.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRListStatus
     */
    public $status = null;

    /**
     * How this list was prepared - whether it is a working list that is suitable for being maintained on an ongoing basis, or if it represents a snapshot of a list of items from another source, or whether it is a prepared list where items may be marked as added, modified or deleted.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRListMode
     */
    public $mode = null;

    /**
     * A label for the list assigned by the author.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $title = null;

    /**
     * This code defines the purpose of the list - why it was created.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $code = null;

    /**
     * The common subject (or patient) of the resources that are in the list if there is one.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $subject = null;

    /**
     * The encounter that is the context in which this list was created.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $encounter = null;

    /**
     * The date that the list was prepared.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $date = null;

    /**
     * The entity responsible for deciding what the contents of the list were. Where the list was created by a human, this is the same as the author of the list.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $source = null;

    /**
     * What order applies to the items in the list.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $orderedBy = null;

    /**
     * Comments that apply to the overall list.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation[]
     */
    public $note = [];

    /**
     * Entries in this list.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRList\FHIRListEntry[]
     */
    public $entry = [];

    /**
     * If the list is empty, why the list is empty.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $emptyReason = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'List';

    /**
     * Identifier for the List assigned for business purposes outside the context of FHIR.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Identifier for the List assigned for business purposes outside the context of FHIR.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * Indicates the current state of this list.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRListStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Indicates the current state of this list.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRListStatus $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * How this list was prepared - whether it is a working list that is suitable for being maintained on an ongoing basis, or if it represents a snapshot of a list of items from another source, or whether it is a prepared list where items may be marked as added, modified or deleted.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRListMode
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * How this list was prepared - whether it is a working list that is suitable for being maintained on an ongoing basis, or if it represents a snapshot of a list of items from another source, or whether it is a prepared list where items may be marked as added, modified or deleted.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRListMode $mode
     * @return $this
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * A label for the list assigned by the author.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * A label for the list assigned by the author.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * This code defines the purpose of the list - why it was created.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * This code defines the purpose of the list - why it was created.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * The common subject (or patient) of the resources that are in the list if there is one.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * The common subject (or patient) of the resources that are in the list if there is one.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * The encounter that is the context in which this list was created.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getEncounter()
    {
        return $this->encounter;
    }

    /**
     * The encounter that is the context in which this list was created.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $encounter
     * @return $this
     */
    public function setEncounter($encounter)
    {
        $this->encounter = $encounter;
        return $this;
    }

    /**
     * The date that the list was prepared.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * The date that the list was prepared.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $date
     * @return $this
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * The entity responsible for deciding what the contents of the list were. Where the list was created by a human, this is the same as the author of the list.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * The entity responsible for deciding what the contents of the list were. Where the list was created by a human, this is the same as the author of the list.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $source
     * @return $this
     */
    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }

    /**
     * What order applies to the items in the list.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getOrderedBy()
    {
        return $this->orderedBy;
    }

    /**
     * What order applies to the items in the list.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $orderedBy
     * @return $this
     */
    public function setOrderedBy($orderedBy)
    {
        $this->orderedBy = $orderedBy;
        return $this;
    }

    /**
     * Comments that apply to the overall list.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation[]
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Comments that apply to the overall list.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation $note
     * @return $this
     */
    public function addNote($note)
    {
        $this->note[] = $note;
        return $this;
    }

    /**
     * Entries in this list.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRList\FHIRListEntry[]
     */
    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * Entries in this list.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRList\FHIRListEntry $entry
     * @return $this
     */
    public function addEntry($entry)
    {
        $this->entry[] = $entry;
        return $this;
    }

    /**
     * If the list is empty, why the list is empty.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getEmptyReason()
    {
        return $this->emptyReason;
    }

    /**
     * If the list is empty, why the list is empty.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $emptyReason
     * @return $this
     */
    public function setEmptyReason($emptyReason)
    {
        $this->emptyReason = $emptyReason;
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
            if (isset($data['mode'])) {
                $this->setMode($data['mode']);
            }
            if (isset($data['title'])) {
                $this->setTitle($data['title']);
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
            if (isset($data['date'])) {
                $this->setDate($data['date']);
            }
            if (isset($data['source'])) {
                $this->setSource($data['source']);
            }
            if (isset($data['orderedBy'])) {
                $this->setOrderedBy($data['orderedBy']);
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
            if (isset($data['entry'])) {
                if (is_array($data['entry'])) {
                    foreach ($data['entry'] as $d) {
                        $this->addEntry($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"entry" must be array of objects or null, ' . gettype($data['entry']) . ' seen.');
                }
            }
            if (isset($data['emptyReason'])) {
                $this->setEmptyReason($data['emptyReason']);
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
        if (isset($this->mode)) {
            $json['mode'] = $this->mode;
        }
        if (isset($this->title)) {
            $json['title'] = $this->title;
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
        if (isset($this->date)) {
            $json['date'] = $this->date;
        }
        if (isset($this->source)) {
            $json['source'] = $this->source;
        }
        if (isset($this->orderedBy)) {
            $json['orderedBy'] = $this->orderedBy;
        }
        if (0 < count($this->note)) {
            $json['note'] = [];
            foreach ($this->note as $note) {
                $json['note'][] = $note;
            }
        }
        if (0 < count($this->entry)) {
            $json['entry'] = [];
            foreach ($this->entry as $entry) {
                $json['entry'][] = $entry;
            }
        }
        if (isset($this->emptyReason)) {
            $json['emptyReason'] = $this->emptyReason;
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
            $sxe = new \SimpleXMLElement('<List xmlns="http://hl7.org/fhir"></List>');
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
        if (isset($this->mode)) {
            $this->mode->xmlSerialize(true, $sxe->addChild('mode'));
        }
        if (isset($this->title)) {
            $this->title->xmlSerialize(true, $sxe->addChild('title'));
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
        if (isset($this->date)) {
            $this->date->xmlSerialize(true, $sxe->addChild('date'));
        }
        if (isset($this->source)) {
            $this->source->xmlSerialize(true, $sxe->addChild('source'));
        }
        if (isset($this->orderedBy)) {
            $this->orderedBy->xmlSerialize(true, $sxe->addChild('orderedBy'));
        }
        if (0 < count($this->note)) {
            foreach ($this->note as $note) {
                $note->xmlSerialize(true, $sxe->addChild('note'));
            }
        }
        if (0 < count($this->entry)) {
            foreach ($this->entry as $entry) {
                $entry->xmlSerialize(true, $sxe->addChild('entry'));
            }
        }
        if (isset($this->emptyReason)) {
            $this->emptyReason->xmlSerialize(true, $sxe->addChild('emptyReason'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
