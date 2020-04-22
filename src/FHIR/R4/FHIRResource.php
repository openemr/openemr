<?php

namespace OpenEMR\FHIR\R4;

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

/**
 * This is the base resource type for everything.
 */
class FHIRResource implements \JsonSerializable
{
    /**
     * The logical id of the resource, as used in the URL for the resource. Once assigned, this value never changes.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public $id = null;

    /**
     * The metadata about the resource. This is content that is maintained by the infrastructure. Changes to the content might not always be associated with version changes to the resource.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRMeta
     */
    public $meta = null;

    /**
     * A reference to a set of rules that were followed when the resource was constructed, and which must be understood when processing the content. Often, this is a reference to an implementation guide that defines the special rules along with other profiles etc.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public $implicitRules = null;

    /**
     * The base language in which the resource is written.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCode
     */
    public $language = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'Resource';

    /**
     * The logical id of the resource, as used in the URL for the resource. Once assigned, this value never changes.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * The logical id of the resource, as used in the URL for the resource. Once assigned, this value never changes.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRId $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * The metadata about the resource. This is content that is maintained by the infrastructure. Changes to the content might not always be associated with version changes to the resource.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRMeta
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * The metadata about the resource. This is content that is maintained by the infrastructure. Changes to the content might not always be associated with version changes to the resource.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRMeta $meta
     * @return $this
     */
    public function setMeta($meta)
    {
        $this->meta = $meta;
        return $this;
    }

    /**
     * A reference to a set of rules that were followed when the resource was constructed, and which must be understood when processing the content. Often, this is a reference to an implementation guide that defines the special rules along with other profiles etc.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public function getImplicitRules()
    {
        return $this->implicitRules;
    }

    /**
     * A reference to a set of rules that were followed when the resource was constructed, and which must be understood when processing the content. Often, this is a reference to an implementation guide that defines the special rules along with other profiles etc.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUri $implicitRules
     * @return $this
     */
    public function setImplicitRules($implicitRules)
    {
        $this->implicitRules = $implicitRules;
        return $this;
    }

    /**
     * The base language in which the resource is written.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCode
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * The base language in which the resource is written.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCode $language
     * @return $this
     */
    public function setLanguage($language)
    {
        $this->language = $language;
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
            if (isset($data['id'])) {
                $this->setId($data['id']);
            }
            if (isset($data['meta'])) {
                $this->setMeta($data['meta']);
            }
            if (isset($data['implicitRules'])) {
                $this->setImplicitRules($data['implicitRules']);
            }
            if (isset($data['language'])) {
                $this->setLanguage($data['language']);
            }
        } elseif (null !== $data) {
            throw new \InvalidArgumentException('$data expected to be array of values, saw "' . gettype($data) . '"');
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getId();
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $json = [];
        if (isset($this->id)) {
            $json['id'] = $this->id;
        }
        if (isset($this->meta)) {
            $json['meta'] = $this->meta;
        }
        if (isset($this->implicitRules)) {
            $json['implicitRules'] = $this->implicitRules;
        }
        if (isset($this->language)) {
            $json['language'] = $this->language;
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
            $sxe = new \SimpleXMLElement('<Resource xmlns="http://hl7.org/fhir"></Resource>');
        }
        if (isset($this->id)) {
            $this->id->xmlSerialize(true, $sxe->addChild('id'));
        }
        if (isset($this->meta)) {
            $this->meta->xmlSerialize(true, $sxe->addChild('meta'));
        }
        if (isset($this->implicitRules)) {
            $this->implicitRules->xmlSerialize(true, $sxe->addChild('implicitRules'));
        }
        if (isset($this->language)) {
            $this->language->xmlSerialize(true, $sxe->addChild('language'));
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
