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
class FHIRContractAsset extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Differentiates the kind of the asset .
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public $scope = null;

    /**
     * Target entity type about which the term may be concerned.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $type = [];

    /**
     * Associated entities.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public $typeReference = [];

    /**
     * May be a subtype or part of an offered asset.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $subtype = [];

    /**
     * Specifies the applicability of the term to an asset resource instance, and instances it refers to orinstances that refer to it, and/or are owned by the offeree.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding
     */
    public $relationship = null;

    /**
     * Circumstance of the asset.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRContract\FHIRContractContext[]
     */
    public $context = [];

    /**
     * Description of the quality and completeness of the asset that imay be a factor in its valuation.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $condition = null;

    /**
     * Type of Asset availability for use or ownership.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $periodType = [];

    /**
     * Asset relevant contractual time period.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod[]
     */
    public $period = [];

    /**
     * Time period of asset use.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod[]
     */
    public $usePeriod = [];

    /**
     * Clause or question text (Prose Object) concerning the asset in a linked form, such as a QuestionnaireResponse used in the formation of the contract.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public $text = null;

    /**
     * Id [identifier??] of the clause or question text about the asset in the referenced form or QuestionnaireResponse.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public $linkId = [];

    /**
     * Response to assets.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRContract\FHIRContractAnswer[]
     */
    public $answer = [];

    /**
     * Security labels that protects the asset.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt[]
     */
    public $securityLabelNumber = [];

    /**
     * Contract Valued Item List.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRContract\FHIRContractValuedItem[]
     */
    public $valuedItem = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'Contract.Asset';

    /**
     * Differentiates the kind of the asset .
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Differentiates the kind of the asset .
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $scope
     * @return $this
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
        return $this;
    }

    /**
     * Target entity type about which the term may be concerned.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Target entity type about which the term may be concerned.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $type
     * @return $this
     */
    public function addType($type)
    {
        $this->type[] = $type;
        return $this;
    }

    /**
     * Associated entities.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRReference[]
     */
    public function getTypeReference()
    {
        return $this->typeReference;
    }

    /**
     * Associated entities.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRReference $typeReference
     * @return $this
     */
    public function addTypeReference($typeReference)
    {
        $this->typeReference[] = $typeReference;
        return $this;
    }

    /**
     * May be a subtype or part of an offered asset.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getSubtype()
    {
        return $this->subtype;
    }

    /**
     * May be a subtype or part of an offered asset.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $subtype
     * @return $this
     */
    public function addSubtype($subtype)
    {
        $this->subtype[] = $subtype;
        return $this;
    }

    /**
     * Specifies the applicability of the term to an asset resource instance, and instances it refers to orinstances that refer to it, and/or are owned by the offeree.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding
     */
    public function getRelationship()
    {
        return $this->relationship;
    }

    /**
     * Specifies the applicability of the term to an asset resource instance, and instances it refers to orinstances that refer to it, and/or are owned by the offeree.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding $relationship
     * @return $this
     */
    public function setRelationship($relationship)
    {
        $this->relationship = $relationship;
        return $this;
    }

    /**
     * Circumstance of the asset.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRContract\FHIRContractContext[]
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Circumstance of the asset.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRContract\FHIRContractContext $context
     * @return $this
     */
    public function addContext($context)
    {
        $this->context[] = $context;
        return $this;
    }

    /**
     * Description of the quality and completeness of the asset that imay be a factor in its valuation.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * Description of the quality and completeness of the asset that imay be a factor in its valuation.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $condition
     * @return $this
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;
        return $this;
    }

    /**
     * Type of Asset availability for use or ownership.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getPeriodType()
    {
        return $this->periodType;
    }

    /**
     * Type of Asset availability for use or ownership.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $periodType
     * @return $this
     */
    public function addPeriodType($periodType)
    {
        $this->periodType[] = $periodType;
        return $this;
    }

    /**
     * Asset relevant contractual time period.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod[]
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * Asset relevant contractual time period.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod $period
     * @return $this
     */
    public function addPeriod($period)
    {
        $this->period[] = $period;
        return $this;
    }

    /**
     * Time period of asset use.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod[]
     */
    public function getUsePeriod()
    {
        return $this->usePeriod;
    }

    /**
     * Time period of asset use.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod $usePeriod
     * @return $this
     */
    public function addUsePeriod($usePeriod)
    {
        $this->usePeriod[] = $usePeriod;
        return $this;
    }

    /**
     * Clause or question text (Prose Object) concerning the asset in a linked form, such as a QuestionnaireResponse used in the formation of the contract.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Clause or question text (Prose Object) concerning the asset in a linked form, such as a QuestionnaireResponse used in the formation of the contract.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $text
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    /**
     * Id [identifier??] of the clause or question text about the asset in the referenced form or QuestionnaireResponse.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public function getLinkId()
    {
        return $this->linkId;
    }

    /**
     * Id [identifier??] of the clause or question text about the asset in the referenced form or QuestionnaireResponse.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString $linkId
     * @return $this
     */
    public function addLinkId($linkId)
    {
        $this->linkId[] = $linkId;
        return $this;
    }

    /**
     * Response to assets.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRContract\FHIRContractAnswer[]
     */
    public function getAnswer()
    {
        return $this->answer;
    }

    /**
     * Response to assets.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRContract\FHIRContractAnswer $answer
     * @return $this
     */
    public function addAnswer($answer)
    {
        $this->answer[] = $answer;
        return $this;
    }

    /**
     * Security labels that protects the asset.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt[]
     */
    public function getSecurityLabelNumber()
    {
        return $this->securityLabelNumber;
    }

    /**
     * Security labels that protects the asset.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRUnsignedInt $securityLabelNumber
     * @return $this
     */
    public function addSecurityLabelNumber($securityLabelNumber)
    {
        $this->securityLabelNumber[] = $securityLabelNumber;
        return $this;
    }

    /**
     * Contract Valued Item List.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRContract\FHIRContractValuedItem[]
     */
    public function getValuedItem()
    {
        return $this->valuedItem;
    }

    /**
     * Contract Valued Item List.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRContract\FHIRContractValuedItem $valuedItem
     * @return $this
     */
    public function addValuedItem($valuedItem)
    {
        $this->valuedItem[] = $valuedItem;
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
            if (isset($data['scope'])) {
                $this->setScope($data['scope']);
            }
            if (isset($data['type'])) {
                if (is_array($data['type'])) {
                    foreach ($data['type'] as $d) {
                        $this->addType($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"type" must be array of objects or null, ' . gettype($data['type']) . ' seen.');
                }
            }
            if (isset($data['typeReference'])) {
                if (is_array($data['typeReference'])) {
                    foreach ($data['typeReference'] as $d) {
                        $this->addTypeReference($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"typeReference" must be array of objects or null, ' . gettype($data['typeReference']) . ' seen.');
                }
            }
            if (isset($data['subtype'])) {
                if (is_array($data['subtype'])) {
                    foreach ($data['subtype'] as $d) {
                        $this->addSubtype($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"subtype" must be array of objects or null, ' . gettype($data['subtype']) . ' seen.');
                }
            }
            if (isset($data['relationship'])) {
                $this->setRelationship($data['relationship']);
            }
            if (isset($data['context'])) {
                if (is_array($data['context'])) {
                    foreach ($data['context'] as $d) {
                        $this->addContext($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"context" must be array of objects or null, ' . gettype($data['context']) . ' seen.');
                }
            }
            if (isset($data['condition'])) {
                $this->setCondition($data['condition']);
            }
            if (isset($data['periodType'])) {
                if (is_array($data['periodType'])) {
                    foreach ($data['periodType'] as $d) {
                        $this->addPeriodType($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"periodType" must be array of objects or null, ' . gettype($data['periodType']) . ' seen.');
                }
            }
            if (isset($data['period'])) {
                if (is_array($data['period'])) {
                    foreach ($data['period'] as $d) {
                        $this->addPeriod($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"period" must be array of objects or null, ' . gettype($data['period']) . ' seen.');
                }
            }
            if (isset($data['usePeriod'])) {
                if (is_array($data['usePeriod'])) {
                    foreach ($data['usePeriod'] as $d) {
                        $this->addUsePeriod($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"usePeriod" must be array of objects or null, ' . gettype($data['usePeriod']) . ' seen.');
                }
            }
            if (isset($data['text'])) {
                $this->setText($data['text']);
            }
            if (isset($data['linkId'])) {
                if (is_array($data['linkId'])) {
                    foreach ($data['linkId'] as $d) {
                        $this->addLinkId($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"linkId" must be array of objects or null, ' . gettype($data['linkId']) . ' seen.');
                }
            }
            if (isset($data['answer'])) {
                if (is_array($data['answer'])) {
                    foreach ($data['answer'] as $d) {
                        $this->addAnswer($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"answer" must be array of objects or null, ' . gettype($data['answer']) . ' seen.');
                }
            }
            if (isset($data['securityLabelNumber'])) {
                if (is_array($data['securityLabelNumber'])) {
                    foreach ($data['securityLabelNumber'] as $d) {
                        $this->addSecurityLabelNumber($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"securityLabelNumber" must be array of objects or null, ' . gettype($data['securityLabelNumber']) . ' seen.');
                }
            }
            if (isset($data['valuedItem'])) {
                if (is_array($data['valuedItem'])) {
                    foreach ($data['valuedItem'] as $d) {
                        $this->addValuedItem($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"valuedItem" must be array of objects or null, ' . gettype($data['valuedItem']) . ' seen.');
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
        if (isset($this->scope)) {
            $json['scope'] = $this->scope;
        }
        if (0 < count($this->type)) {
            $json['type'] = [];
            foreach ($this->type as $type) {
                $json['type'][] = $type;
            }
        }
        if (0 < count($this->typeReference)) {
            $json['typeReference'] = [];
            foreach ($this->typeReference as $typeReference) {
                $json['typeReference'][] = $typeReference;
            }
        }
        if (0 < count($this->subtype)) {
            $json['subtype'] = [];
            foreach ($this->subtype as $subtype) {
                $json['subtype'][] = $subtype;
            }
        }
        if (isset($this->relationship)) {
            $json['relationship'] = $this->relationship;
        }
        if (0 < count($this->context)) {
            $json['context'] = [];
            foreach ($this->context as $context) {
                $json['context'][] = $context;
            }
        }
        if (isset($this->condition)) {
            $json['condition'] = $this->condition;
        }
        if (0 < count($this->periodType)) {
            $json['periodType'] = [];
            foreach ($this->periodType as $periodType) {
                $json['periodType'][] = $periodType;
            }
        }
        if (0 < count($this->period)) {
            $json['period'] = [];
            foreach ($this->period as $period) {
                $json['period'][] = $period;
            }
        }
        if (0 < count($this->usePeriod)) {
            $json['usePeriod'] = [];
            foreach ($this->usePeriod as $usePeriod) {
                $json['usePeriod'][] = $usePeriod;
            }
        }
        if (isset($this->text)) {
            $json['text'] = $this->text;
        }
        if (0 < count($this->linkId)) {
            $json['linkId'] = [];
            foreach ($this->linkId as $linkId) {
                $json['linkId'][] = $linkId;
            }
        }
        if (0 < count($this->answer)) {
            $json['answer'] = [];
            foreach ($this->answer as $answer) {
                $json['answer'][] = $answer;
            }
        }
        if (0 < count($this->securityLabelNumber)) {
            $json['securityLabelNumber'] = [];
            foreach ($this->securityLabelNumber as $securityLabelNumber) {
                $json['securityLabelNumber'][] = $securityLabelNumber;
            }
        }
        if (0 < count($this->valuedItem)) {
            $json['valuedItem'] = [];
            foreach ($this->valuedItem as $valuedItem) {
                $json['valuedItem'][] = $valuedItem;
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
            $sxe = new \SimpleXMLElement('<ContractAsset xmlns="http://hl7.org/fhir"></ContractAsset>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->scope)) {
            $this->scope->xmlSerialize(true, $sxe->addChild('scope'));
        }
        if (0 < count($this->type)) {
            foreach ($this->type as $type) {
                $type->xmlSerialize(true, $sxe->addChild('type'));
            }
        }
        if (0 < count($this->typeReference)) {
            foreach ($this->typeReference as $typeReference) {
                $typeReference->xmlSerialize(true, $sxe->addChild('typeReference'));
            }
        }
        if (0 < count($this->subtype)) {
            foreach ($this->subtype as $subtype) {
                $subtype->xmlSerialize(true, $sxe->addChild('subtype'));
            }
        }
        if (isset($this->relationship)) {
            $this->relationship->xmlSerialize(true, $sxe->addChild('relationship'));
        }
        if (0 < count($this->context)) {
            foreach ($this->context as $context) {
                $context->xmlSerialize(true, $sxe->addChild('context'));
            }
        }
        if (isset($this->condition)) {
            $this->condition->xmlSerialize(true, $sxe->addChild('condition'));
        }
        if (0 < count($this->periodType)) {
            foreach ($this->periodType as $periodType) {
                $periodType->xmlSerialize(true, $sxe->addChild('periodType'));
            }
        }
        if (0 < count($this->period)) {
            foreach ($this->period as $period) {
                $period->xmlSerialize(true, $sxe->addChild('period'));
            }
        }
        if (0 < count($this->usePeriod)) {
            foreach ($this->usePeriod as $usePeriod) {
                $usePeriod->xmlSerialize(true, $sxe->addChild('usePeriod'));
            }
        }
        if (isset($this->text)) {
            $this->text->xmlSerialize(true, $sxe->addChild('text'));
        }
        if (0 < count($this->linkId)) {
            foreach ($this->linkId as $linkId) {
                $linkId->xmlSerialize(true, $sxe->addChild('linkId'));
            }
        }
        if (0 < count($this->answer)) {
            foreach ($this->answer as $answer) {
                $answer->xmlSerialize(true, $sxe->addChild('answer'));
            }
        }
        if (0 < count($this->securityLabelNumber)) {
            foreach ($this->securityLabelNumber as $securityLabelNumber) {
                $securityLabelNumber->xmlSerialize(true, $sxe->addChild('securityLabelNumber'));
            }
        }
        if (0 < count($this->valuedItem)) {
            foreach ($this->valuedItem as $valuedItem) {
                $valuedItem->xmlSerialize(true, $sxe->addChild('valuedItem'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
