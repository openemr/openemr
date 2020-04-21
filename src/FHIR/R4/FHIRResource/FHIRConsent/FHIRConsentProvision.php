<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRConsent;

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
 * A record of a healthcare consumer’s  choices, which permits or denies identified recipient(s) or recipient role(s) to perform one or more actions within a given policy context, for specific purposes and periods of time.
 */
class FHIRConsentProvision extends FHIRBackboneElement implements \JsonSerializable
{
    /**
     * Action  to take - permit or deny - when the rule conditions are met.  Not permitted in root rule, required in all nested rules.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRConsentProvisionType
     */
    public $type = null;

    /**
     * The timeframe in this rule is valid.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public $period = null;

    /**
     * Who or what is controlled by this rule. Use group to identify a set of actors by some property they share (e.g. 'admitting officers').
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRConsent\FHIRConsentActor[]
     */
    public $actor = [];

    /**
     * Actions controlled by this Rule.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $action = [];

    /**
     * A security label, comprised of 0..* security label fields (Privacy tags), which define which resources are controlled by this exception.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding[]
     */
    public $securityLabel = [];

    /**
     * The context of the activities a user is taking - why the user is accessing the data - that are controlled by this rule.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding[]
     */
    public $purpose = [];

    /**
     * The class of information covered by this rule. The type can be a FHIR resource type, a profile on a type, or a CDA document, or some other type that indicates what sort of information the consent relates to.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding[]
     */
    public $class = [];

    /**
     * If this code is found in an instance, then the rule applies.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public $code = [];

    /**
     * Clinical or Operational Relevant period of time that bounds the data controlled by this rule.
     * @var \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public $dataPeriod = null;

    /**
     * The resources controlled by this rule if specific resources are referenced.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRConsent\FHIRConsentData[]
     */
    public $data = [];

    /**
     * Rules which provide exceptions to the base rule or subrules.
     * @var \OpenEMR\FHIR\R4\FHIRResource\FHIRConsent\FHIRConsentProvision[]
     */
    public $provision = [];

    /**
     * @var string
     */
    private $_fhirElementName = 'Consent.Provision';

    /**
     * Action  to take - permit or deny - when the rule conditions are met.  Not permitted in root rule, required in all nested rules.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRConsentProvisionType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Action  to take - permit or deny - when the rule conditions are met.  Not permitted in root rule, required in all nested rules.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRConsentProvisionType $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * The timeframe in this rule is valid.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * The timeframe in this rule is valid.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod $period
     * @return $this
     */
    public function setPeriod($period)
    {
        $this->period = $period;
        return $this;
    }

    /**
     * Who or what is controlled by this rule. Use group to identify a set of actors by some property they share (e.g. 'admitting officers').
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRConsent\FHIRConsentActor[]
     */
    public function getActor()
    {
        return $this->actor;
    }

    /**
     * Who or what is controlled by this rule. Use group to identify a set of actors by some property they share (e.g. 'admitting officers').
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRConsent\FHIRConsentActor $actor
     * @return $this
     */
    public function addActor($actor)
    {
        $this->actor[] = $actor;
        return $this;
    }

    /**
     * Actions controlled by this Rule.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Actions controlled by this Rule.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $action
     * @return $this
     */
    public function addAction($action)
    {
        $this->action[] = $action;
        return $this;
    }

    /**
     * A security label, comprised of 0..* security label fields (Privacy tags), which define which resources are controlled by this exception.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding[]
     */
    public function getSecurityLabel()
    {
        return $this->securityLabel;
    }

    /**
     * A security label, comprised of 0..* security label fields (Privacy tags), which define which resources are controlled by this exception.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding $securityLabel
     * @return $this
     */
    public function addSecurityLabel($securityLabel)
    {
        $this->securityLabel[] = $securityLabel;
        return $this;
    }

    /**
     * The context of the activities a user is taking - why the user is accessing the data - that are controlled by this rule.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding[]
     */
    public function getPurpose()
    {
        return $this->purpose;
    }

    /**
     * The context of the activities a user is taking - why the user is accessing the data - that are controlled by this rule.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding $purpose
     * @return $this
     */
    public function addPurpose($purpose)
    {
        $this->purpose[] = $purpose;
        return $this;
    }

    /**
     * The class of information covered by this rule. The type can be a FHIR resource type, a profile on a type, or a CDA document, or some other type that indicates what sort of information the consent relates to.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding[]
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * The class of information covered by this rule. The type can be a FHIR resource type, a profile on a type, or a CDA document, or some other type that indicates what sort of information the consent relates to.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCoding $class
     * @return $this
     */
    public function addClass($class)
    {
        $this->class[] = $class;
        return $this;
    }

    /**
     * If this code is found in an instance, then the rule applies.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept[]
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * If this code is found in an instance, then the rule applies.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $code
     * @return $this
     */
    public function addCode($code)
    {
        $this->code[] = $code;
        return $this;
    }

    /**
     * Clinical or Operational Relevant period of time that bounds the data controlled by this rule.
     * @return \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod
     */
    public function getDataPeriod()
    {
        return $this->dataPeriod;
    }

    /**
     * Clinical or Operational Relevant period of time that bounds the data controlled by this rule.
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod $dataPeriod
     * @return $this
     */
    public function setDataPeriod($dataPeriod)
    {
        $this->dataPeriod = $dataPeriod;
        return $this;
    }

    /**
     * The resources controlled by this rule if specific resources are referenced.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRConsent\FHIRConsentData[]
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * The resources controlled by this rule if specific resources are referenced.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRConsent\FHIRConsentData $data
     * @return $this
     */
    public function addData($data)
    {
        $this->data[] = $data;
        return $this;
    }

    /**
     * Rules which provide exceptions to the base rule or subrules.
     * @return \OpenEMR\FHIR\R4\FHIRResource\FHIRConsent\FHIRConsentProvision[]
     */
    public function getProvision()
    {
        return $this->provision;
    }

    /**
     * Rules which provide exceptions to the base rule or subrules.
     * @param \OpenEMR\FHIR\R4\FHIRResource\FHIRConsent\FHIRConsentProvision $provision
     * @return $this
     */
    public function addProvision($provision)
    {
        $this->provision[] = $provision;
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
            if (isset($data['type'])) {
                $this->setType($data['type']);
            }
            if (isset($data['period'])) {
                $this->setPeriod($data['period']);
            }
            if (isset($data['actor'])) {
                if (is_array($data['actor'])) {
                    foreach ($data['actor'] as $d) {
                        $this->addActor($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"actor" must be array of objects or null, ' . gettype($data['actor']) . ' seen.');
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
            if (isset($data['securityLabel'])) {
                if (is_array($data['securityLabel'])) {
                    foreach ($data['securityLabel'] as $d) {
                        $this->addSecurityLabel($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"securityLabel" must be array of objects or null, ' . gettype($data['securityLabel']) . ' seen.');
                }
            }
            if (isset($data['purpose'])) {
                if (is_array($data['purpose'])) {
                    foreach ($data['purpose'] as $d) {
                        $this->addPurpose($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"purpose" must be array of objects or null, ' . gettype($data['purpose']) . ' seen.');
                }
            }
            if (isset($data['class'])) {
                if (is_array($data['class'])) {
                    foreach ($data['class'] as $d) {
                        $this->addClass($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"class" must be array of objects or null, ' . gettype($data['class']) . ' seen.');
                }
            }
            if (isset($data['code'])) {
                if (is_array($data['code'])) {
                    foreach ($data['code'] as $d) {
                        $this->addCode($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"code" must be array of objects or null, ' . gettype($data['code']) . ' seen.');
                }
            }
            if (isset($data['dataPeriod'])) {
                $this->setDataPeriod($data['dataPeriod']);
            }
            if (isset($data['data'])) {
                if (is_array($data['data'])) {
                    foreach ($data['data'] as $d) {
                        $this->addData($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"data" must be array of objects or null, ' . gettype($data['data']) . ' seen.');
                }
            }
            if (isset($data['provision'])) {
                if (is_array($data['provision'])) {
                    foreach ($data['provision'] as $d) {
                        $this->addProvision($d);
                    }
                } else {
                    throw new \InvalidArgumentException('"provision" must be array of objects or null, ' . gettype($data['provision']) . ' seen.');
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
        if (isset($this->type)) {
            $json['type'] = $this->type;
        }
        if (isset($this->period)) {
            $json['period'] = $this->period;
        }
        if (0 < count($this->actor)) {
            $json['actor'] = [];
            foreach ($this->actor as $actor) {
                $json['actor'][] = $actor;
            }
        }
        if (0 < count($this->action)) {
            $json['action'] = [];
            foreach ($this->action as $action) {
                $json['action'][] = $action;
            }
        }
        if (0 < count($this->securityLabel)) {
            $json['securityLabel'] = [];
            foreach ($this->securityLabel as $securityLabel) {
                $json['securityLabel'][] = $securityLabel;
            }
        }
        if (0 < count($this->purpose)) {
            $json['purpose'] = [];
            foreach ($this->purpose as $purpose) {
                $json['purpose'][] = $purpose;
            }
        }
        if (0 < count($this->class)) {
            $json['class'] = [];
            foreach ($this->class as $class) {
                $json['class'][] = $class;
            }
        }
        if (0 < count($this->code)) {
            $json['code'] = [];
            foreach ($this->code as $code) {
                $json['code'][] = $code;
            }
        }
        if (isset($this->dataPeriod)) {
            $json['dataPeriod'] = $this->dataPeriod;
        }
        if (0 < count($this->data)) {
            $json['data'] = [];
            foreach ($this->data as $data) {
                $json['data'][] = $data;
            }
        }
        if (0 < count($this->provision)) {
            $json['provision'] = [];
            foreach ($this->provision as $provision) {
                $json['provision'][] = $provision;
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
            $sxe = new \SimpleXMLElement('<ConsentProvision xmlns="http://hl7.org/fhir"></ConsentProvision>');
        }
        parent::xmlSerialize(true, $sxe);
        if (isset($this->type)) {
            $this->type->xmlSerialize(true, $sxe->addChild('type'));
        }
        if (isset($this->period)) {
            $this->period->xmlSerialize(true, $sxe->addChild('period'));
        }
        if (0 < count($this->actor)) {
            foreach ($this->actor as $actor) {
                $actor->xmlSerialize(true, $sxe->addChild('actor'));
            }
        }
        if (0 < count($this->action)) {
            foreach ($this->action as $action) {
                $action->xmlSerialize(true, $sxe->addChild('action'));
            }
        }
        if (0 < count($this->securityLabel)) {
            foreach ($this->securityLabel as $securityLabel) {
                $securityLabel->xmlSerialize(true, $sxe->addChild('securityLabel'));
            }
        }
        if (0 < count($this->purpose)) {
            foreach ($this->purpose as $purpose) {
                $purpose->xmlSerialize(true, $sxe->addChild('purpose'));
            }
        }
        if (0 < count($this->class)) {
            foreach ($this->class as $class) {
                $class->xmlSerialize(true, $sxe->addChild('class'));
            }
        }
        if (0 < count($this->code)) {
            foreach ($this->code as $code) {
                $code->xmlSerialize(true, $sxe->addChild('code'));
            }
        }
        if (isset($this->dataPeriod)) {
            $this->dataPeriod->xmlSerialize(true, $sxe->addChild('dataPeriod'));
        }
        if (0 < count($this->data)) {
            foreach ($this->data as $data) {
                $data->xmlSerialize(true, $sxe->addChild('data'));
            }
        }
        if (0 < count($this->provision)) {
            foreach ($this->provision as $provision) {
                $provision->xmlSerialize(true, $sxe->addChild('provision'));
            }
        }
        if ($returnSXE) {
            return $sxe;
        }
        return $sxe->saveXML();
    }
}
