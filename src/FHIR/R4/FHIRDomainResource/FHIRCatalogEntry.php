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
 * Catalog entries are wrappers that contextualize items included in a catalog.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRCatalogEntry extends FHIRDomainResource implements \JsonSerializable
{
    /**
     * Used in supporting different identifiers for the same product, e.g. manufacturer code and retailer code.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = [];

    /**
     * The type of item - medication, device, service, protocol or other.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $type = null;

    /**
     * Whether the entry represents an orderable item.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public $orderable = null;

    /**
     * The item in a catalog or definition.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $referencedItem = null;

    /**
     * Used in supporting related concepts, e.g. NDC to RxNorm.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public $additionalIdentifier = [];

    /**
     * Classes of devices, or ATC for medication.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $classification = [];

    /**
     * Used to support catalog exchange even for unsupported products, e.g. getting list of medications even if not prescribable.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPublicationStatus
     */
    public $status = null;

    /**
     * The time period in which this catalog entry is expected to be active.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public $validityPeriod = null;

    /**
     * The date until which this catalog entry is expected to be active.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $validTo = null;

    /**
     * Typically date of issue is different from the beginning of the validity. This can be used to see when an item was last updated.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $lastUpdated = null;

    /**
     * Used for examplefor Out of Formulary, or any specifics.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $additionalCharacteristic = [];

    /**
     * User for example for ATC classification, or.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $additionalClassification = [];

    /**
     * Used for example, to point to a substance, or to a device used to administer a medication.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRCatalogEntry\FHIRCatalogEntryRelatedEntry[]
     */
    public $relatedEntry = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'CatalogEntry';

    /**
     * Used in supporting different identifiers for the same product, e.g. manufacturer code and retailer code.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Used in supporting different identifiers for the same product, e.g. manufacturer code and retailer code.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * The type of item - medication, device, service, protocol or other.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * The type of item - medication, device, service, protocol or other.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Whether the entry represents an orderable item.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean
     */
    public function getOrderable()
    {
        return $this->orderable;
    }

    /**
     * Whether the entry represents an orderable item.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean $orderable
     * @return $this
     */
    public function setOrderable($orderable)
    {
        $this->orderable = $orderable;
        return $this;
    }

    /**
     * The item in a catalog or definition.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getReferencedItem()
    {
        return $this->referencedItem;
    }

    /**
     * The item in a catalog or definition.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $referencedItem
     * @return $this
     */
    public function setReferencedItem($referencedItem)
    {
        $this->referencedItem = $referencedItem;
        return $this;
    }

    /**
     * Used in supporting related concepts, e.g. NDC to RxNorm.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier[]
     */
    public function getAdditionalIdentifier()
    {
        return $this->additionalIdentifier;
    }

    /**
     * Used in supporting related concepts, e.g. NDC to RxNorm.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $additionalIdentifier
     * @return $this
     */
    public function addAdditionalIdentifier($additionalIdentifier)
    {
        $this->additionalIdentifier[] = $additionalIdentifier;
        return $this;
    }

    /**
     * Classes of devices, or ATC for medication.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getClassification()
    {
        return $this->classification;
    }

    /**
     * Classes of devices, or ATC for medication.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $classification
     * @return $this
     */
    public function addClassification($classification)
    {
        $this->classification[] = $classification;
        return $this;
    }

    /**
     * Used to support catalog exchange even for unsupported products, e.g. getting list of medications even if not prescribable.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPublicationStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Used to support catalog exchange even for unsupported products, e.g. getting list of medications even if not prescribable.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPublicationStatus $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * The time period in which this catalog entry is expected to be active.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public function getValidityPeriod()
    {
        return $this->validityPeriod;
    }

    /**
     * The time period in which this catalog entry is expected to be active.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod $validityPeriod
     * @return $this
     */
    public function setValidityPeriod($validityPeriod)
    {
        $this->validityPeriod = $validityPeriod;
        return $this;
    }

    /**
     * The date until which this catalog entry is expected to be active.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getValidTo()
    {
        return $this->validTo;
    }

    /**
     * The date until which this catalog entry is expected to be active.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $validTo
     * @return $this
     */
    public function setValidTo($validTo)
    {
        $this->validTo = $validTo;
        return $this;
    }

    /**
     * Typically date of issue is different from the beginning of the validity. This can be used to see when an item was last updated.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getLastUpdated()
    {
        return $this->lastUpdated;
    }

    /**
     * Typically date of issue is different from the beginning of the validity. This can be used to see when an item was last updated.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $lastUpdated
     * @return $this
     */
    public function setLastUpdated($lastUpdated)
    {
        $this->lastUpdated = $lastUpdated;
        return $this;
    }

    /**
     * Used for examplefor Out of Formulary, or any specifics.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getAdditionalCharacteristic()
    {
        return $this->additionalCharacteristic;
    }

    /**
     * Used for examplefor Out of Formulary, or any specifics.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $additionalCharacteristic
     * @return $this
     */
    public function addAdditionalCharacteristic($additionalCharacteristic)
    {
        $this->additionalCharacteristic[] = $additionalCharacteristic;
        return $this;
    }

    /**
     * User for example for ATC classification, or.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getAdditionalClassification()
    {
        return $this->additionalClassification;
    }

    /**
     * User for example for ATC classification, or.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $additionalClassification
     * @return $this
     */
    public function addAdditionalClassification($additionalClassification)
    {
        $this->additionalClassification[] = $additionalClassification;
        return $this;
    }

    /**
     * Used for example, to point to a substance, or to a device used to administer a medication.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRCatalogEntry\FHIRCatalogEntryRelatedEntry[]
     */
    public function getRelatedEntry()
    {
        return $this->relatedEntry;
    }

    /**
     * Used for example, to point to a substance, or to a device used to administer a medication.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRCatalogEntry\FHIRCatalogEntryRelatedEntry $relatedEntry
     * @return $this
     */
    public function addRelatedEntry($relatedEntry)
    {
        $this->relatedEntry[] = $relatedEntry;
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
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['orderable'])) {
                $this->setOrderable($data['orderable']);
            }
            if (isset($data['referencedItem'])) {
                $this->setReferencedItem($data['referencedItem']);
            }
            if (isset($data['additionalIdentifier'])) {
                if (is_array($data['additionalIdentifier'])) {
                    foreach ($data['additionalIdentifier'] as $d) {
                        $this->addAdditionalIdentifier($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"additionalIdentifier" must be array of objects or null, ' . gettype($data['additionalIdentifier']) . ' seen.');
                }
            }
            if (isset($data['classification'])) {
                if (is_array($data['classification'])) {
                    foreach ($data['classification'] as $d) {
                        $this->addClassification($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"classification" must be array of objects or null, ' . gettype($data['classification']) . ' seen.');
                }
            }
            if (isset($data['status'])) {
                $this->setStatus($data['status']);
            }
            if (isset($data['validityPeriod'])) {
                $this->setValidityPeriod($data['validityPeriod']);
            }
            if (isset($data['validTo'])) {
                $this->setValidTo($data['validTo']);
            }
            if (isset($data['lastUpdated'])) {
                $this->setLastUpdated($data['lastUpdated']);
            }
            if (isset($data['additionalCharacteristic'])) {
                if (is_array($data['additionalCharacteristic'])) {
                    foreach ($data['additionalCharacteristic'] as $d) {
                        $this->addAdditionalCharacteristic($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"additionalCharacteristic" must be array of objects or null, ' . gettype($data['additionalCharacteristic']) . ' seen.');
                }
            }
            if (isset($data['additionalClassification'])) {
                if (is_array($data['additionalClassification'])) {
                    foreach ($data['additionalClassification'] as $d) {
                        $this->addAdditionalClassification($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"additionalClassification" must be array of objects or null, ' . gettype($data['additionalClassification']) . ' seen.');
                }
            }
            if (isset($data['relatedEntry'])) {
                if (is_array($data['relatedEntry'])) {
                    foreach ($data['relatedEntry'] as $d) {
                        $this->addRelatedEntry($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"relatedEntry" must be array of objects or null, ' . gettype($data['relatedEntry']) . ' seen.');
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
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->orderable)) {
            $json['orderable'] = $this->orderable;
        }
        if (isset($this->referencedItem)) {
            $json['referencedItem'] = $this->referencedItem;
        }
        if (0 < count($this->additionalIdentifier)) {
            $json['additionalIdentifier'] = [];
            foreach ($this->additionalIdentifier as $additionalIdentifier) {
                $json['additionalIdentifier'][] = $additionalIdentifier;
            }
        }
        if (0 < count($this->classification)) {
            $json['classification'] = [];
            foreach ($this->classification as $classification) {
                $json['classification'][] = $classification;
            }
        }
        if (isset($this->status)) {
            $json['status'] = $this->status;
        }
        if (isset($this->validityPeriod)) {
            $json['validityPeriod'] = $this->validityPeriod;
        }
        if (isset($this->validTo)) {
            $json['validTo'] = $this->validTo;
        }
        if (isset($this->lastUpdated)) {
            $json['lastUpdated'] = $this->lastUpdated;
        }
        if (0 < count($this->additionalCharacteristic)) {
            $json['additionalCharacteristic'] = [];
            foreach ($this->additionalCharacteristic as $additionalCharacteristic) {
                $json['additionalCharacteristic'][] = $additionalCharacteristic;
            }
        }
        if (0 < count($this->additionalClassification)) {
            $json['additionalClassification'] = [];
            foreach ($this->additionalClassification as $additionalClassification) {
                $json['additionalClassification'][] = $additionalClassification;
            }
        }
        if (0 < count($this->relatedEntry)) {
            $json['relatedEntry'] = [];
            foreach ($this->relatedEntry as $relatedEntry) {
                $json['relatedEntry'][] = $relatedEntry;
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
            $sxe = new \SimpleXMLElement('<CatalogEntry xmlns="http://hl7.org/fhir"></CatalogEntry>');
        }
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->identifier)) {
            foreach ($this->identifier as $identifier) {
                $identifier->xmlSerialize(true, $sxe->addChild('identifier'));
            }
        }
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->orderable)) {
            $this->orderable->xmlSerialize(true, $sxe->addChild('orderable'));
        }
        if (isset($this->referencedItem)) {
            $this->referencedItem->xmlSerialize(true, $sxe->addChild('referencedItem'));
        }
        if (0 < count($this->additionalIdentifier)) {
            foreach ($this->additionalIdentifier as $additionalIdentifier) {
                $additionalIdentifier->xmlSerialize(true, $sxe->addChild('additionalIdentifier'));
            }
        }
        if (0 < count($this->classification)) {
            foreach ($this->classification as $classification) {
                $classification->xmlSerialize(true, $sxe->addChild('classification'));
            }
        }
        if (isset($this->status)) {
            $this->status->xmlSerialize(true, $sxe->addChild('status'));
        }
        if (isset($this->validityPeriod)) {
            $this->validityPeriod->xmlSerialize(true, $sxe->addChild('validityPeriod'));
        }
        if (isset($this->validTo)) {
            $this->validTo->xmlSerialize(true, $sxe->addChild('validTo'));
        }
        if (isset($this->lastUpdated)) {
            $this->lastUpdated->xmlSerialize(true, $sxe->addChild('lastUpdated'));
        }
        if (0 < count($this->additionalCharacteristic)) {
            foreach ($this->additionalCharacteristic as $additionalCharacteristic) {
                $additionalCharacteristic->xmlSerialize(true, $sxe->addChild('additionalCharacteristic'));
            }
        }
        if (0 < count($this->additionalClassification)) {
            foreach ($this->additionalClassification as $additionalClassification) {
                $additionalClassification->xmlSerialize(true, $sxe->addChild('additionalClassification'));
            }
        }
        if (0 < count($this->relatedEntry)) {
            foreach ($this->relatedEntry as $relatedEntry) {
                $relatedEntry->xmlSerialize(true, $sxe->addChild('relatedEntry'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
