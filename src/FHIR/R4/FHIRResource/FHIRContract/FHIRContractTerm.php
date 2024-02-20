<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRContract;

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
 * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a policy or agreement.
 */
class FHIRContractTerm extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Unique identifier for this particular Contract Provision.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier
     */
    public $identifier = null;

    /**
     * When this Contract Provision was issued.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public $issued = null;

    /**
     * Relevant time or time-period when this Contract Provision is applicable.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public $applies = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $topicCodeableConcept = null;

    /**
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public $topicReference = null;

    /**
     * A legal clause or condition contained within a contract that requires one or both parties to perform a particular requirement by some specified time or prevents one or both parties from performing a particular requirement by some specified time.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $type = null;

    /**
     * A specialized legal clause or condition based on overarching contract type.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $subType = null;

    /**
     * Statement of a provision in a policy or a contract.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $text = null;

    /**
     * Security labels that protect the handling of information about the term and its elements, which may be specifically identified..
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRContract\FHIRContractSecurityLabel[]
     */
    public $securityLabel = [];

    /**
     * The matter of concern in the context of this provision of the agrement.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRContract\FHIRContractOffer
     */
    public $offer = null;

    /**
     * Contract Term Asset List.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRContract\FHIRContractAsset[]
     */
    public $asset = [];

    /**
     * An actor taking a role in an activity for which it can be assigned some degree of responsibility for the activity taking place.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRContract\FHIRContractAction[]
     */
    public $action = [];

    /**
     * Nested group of Contract Provisions.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRContract\FHIRContractTerm[]
     */
    public $group = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'Contract.Term';

    /**
     * Unique identifier for this particular Contract Provision.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Unique identifier for this particular Contract Provision.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier $identifier
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * When this Contract Provision was issued.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime
     */
    public function getIssued()
    {
        return $this->issued;
    }

    /**
     * When this Contract Provision was issued.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime $issued
     * @return $this
     */
    public function setIssued($issued)
    {
        $this->issued = $issued;
        return $this;
    }

    /**
     * Relevant time or time-period when this Contract Provision is applicable.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public function getApplies()
    {
        return $this->applies;
    }

    /**
     * Relevant time or time-period when this Contract Provision is applicable.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod $applies
     * @return $this
     */
    public function setApplies($applies)
    {
        $this->applies = $applies;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getTopicCodeableConcept()
    {
        return $this->topicCodeableConcept;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $topicCodeableConcept
     * @return $this
     */
    public function setTopicCodeableConcept($topicCodeableConcept)
    {
        $this->topicCodeableConcept = $topicCodeableConcept;
        return $this;
    }

    /**
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getTopicReference()
    {
        return $this->topicReference;
    }

    /**
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $topicReference
     * @return $this
     */
    public function setTopicReference($topicReference)
    {
        $this->topicReference = $topicReference;
        return $this;
    }

    /**
     * A legal clause or condition contained within a contract that requires one or both parties to perform a particular requirement by some specified time or prevents one or both parties from performing a particular requirement by some specified time.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * A legal clause or condition contained within a contract that requires one or both parties to perform a particular requirement by some specified time or prevents one or both parties from performing a particular requirement by some specified time.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * A specialized legal clause or condition based on overarching contract type.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getSubType()
    {
        return $this->subType;
    }

    /**
     * A specialized legal clause or condition based on overarching contract type.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $subType
     * @return $this
     */
    public function setSubType($subType)
    {
        $this->subType = $subType;
        return $this;
    }

    /**
     * Statement of a provision in a policy or a contract.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Statement of a provision in a policy or a contract.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $text
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    /**
     * Security labels that protect the handling of information about the term and its elements, which may be specifically identified..
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRContract\FHIRContractSecurityLabel[]
     */
    public function getSecurityLabel()
    {
        return $this->securityLabel;
    }

    /**
     * Security labels that protect the handling of information about the term and its elements, which may be specifically identified..
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRContract\FHIRContractSecurityLabel $securityLabel
     * @return $this
     */
    public function addSecurityLabel($securityLabel)
    {
        $this->securityLabel[] = $securityLabel;
        return $this;
    }

    /**
     * The matter of concern in the context of this provision of the agrement.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRContract\FHIRContractOffer
     */
    public function getOffer()
    {
        return $this->offer;
    }

    /**
     * The matter of concern in the context of this provision of the agrement.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRContract\FHIRContractOffer $offer
     * @return $this
     */
    public function setOffer($offer)
    {
        $this->offer = $offer;
        return $this;
    }

    /**
     * Contract Term Asset List.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRContract\FHIRContractAsset[]
     */
    public function getAsset()
    {
        return $this->asset;
    }

    /**
     * Contract Term Asset List.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRContract\FHIRContractAsset $asset
     * @return $this
     */
    public function addAsset($asset)
    {
        $this->asset[] = $asset;
        return $this;
    }

    /**
     * An actor taking a role in an activity for which it can be assigned some degree of responsibility for the activity taking place.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRContract\FHIRContractAction[]
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * An actor taking a role in an activity for which it can be assigned some degree of responsibility for the activity taking place.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRContract\FHIRContractAction $action
     * @return $this
     */
    public function addAction($action)
    {
        $this->action[] = $action;
        return $this;
    }

    /**
     * Nested group of Contract Provisions.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRContract\FHIRContractTerm[]
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Nested group of Contract Provisions.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRContract\FHIRContractTerm $group
     * @return $this
     */
    public function addGroup($group)
    {
        $this->group[] = $group;
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
            if (isset($data['issued'])) {
                $this->setIssued($data['issued']);
            }
            if (isset($data['applies'])) {
                $this->setApplies($data['applies']);
            }
            if (isset($data['topicCodeableConcept'])) {
                $this->setTopicCodeableConcept($data['topicCodeableConcept']);
            }
            if (isset($data['topicReference'])) {
                $this->setTopicReference($data['topicReference']);
            }
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['subType'])) {
                $this->setSubType($data['subType']);
            }
            if (isset($data['text'])) {
                $this->setText($data['text']);
            }
            if (isset($data['securityLabel'])) {
                if (is_array($data['securityLabel'])) {
                    foreach ($data['securityLabel'] as $d) {
                        $this->addSecurityLabel($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"securityLabel" must be array of objects or null, ' . gettype($data['securityLabel']) . ' seen.');
                }
            }
            if (isset($data['offer'])) {
                $this->setOffer($data['offer']);
            }
            if (isset($data['asset'])) {
                if (is_array($data['asset'])) {
                    foreach ($data['asset'] as $d) {
                        $this->addAsset($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"asset" must be array of objects or null, ' . gettype($data['asset']) . ' seen.');
                }
            }
            if (isset($data['action'])) {
                if (is_array($data['action'])) {
                    foreach ($data['action'] as $d) {
                        $this->addAction($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"action" must be array of objects or null, ' . gettype($data['action']) . ' seen.');
                }
            }
            if (isset($data['group'])) {
                if (is_array($data['group'])) {
                    foreach ($data['group'] as $d) {
                        $this->addGroup($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"group" must be array of objects or null, ' . gettype($data['group']) . ' seen.');
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
        if (isset($this->identifier)) {
            $json['identifier'] = $this->identifier;
        }
        if (isset($this->issued)) {
            $json['issued'] = $this->issued;
        }
        if (isset($this->applies)) {
            $json['applies'] = $this->applies;
        }
        if (isset($this->topicCodeableConcept)) {
            $json['topicCodeableConcept'] = $this->topicCodeableConcept;
        }
        if (isset($this->topicReference)) {
            $json['topicReference'] = $this->topicReference;
        }
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->subType)) {
            $json['subType'] = $this->subType;
        }
        if (isset($this->text)) {
            $json['text'] = $this->text;
        }
        if (0 < count($this->securityLabel)) {
            $json['securityLabel'] = [];
            foreach ($this->securityLabel as $securityLabel) {
                $json['securityLabel'][] = $securityLabel;
            }
        }
        if (isset($this->offer)) {
            $json['offer'] = $this->offer;
        }
        if (0 < count($this->asset)) {
            $json['asset'] = [];
            foreach ($this->asset as $asset) {
                $json['asset'][] = $asset;
            }
        }
        if (0 < count($this->action)) {
            $json['action'] = [];
            foreach ($this->action as $action) {
                $json['action'][] = $action;
            }
        }
        if (0 < count($this->group)) {
            $json['group'] = [];
            foreach ($this->group as $group) {
                $json['group'][] = $group;
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
            $sxe = new \SimpleXMLElement('<ContractTerm xmlns="http://hl7.org/fhir"></ContractTerm>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->identifier)) {
            $this->identifier->xmlSerialize(true, $sxe->addChild('identifier'));
        }
        if (isset($this->issued)) {
            $this->issued->xmlSerialize(true, $sxe->addChild('issued'));
        }
        if (isset($this->applies)) {
            $this->applies->xmlSerialize(true, $sxe->addChild('applies'));
        }
        if (isset($this->topicCodeableConcept)) {
            $this->topicCodeableConcept->xmlSerialize(true, $sxe->addChild('topicCodeableConcept'));
        }
        if (isset($this->topicReference)) {
            $this->topicReference->xmlSerialize(true, $sxe->addChild('topicReference'));
        }
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->subType)) {
            $this->subType->xmlSerialize(true, $sxe->addChild('subType'));
        }
        if (isset($this->text)) {
            $this->text->xmlSerialize(true, $sxe->addChild('text'));
        }
        if (0 < count($this->securityLabel)) {
            foreach ($this->securityLabel as $securityLabel) {
                $securityLabel->xmlSerialize(true, $sxe->addChild('securityLabel'));
            }
        }
        if (isset($this->offer)) {
            $this->offer->xmlSerialize(true, $sxe->addChild('offer'));
        }
        if (0 < count($this->asset)) {
            foreach ($this->asset as $asset) {
                $asset->xmlSerialize(true, $sxe->addChild('asset'));
            }
        }
        if (0 < count($this->action)) {
            foreach ($this->action as $action) {
                $action->xmlSerialize(true, $sxe->addChild('action'));
            }
        }
        if (0 < count($this->group)) {
            foreach ($this->group as $group) {
                $group->xmlSerialize(true, $sxe->addChild('group'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
