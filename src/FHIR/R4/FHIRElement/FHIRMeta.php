<?php

namespace OpenEMR\FHIR\R4\FHIRElement;

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

use OpenEMR\FHIR\R4\FHIRElement;

/**
 * The metadata about a resource. This is content in the resource that is maintained by the infrastructure. Changes to the content might not always be associated with version changes to the resource.
 * If the element is present, it must have a value for at least one of the defined elements, an @id referenced from the Narrative, or extensions
 */
class FHIRMeta extends FHIRElement implements \JsonSerializable
{
    /**
     * The version specific identifier, as it appears in the version portion of the URL. This value changes when the resource is created, updated, or deleted.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public $versionId = null;

    /**
     * When the resource last changed - e.g. when the version changed.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRInstant
     */
    public $lastUpdated = null;

    /**
     * A uri that identifies the source system of the resource. This provides a minimal amount of [[[Provenance]]] information that can be used to track or differentiate the source of information in the resource. The source may identify another FHIR server, document, message, database, etc.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public $source = null;

    /**
     * A list of profiles (references to [[[StructureDefinition]]] resources) that this resource claims to conform to. The URL is a reference to [[[StructureDefinition.url]]].
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical[]
     */
    public $profile = [];

    /**
     * Security labels applied to this resource. These tags connect specific resources to the overall security policy and infrastructure.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding[]
     */
    public $security = [];

    /**
     * Tags applied to this resource. Tags are intended to be used to identify and relate resources to process and workflow, and applications are not required to consider the tags when interpreting the meaning of a resource.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding[]
     */
    public $tag = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'Meta';

    /**
     * The version specific identifier, as it appears in the version portion of the URL. This value changes when the resource is created, updated, or deleted.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRId
     */
    public function getVersionId()
    {
        return $this->versionId;
    }

    /**
     * The version specific identifier, as it appears in the version portion of the URL. This value changes when the resource is created, updated, or deleted.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRId $versionId
     * @return $this
     */
    public function setVersionId($versionId)
    {
        $this->versionId = $versionId;
        return $this;
    }

    /**
     * When the resource last changed - e.g. when the version changed.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRInstant
     */
    public function getLastUpdated()
    {
        return $this->lastUpdated;
    }

    /**
     * When the resource last changed - e.g. when the version changed.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRInstant $lastUpdated
     * @return $this
     */
    public function setLastUpdated($lastUpdated)
    {
        $this->lastUpdated = $lastUpdated;
        return $this;
    }

    /**
     * A uri that identifies the source system of the resource. This provides a minimal amount of [[[Provenance]]] information that can be used to track or differentiate the source of information in the resource. The source may identify another FHIR server, document, message, database, etc.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * A uri that identifies the source system of the resource. This provides a minimal amount of [[[Provenance]]] information that can be used to track or differentiate the source of information in the resource. The source may identify another FHIR server, document, message, database, etc.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUri $source
     * @return $this
     */
    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }

    /**
     * A list of profiles (references to [[[StructureDefinition]]] resources) that this resource claims to conform to. The URL is a reference to [[[StructureDefinition.url]]].
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical[]
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * A list of profiles (references to [[[StructureDefinition]]] resources) that this resource claims to conform to. The URL is a reference to [[[StructureDefinition.url]]].
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical $profile
     * @return $this
     */
    public function addProfile($profile)
    {
        $this->profile[] = $profile;
        return $this;
    }

    /**
     * Security labels applied to this resource. These tags connect specific resources to the overall security policy and infrastructure.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding[]
     */
    public function getSecurity()
    {
        return $this->security;
    }

    /**
     * Security labels applied to this resource. These tags connect specific resources to the overall security policy and infrastructure.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding $security
     * @return $this
     */
    public function addSecurity($security)
    {
        $this->security[] = $security;
        return $this;
    }

    /**
     * Tags applied to this resource. Tags are intended to be used to identify and relate resources to process and workflow, and applications are not required to consider the tags when interpreting the meaning of a resource.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding[]
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * Tags applied to this resource. Tags are intended to be used to identify and relate resources to process and workflow, and applications are not required to consider the tags when interpreting the meaning of a resource.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding $tag
     * @return $this
     */
    public function addTag($tag)
    {
        $this->tag[] = $tag;
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
            if (isset($data['versionId'])) {
                $this->setVersionId($data['versionId']);
            }
            if (isset($data['lastUpdated'])) {
                $this->setLastUpdated($data['lastUpdated']);
            }
            if (isset($data['source'])) {
                $this->setSource($data['source']);
            }
            if (isset($data['profile'])) {
                if (is_array($data['profile'])) {
                    foreach ($data['profile'] as $d) {
                        $this->addProfile($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"profile" must be array of objects or null, ' . gettype($data['profile']) . ' seen.');
                }
            }
            if (isset($data['security'])) {
                if (is_array($data['security'])) {
                    foreach ($data['security'] as $d) {
                        $this->addSecurity($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"security" must be array of objects or null, ' . gettype($data['security']) . ' seen.');
                }
            }
            if (isset($data['tag'])) {
                if (is_array($data['tag'])) {
                    foreach ($data['tag'] as $d) {
                        $this->addTag($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"tag" must be array of objects or null, ' . gettype($data['tag']) . ' seen.');
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
        if (isset($this->versionId)) {
            $json['versionId'] = $this->versionId;
        }
        if (isset($this->lastUpdated)) {
            $json['lastUpdated'] = $this->lastUpdated;
        }
        if (isset($this->source)) {
            $json['source'] = $this->source;
        }
        if (0 < count($this->profile)) {
            $json['profile'] = [];
            foreach ($this->profile as $profile) {
                $json['profile'][] = $profile;
            }
        }
        if (0 < count($this->security)) {
            $json['security'] = [];
            foreach ($this->security as $security) {
                $json['security'][] = $security;
            }
        }
        if (0 < count($this->tag)) {
            $json['tag'] = [];
            foreach ($this->tag as $tag) {
                $json['tag'][] = $tag;
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
            $sxe = new \SimpleXMLElement('<Meta xmlns="http://hl7.org/fhir"></Meta>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->versionId)) {
            $this->versionId->xmlSerialize(true, $sxe->addChild('versionId'));
        }
        if (isset($this->lastUpdated)) {
            $this->lastUpdated->xmlSerialize(true, $sxe->addChild('lastUpdated'));
        }
        if (isset($this->source)) {
            $this->source->xmlSerialize(true, $sxe->addChild('source'));
        }
        if (0 < count($this->profile)) {
            foreach ($this->profile as $profile) {
                $profile->xmlSerialize(true, $sxe->addChild('profile'));
            }
        }
        if (0 < count($this->security)) {
            foreach ($this->security as $security) {
                $security->xmlSerialize(true, $sxe->addChild('security'));
            }
        }
        if (0 < count($this->tag)) {
            foreach ($this->tag as $tag) {
                $tag->xmlSerialize(true, $sxe->addChild('tag'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
