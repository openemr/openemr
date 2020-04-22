<?php

namespace OpenEMR\FHIR\R4\FHIRResource;

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

use OpenEMR\FHIR\R4\FHIRResource;

/**
 * A container for a collection of resources.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRBundle extends FHIRResource implements \JsonSerializable
{
    /**
     * A persistent identifier for the bundle that won't change as a bundle is copied from server to server.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier
     */
    public $identifier = null;

    /**
     * Indicates the purpose of this bundle - how it is intended to be used.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBundleType
     */
    public $type = null;

    /**
     * The date/time that the bundle was assembled - i.e. when the resources were placed in the bundle.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRInstant
     */
    public $timestamp = null;

    /**
     * If a set of search matches, this is the total number of entries of type 'match' across all pages in the search.  It does not include search.mode = 'include' or 'outcome' entries and it does not provide a count of the number of entries in the Bundle.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt
     */
    public $total = null;

    /**
     * A series of links that provide context to this bundle.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleLink[]
     */
    public $link = [];

    /**
     * An entry in a bundle resource - will either contain a resource or information about a resource (transactions and history only).
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry[]
     */
    public $entry = [];

    /**
     * Digital Signature - base64 encoded. XML-DSig or a JWT.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRSignature
     */
    public $signature = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Bundle';

    /**
     * A persistent identifier for the bundle that won't change as a bundle is copied from server to server.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * A persistent identifier for the bundle that won't change as a bundle is copied from server to server.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * Indicates the purpose of this bundle - how it is intended to be used.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBundleType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Indicates the purpose of this bundle - how it is intended to be used.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBundleType $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * The date/time that the bundle was assembled - i.e. when the resources were placed in the bundle.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRInstant
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * The date/time that the bundle was assembled - i.e. when the resources were placed in the bundle.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRInstant $timestamp
     * @return $this
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    /**
     * If a set of search matches, this is the total number of entries of type 'match' across all pages in the search.  It does not include search.mode = 'include' or 'outcome' entries and it does not provide a count of the number of entries in the Bundle.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * If a set of search matches, this is the total number of entries of type 'match' across all pages in the search.  It does not include search.mode = 'include' or 'outcome' entries and it does not provide a count of the number of entries in the Bundle.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt $total
     * @return $this
     */
    public function setTotal($total)
    {
        $this->total = $total;
        return $this;
    }

    /**
     * A series of links that provide context to this bundle.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleLink[]
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * A series of links that provide context to this bundle.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleLink $link
     * @return $this
     */
    public function addLink($link)
    {
        $this->link[] = $link;
        return $this;
    }

    /**
     * An entry in a bundle resource - will either contain a resource or information about a resource (transactions and history only).
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry[]
     */
    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * An entry in a bundle resource - will either contain a resource or information about a resource (transactions and history only).
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry $entry
     * @return $this
     */
    public function addEntry($entry)
    {
        $this->entry[] = $entry;
        return $this;
    }

    /**
     * Digital Signature - base64 encoded. XML-DSig or a JWT.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRSignature
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * Digital Signature - base64 encoded. XML-DSig or a JWT.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRSignature $signature
     * @return $this
     */
    public function setSignature($signature)
    {
        $this->signature = $signature;
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
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['timestamp'])) {
                $this->setTimestamp($data['timestamp']);
            }
            if (isset($data['total'])) {
                $this->setTotal($data['total']);
            }
            if (isset($data['link'])) {
                if (is_array($data['link'])) {
                    foreach ($data['link'] as $d) {
                        $this->addLink($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"link" must be array of objects or null, ' . gettype($data['link']) . ' seen.');
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
            if (isset($data['signature'])) {
                $this->setSignature($data['signature']);
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
        if (isset($this->identifier)) {
            $json['identifier'] = $this->identifier;
        }
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->timestamp)) {
            $json['timestamp'] = $this->timestamp;
        }
        if (isset($this->total)) {
            $json['total'] = $this->total;
        }
        if (0 < count($this->link)) {
            $json['link'] = [];
            foreach ($this->link as $link) {
                $json['link'][] = $link;
            }
        }
        if (0 < count($this->entry)) {
            $json['entry'] = [];
            foreach ($this->entry as $entry) {
                $json['entry'][] = $entry;
            }
        }
        if (isset($this->signature)) {
            $json['signature'] = $this->signature;
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
            $sxe = new \SimpleXMLElement('<Bundle xmlns="http://hl7.org/fhir"></Bundle>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->identifier)) {
            $this->identifier->xmlSerialize(true, $sxe->addChild('identifier'));
        }
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->timestamp)) {
            $this->timestamp->xmlSerialize(true, $sxe->addChild('timestamp'));
        }
        if (isset($this->total)) {
            $this->total->xmlSerialize(true, $sxe->addChild('total'));
        }
        if (0 < count($this->link)) {
            foreach ($this->link as $link) {
                $link->xmlSerialize(true, $sxe->addChild('link'));
            }
        }
        if (0 < count($this->entry)) {
            foreach ($this->entry as $entry) {
                $entry->xmlSerialize(true, $sxe->addChild('entry'));
            }
        }
        if (isset($this->signature)) {
            $this->signature->xmlSerialize(true, $sxe->addChild('signature'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
