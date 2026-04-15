<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConsent;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 *
 * Class creation date: April 15th, 2026 16:02+0000
 *
 * PHPFHIR Copyright:
 *
 * Copyright 2016-2026 Daniel Carbone (daniel.p.carbone@gmail.com)
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
 *   Generated on Fri, Nov 1, 2019 09:29+1100 for FHIR v4.0.1
 *
 *   Note: the schemas & schematrons do not contain all of the rules about what makes resources
 *   valid. Implementers will still need to be familiar with the content of the specification and with
 *   any profiles that apply to the resources in order to make a conformant implementation.
 *
 */
use OpenEMR\FHIR\Encoding\JSONSerializationOptionsTrait;
use OpenEMR\FHIR\Encoding\SerializeConfig;
use OpenEMR\FHIR\Encoding\UnserializeConfig;
use OpenEMR\FHIR\Encoding\ValueXMLLocationEnum;
use OpenEMR\FHIR\Encoding\XMLSerializationOptionsTrait;
use OpenEMR\FHIR\Encoding\XMLWriter;
use OpenEMR\FHIR\Types\ElementTypeInterface;
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRConsentProvisionTypeList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRConsentProvisionType;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * A record of a healthcare consumer’s choices, which permits or denies
 * identified recipient(s) or recipient role(s) to perform one or more actions
 * within a given policy context, for specific purposes and periods of time.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRConsentProvision extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_CONSENT_DOT_PROVISION;

    /* class_default.php:56 */
    public const FIELD_TYPE = 'type';
    public const FIELD_TYPE_EXT = '_type';
    public const FIELD_PERIOD = 'period';
    public const FIELD_ACTOR = 'actor';
    public const FIELD_ACTION = 'action';
    public const FIELD_SECURITY_LABEL = 'securityLabel';
    public const FIELD_PURPOSE = 'purpose';
    public const FIELD_CLASS = 'class';
    public const FIELD_CODE = 'code';
    public const FIELD_DATA_PERIOD = 'dataPeriod';
    public const FIELD_DATA = 'data';
    public const FIELD_PROVISION = 'provision';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_TYPE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * How a rule statement is applied, such as adding additional consent or removing
     * consent.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Action to take - permit or deny - when the rule conditions are met. Not
     * permitted in root rule, required in all nested rules.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRConsentProvisionType
     */
    #[FHIRConsentProvisionType]
    protected FHIRConsentProvisionType $type;
    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The timeframe in this rule is valid.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    #[FHIRPeriod]
    protected FHIRPeriod $period;
    /**
     * A record of a healthcare consumer’s choices, which permits or denies
     * identified recipient(s) or recipient role(s) to perform one or more actions
     * within a given policy context, for specific purposes and periods of time.
     *
     * Who or what is controlled by this rule. Use group to identify a set of actors by
     * some property they share (e.g. 'admitting officers').
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConsent\FHIRConsentActor>
     */
    #[FHIRConsentActor]
    protected array $actor;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Actions controlled by this Rule.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $action;
    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A security label, comprised of 0..* security label fields (Privacy tags), which
     * define which resources are controlled by this exception.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding>
     */
    #[FHIRCoding]
    protected array $securityLabel;
    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The context of the activities a user is taking - why the user is accessing the
     * data - that are controlled by this rule.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding>
     */
    #[FHIRCoding]
    protected array $purpose;
    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The class of information covered by this rule. The type can be a FHIR resource
     * type, a profile on a type, or a CDA document, or some other type that indicates
     * what sort of information the consent relates to.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding>
     */
    #[FHIRCoding]
    protected array $class;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If this code is found in an instance, then the rule applies.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $code;
    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Clinical or Operational Relevant period of time that bounds the data controlled
     * by this rule.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    #[FHIRPeriod]
    protected FHIRPeriod $dataPeriod;
    /**
     * A record of a healthcare consumer’s choices, which permits or denies
     * identified recipient(s) or recipient role(s) to perform one or more actions
     * within a given policy context, for specific purposes and periods of time.
     *
     * The resources controlled by this rule if specific resources are referenced.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConsent\FHIRConsentData>
     */
    #[FHIRConsentData]
    protected array $data;
    /**
     * A record of a healthcare consumer’s choices, which permits or denies
     * identified recipient(s) or recipient role(s) to perform one or more actions
     * within a given policy context, for specific purposes and periods of time.
     *
     * Rules which provide exceptions to the base rule or subrules.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConsent\FHIRConsentProvision>
     */
    #[FHIRConsentProvision]
    protected array $provision;

    /* constructor.php:61 */
    /**
     * FHIRConsentProvision Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRConsentProvisionTypeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRConsentProvisionType $type
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $period
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConsent\FHIRConsentActor> $actor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $action
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding> $securityLabel
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding> $purpose
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding> $class
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $code
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $dataPeriod
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConsent\FHIRConsentData> $data
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConsent\FHIRConsentProvision> $provision
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|string|FHIRConsentProvisionTypeList|FHIRConsentProvisionType $type = null,
                                null|FHIRPeriod $period = null,
                                null|iterable $actor = null,
                                null|iterable $action = null,
                                null|iterable $securityLabel = null,
                                null|iterable $purpose = null,
                                null|iterable $class = null,
                                null|iterable $code = null,
                                null|FHIRPeriod $dataPeriod = null,
                                null|iterable $data = null,
                                null|iterable $provision = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $type) {
            $this->setType($type);
        }
        if (null !== $period) {
            $this->setPeriod($period);
        }
        if (null !== $actor) {
            $this->setActor(...$actor);
        }
        if (null !== $action) {
            $this->setAction(...$action);
        }
        if (null !== $securityLabel) {
            $this->setSecurityLabel(...$securityLabel);
        }
        if (null !== $purpose) {
            $this->setPurpose(...$purpose);
        }
        if (null !== $class) {
            $this->setClass(...$class);
        }
        if (null !== $code) {
            $this->setCode(...$code);
        }
        if (null !== $dataPeriod) {
            $this->setDataPeriod($dataPeriod);
        }
        if (null !== $data) {
            $this->setData(...$data);
        }
        if (null !== $provision) {
            $this->setProvision(...$provision);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * How a rule statement is applied, such as adding additional consent or removing
     * consent.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Action to take - permit or deny - when the rule conditions are met. Not
     * permitted in root rule, required in all nested rules.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRConsentProvisionType
     */
    public function getType(): null|FHIRConsentProvisionType
    {
        return $this->type ?? null;
    }

    /**
     * How a rule statement is applied, such as adding additional consent or removing
     * consent.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Action to take - permit or deny - when the rule conditions are met. Not
     * permitted in root rule, required in all nested rules.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRConsentProvisionTypeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRConsentProvisionType $type
     * @return static
     */
    public function setType(null|string|FHIRConsentProvisionTypeList|FHIRConsentProvisionType $type): self
    {
        if (null === $type) {
            unset($this->type);
            return $this;
        }
        if (!($type instanceof FHIRConsentProvisionType)) {
            $type = new FHIRConsentProvisionType(value: $type);
        }
        $this->type = $type;
        return $this;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The timeframe in this rule is valid.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    public function getPeriod(): null|FHIRPeriod
    {
        return $this->period ?? null;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The timeframe in this rule is valid.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $period
     * @return static
     */
    public function setPeriod(null|FHIRPeriod $period): self
    {
        if (null === $period) {
            unset($this->period);
            return $this;
        }
        $this->period = $period;
        return $this;
    }

    /**
     * A record of a healthcare consumer’s choices, which permits or denies
     * identified recipient(s) or recipient role(s) to perform one or more actions
     * within a given policy context, for specific purposes and periods of time.
     *
     * Who or what is controlled by this rule. Use group to identify a set of actors by
     * some property they share (e.g. 'admitting officers').
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConsent\FHIRConsentActor>
     */
    public function getActor(): array
    {
        return $this->actor ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConsent\FHIRConsentActor>
     */
    public function getActorIterator(): iterable
    {
        if (!isset($this->actor)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->actor);
    }

    /**
     * A record of a healthcare consumer’s choices, which permits or denies
     * identified recipient(s) or recipient role(s) to perform one or more actions
     * within a given policy context, for specific purposes and periods of time.
     *
     * Who or what is controlled by this rule. Use group to identify a set of actors by
     * some property they share (e.g. 'admitting officers').
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConsent\FHIRConsentActor $actor
     * @return static
     */
    public function addActor(FHIRConsentActor $actor): self
    {
        if (!isset($this->actor)) {
            $this->actor = [];
        }
        $this->actor[] = $actor;
        return $this;
    }

    /**
     * A record of a healthcare consumer’s choices, which permits or denies
     * identified recipient(s) or recipient role(s) to perform one or more actions
     * within a given policy context, for specific purposes and periods of time.
     *
     * Who or what is controlled by this rule. Use group to identify a set of actors by
     * some property they share (e.g. 'admitting officers').
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConsent\FHIRConsentActor ...$actor
     * @return static
     */
    public function setActor(FHIRConsentActor ...$actor): self
    {
        if ([] === $actor) {
            unset($this->actor);
            return $this;
        }
        $this->actor = $actor;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Actions controlled by this Rule.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getAction(): array
    {
        return $this->action ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getActionIterator(): iterable
    {
        if (!isset($this->action)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->action);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Actions controlled by this Rule.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $action
     * @return static
     */
    public function addAction(FHIRCodeableConcept $action): self
    {
        if (!isset($this->action)) {
            $this->action = [];
        }
        $this->action[] = $action;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Actions controlled by this Rule.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$action
     * @return static
     */
    public function setAction(FHIRCodeableConcept ...$action): self
    {
        if ([] === $action) {
            unset($this->action);
            return $this;
        }
        $this->action = $action;
        return $this;
    }

    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A security label, comprised of 0..* security label fields (Privacy tags), which
     * define which resources are controlled by this exception.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding>
     */
    public function getSecurityLabel(): array
    {
        return $this->securityLabel ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding>
     */
    public function getSecurityLabelIterator(): iterable
    {
        if (!isset($this->securityLabel)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->securityLabel);
    }

    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A security label, comprised of 0..* security label fields (Privacy tags), which
     * define which resources are controlled by this exception.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding $securityLabel
     * @return static
     */
    public function addSecurityLabel(FHIRCoding $securityLabel): self
    {
        if (!isset($this->securityLabel)) {
            $this->securityLabel = [];
        }
        $this->securityLabel[] = $securityLabel;
        return $this;
    }

    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A security label, comprised of 0..* security label fields (Privacy tags), which
     * define which resources are controlled by this exception.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding ...$securityLabel
     * @return static
     */
    public function setSecurityLabel(FHIRCoding ...$securityLabel): self
    {
        if ([] === $securityLabel) {
            unset($this->securityLabel);
            return $this;
        }
        $this->securityLabel = $securityLabel;
        return $this;
    }

    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The context of the activities a user is taking - why the user is accessing the
     * data - that are controlled by this rule.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding>
     */
    public function getPurpose(): array
    {
        return $this->purpose ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding>
     */
    public function getPurposeIterator(): iterable
    {
        if (!isset($this->purpose)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->purpose);
    }

    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The context of the activities a user is taking - why the user is accessing the
     * data - that are controlled by this rule.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding $purpose
     * @return static
     */
    public function addPurpose(FHIRCoding $purpose): self
    {
        if (!isset($this->purpose)) {
            $this->purpose = [];
        }
        $this->purpose[] = $purpose;
        return $this;
    }

    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The context of the activities a user is taking - why the user is accessing the
     * data - that are controlled by this rule.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding ...$purpose
     * @return static
     */
    public function setPurpose(FHIRCoding ...$purpose): self
    {
        if ([] === $purpose) {
            unset($this->purpose);
            return $this;
        }
        $this->purpose = $purpose;
        return $this;
    }

    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The class of information covered by this rule. The type can be a FHIR resource
     * type, a profile on a type, or a CDA document, or some other type that indicates
     * what sort of information the consent relates to.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding>
     */
    public function getClass(): array
    {
        return $this->class ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding>
     */
    public function getClassIterator(): iterable
    {
        if (!isset($this->class)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->class);
    }

    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The class of information covered by this rule. The type can be a FHIR resource
     * type, a profile on a type, or a CDA document, or some other type that indicates
     * what sort of information the consent relates to.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding $class
     * @return static
     */
    public function addClass(FHIRCoding $class): self
    {
        if (!isset($this->class)) {
            $this->class = [];
        }
        $this->class[] = $class;
        return $this;
    }

    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The class of information covered by this rule. The type can be a FHIR resource
     * type, a profile on a type, or a CDA document, or some other type that indicates
     * what sort of information the consent relates to.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding ...$class
     * @return static
     */
    public function setClass(FHIRCoding ...$class): self
    {
        if ([] === $class) {
            unset($this->class);
            return $this;
        }
        $this->class = $class;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If this code is found in an instance, then the rule applies.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getCode(): array
    {
        return $this->code ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getCodeIterator(): iterable
    {
        if (!isset($this->code)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->code);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If this code is found in an instance, then the rule applies.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $code
     * @return static
     */
    public function addCode(FHIRCodeableConcept $code): self
    {
        if (!isset($this->code)) {
            $this->code = [];
        }
        $this->code[] = $code;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If this code is found in an instance, then the rule applies.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$code
     * @return static
     */
    public function setCode(FHIRCodeableConcept ...$code): self
    {
        if ([] === $code) {
            unset($this->code);
            return $this;
        }
        $this->code = $code;
        return $this;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Clinical or Operational Relevant period of time that bounds the data controlled
     * by this rule.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    public function getDataPeriod(): null|FHIRPeriod
    {
        return $this->dataPeriod ?? null;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Clinical or Operational Relevant period of time that bounds the data controlled
     * by this rule.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $dataPeriod
     * @return static
     */
    public function setDataPeriod(null|FHIRPeriod $dataPeriod): self
    {
        if (null === $dataPeriod) {
            unset($this->dataPeriod);
            return $this;
        }
        $this->dataPeriod = $dataPeriod;
        return $this;
    }

    /**
     * A record of a healthcare consumer’s choices, which permits or denies
     * identified recipient(s) or recipient role(s) to perform one or more actions
     * within a given policy context, for specific purposes and periods of time.
     *
     * The resources controlled by this rule if specific resources are referenced.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConsent\FHIRConsentData>
     */
    public function getData(): array
    {
        return $this->data ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConsent\FHIRConsentData>
     */
    public function getDataIterator(): iterable
    {
        if (!isset($this->data)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->data);
    }

    /**
     * A record of a healthcare consumer’s choices, which permits or denies
     * identified recipient(s) or recipient role(s) to perform one or more actions
     * within a given policy context, for specific purposes and periods of time.
     *
     * The resources controlled by this rule if specific resources are referenced.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConsent\FHIRConsentData $data
     * @return static
     */
    public function addData(FHIRConsentData $data): self
    {
        if (!isset($this->data)) {
            $this->data = [];
        }
        $this->data[] = $data;
        return $this;
    }

    /**
     * A record of a healthcare consumer’s choices, which permits or denies
     * identified recipient(s) or recipient role(s) to perform one or more actions
     * within a given policy context, for specific purposes and periods of time.
     *
     * The resources controlled by this rule if specific resources are referenced.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConsent\FHIRConsentData ...$data
     * @return static
     */
    public function setData(FHIRConsentData ...$data): self
    {
        if ([] === $data) {
            unset($this->data);
            return $this;
        }
        $this->data = $data;
        return $this;
    }

    /**
     * A record of a healthcare consumer’s choices, which permits or denies
     * identified recipient(s) or recipient role(s) to perform one or more actions
     * within a given policy context, for specific purposes and periods of time.
     *
     * Rules which provide exceptions to the base rule or subrules.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConsent\FHIRConsentProvision>
     */
    public function getProvision(): array
    {
        return $this->provision ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConsent\FHIRConsentProvision>
     */
    public function getProvisionIterator(): iterable
    {
        if (!isset($this->provision)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->provision);
    }

    /**
     * A record of a healthcare consumer’s choices, which permits or denies
     * identified recipient(s) or recipient role(s) to perform one or more actions
     * within a given policy context, for specific purposes and periods of time.
     *
     * Rules which provide exceptions to the base rule or subrules.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConsent\FHIRConsentProvision $provision
     * @return static
     */
    public function addProvision(FHIRConsentProvision $provision): self
    {
        if (!isset($this->provision)) {
            $this->provision = [];
        }
        $this->provision[] = $provision;
        return $this;
    }

    /**
     * A record of a healthcare consumer’s choices, which permits or denies
     * identified recipient(s) or recipient role(s) to perform one or more actions
     * within a given policy context, for specific purposes and periods of time.
     *
     * Rules which provide exceptions to the base rule or subrules.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConsent\FHIRConsentProvision ...$provision
     * @return static
     */
    public function setProvision(FHIRConsentProvision ...$provision): self
    {
        if ([] === $provision) {
            unset($this->provision);
            return $this;
        }
        $this->provision = $provision;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConsent\FHIRConsentProvision $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConsent\FHIRConsentProvision
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRConsentProvision)) {
            throw new \RuntimeException(sprintf(
                '%s::xmlUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        foreach ($element->children() as $ce) {
            $cen = $ce->getName();
            if (self::FIELD_EXTENSION === $cen) {
                $type->addExtension(FHIRExtension::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ID === $cen) {
                $va = $ce->attributes()[FHIRStringPrimitive::FIELD_VALUE] ?? null;
                if (null !== $va) {
                    $type->setId((string)$va);
                    $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::ELEMENT_ATTRIBUTE);
                } else {
                    $type->setId((string)$ce);
                    $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::ELEMENT_VALUE);
                }
            } else if (self::FIELD_MODIFIER_EXTENSION === $cen) {
                $type->addModifierExtension(FHIRExtension::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TYPE === $cen) {
                $type->setType(FHIRConsentProvisionType::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PERIOD === $cen) {
                $type->setPeriod(FHIRPeriod::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ACTOR === $cen) {
                $type->addActor(FHIRConsentActor::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ACTION === $cen) {
                $type->addAction(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SECURITY_LABEL === $cen) {
                $type->addSecurityLabel(FHIRCoding::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PURPOSE === $cen) {
                $type->addPurpose(FHIRCoding::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CLASS === $cen) {
                $type->addClass(FHIRCoding::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CODE === $cen) {
                $type->addCode(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DATA_PERIOD === $cen) {
                $type->setDataPeriod(FHIRPeriod::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DATA === $cen) {
                $type->addData(FHIRConsentData::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PROVISION === $cen) {
                $type->addProvision(FHIRConsentProvision::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_TYPE])) {
            if (isset($type->type)) {
                $type->type->setValue((string)$attributes[self::FIELD_TYPE]);
            } else {
                $type->setType((string)$attributes[self::FIELD_TYPE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_TYPE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        return $type;
    }

    /**
     * @param \OpenEMR\FHIR\Encoding\XMLWriter $xw
     * @param \OpenEMR\FHIR\Encoding\SerializeConfig $config
     */
    public function xmlSerialize(XMLWriter $xw,
                                 SerializeConfig $config): void
    {
        if (isset($this->type) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_TYPE]) {
            $xw->writeAttribute(self::FIELD_TYPE, $this->type->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->type)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_TYPE]
                || $this->type->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_TYPE);
            $this->type->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_TYPE]);
            $xw->endElement();
        }
        if (isset($this->period)) {
            $xw->startElement(self::FIELD_PERIOD);
            $this->period->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->actor)) {
            foreach ($this->actor as $v) {
                $xw->startElement(self::FIELD_ACTOR);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->action)) {
            foreach ($this->action as $v) {
                $xw->startElement(self::FIELD_ACTION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->securityLabel)) {
            foreach ($this->securityLabel as $v) {
                $xw->startElement(self::FIELD_SECURITY_LABEL);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->purpose)) {
            foreach ($this->purpose as $v) {
                $xw->startElement(self::FIELD_PURPOSE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->class)) {
            foreach ($this->class as $v) {
                $xw->startElement(self::FIELD_CLASS);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->code)) {
            foreach ($this->code as $v) {
                $xw->startElement(self::FIELD_CODE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->dataPeriod)) {
            $xw->startElement(self::FIELD_DATA_PERIOD);
            $this->dataPeriod->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->data)) {
            foreach ($this->data as $v) {
                $xw->startElement(self::FIELD_DATA);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->provision)) {
            foreach ($this->provision as $v) {
                $xw->startElement(self::FIELD_PROVISION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConsent\FHIRConsentProvision $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConsent\FHIRConsentProvision
     * @throws \Exception
     */
    public static function jsonUnserialize(\stdClass $decoded,
                                           UnserializeConfig $config,
                                           null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            if (isset($decoded->resourceType) && $decoded->resourceType !== static::FHIR_TYPE_NAME) {
                throw new \DomainException(sprintf(
                    '%s::jsonUnserialize - Cannot unmarshal data for resource type "%s" into this type.',
                    ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                    $decoded->resourceType,
                ));
            }
            $type = new static();
        } else if (!($type instanceof FHIRConsentProvision)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->type)
            || isset($decoded->_type)
            || property_exists($decoded, self::FIELD_TYPE)
            || property_exists($decoded, self::FIELD_TYPE_EXT)) {
            $v = $decoded->_type ?? new \stdClass();
            $v->value = $decoded->type ?? null;
            $type->setType(FHIRConsentProvisionType::jsonUnserialize($v, $config));
        }
        if (isset($decoded->period) || property_exists($decoded, self::FIELD_PERIOD)) {
            if (is_array($decoded->period)) {
                $type->setPeriod(FHIRPeriod::jsonUnserialize(reset($decoded->period), $config));
            } else {
                $type->setPeriod(FHIRPeriod::jsonUnserialize($decoded->period, $config));
            }
        }
        if (isset($decoded->actor) || property_exists($decoded, self::FIELD_ACTOR)) {
            if (is_object($decoded->actor)) {
                $vals = [$decoded->actor];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_ACTOR, true);
            } else {
                $vals = $decoded->actor;
            }
            foreach($vals as $v) {
                $type->addActor(FHIRConsentActor::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->action) || property_exists($decoded, self::FIELD_ACTION)) {
            if (is_object($decoded->action)) {
                $vals = [$decoded->action];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_ACTION, true);
            } else {
                $vals = $decoded->action;
            }
            foreach($vals as $v) {
                $type->addAction(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->securityLabel) || property_exists($decoded, self::FIELD_SECURITY_LABEL)) {
            if (is_object($decoded->securityLabel)) {
                $vals = [$decoded->securityLabel];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_SECURITY_LABEL, true);
            } else {
                $vals = $decoded->securityLabel;
            }
            foreach($vals as $v) {
                $type->addSecurityLabel(FHIRCoding::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->purpose) || property_exists($decoded, self::FIELD_PURPOSE)) {
            if (is_object($decoded->purpose)) {
                $vals = [$decoded->purpose];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PURPOSE, true);
            } else {
                $vals = $decoded->purpose;
            }
            foreach($vals as $v) {
                $type->addPurpose(FHIRCoding::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->class) || property_exists($decoded, self::FIELD_CLASS)) {
            if (is_object($decoded->class)) {
                $vals = [$decoded->class];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_CLASS, true);
            } else {
                $vals = $decoded->class;
            }
            foreach($vals as $v) {
                $type->addClass(FHIRCoding::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->code) || property_exists($decoded, self::FIELD_CODE)) {
            if (is_object($decoded->code)) {
                $vals = [$decoded->code];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_CODE, true);
            } else {
                $vals = $decoded->code;
            }
            foreach($vals as $v) {
                $type->addCode(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->dataPeriod) || property_exists($decoded, self::FIELD_DATA_PERIOD)) {
            if (is_array($decoded->dataPeriod)) {
                $type->setDataPeriod(FHIRPeriod::jsonUnserialize(reset($decoded->dataPeriod), $config));
            } else {
                $type->setDataPeriod(FHIRPeriod::jsonUnserialize($decoded->dataPeriod, $config));
            }
        }
        if (isset($decoded->data) || property_exists($decoded, self::FIELD_DATA)) {
            if (is_object($decoded->data)) {
                $vals = [$decoded->data];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_DATA, true);
            } else {
                $vals = $decoded->data;
            }
            foreach($vals as $v) {
                $type->addData(FHIRConsentData::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->provision) || property_exists($decoded, self::FIELD_PROVISION)) {
            if (is_object($decoded->provision)) {
                $vals = [$decoded->provision];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PROVISION, true);
            } else {
                $vals = $decoded->provision;
            }
            foreach($vals as $v) {
                $type->addProvision(FHIRConsentProvision::jsonUnserialize($v, $config));
            }
        }
        return $type;
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize(): mixed
    {
        $out = parent::jsonSerialize();
        if (isset($this->type)) {
            if (null !== ($val = $this->type->getValue())) {
                $out->type = $val;
            }
            if ($this->type->_nonValueFieldDefined()) {
                $ext = $this->type->jsonSerialize();
                unset($ext->value);
                $out->_type = $ext;
            }
        }
        if (isset($this->period)) {
            $out->period = $this->period;
        }
        if (isset($this->actor) && [] !== $this->actor) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_ACTOR) && 1 === count($this->actor)) {
                $out->actor = $this->actor[0];
            } else {
                $out->actor = $this->actor;
            }
        }
        if (isset($this->action) && [] !== $this->action) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_ACTION) && 1 === count($this->action)) {
                $out->action = $this->action[0];
            } else {
                $out->action = $this->action;
            }
        }
        if (isset($this->securityLabel) && [] !== $this->securityLabel) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_SECURITY_LABEL) && 1 === count($this->securityLabel)) {
                $out->securityLabel = $this->securityLabel[0];
            } else {
                $out->securityLabel = $this->securityLabel;
            }
        }
        if (isset($this->purpose) && [] !== $this->purpose) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PURPOSE) && 1 === count($this->purpose)) {
                $out->purpose = $this->purpose[0];
            } else {
                $out->purpose = $this->purpose;
            }
        }
        if (isset($this->class) && [] !== $this->class) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_CLASS) && 1 === count($this->class)) {
                $out->class = $this->class[0];
            } else {
                $out->class = $this->class;
            }
        }
        if (isset($this->code) && [] !== $this->code) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_CODE) && 1 === count($this->code)) {
                $out->code = $this->code[0];
            } else {
                $out->code = $this->code;
            }
        }
        if (isset($this->dataPeriod)) {
            $out->dataPeriod = $this->dataPeriod;
        }
        if (isset($this->data) && [] !== $this->data) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_DATA) && 1 === count($this->data)) {
                $out->data = $this->data[0];
            } else {
                $out->data = $this->data;
            }
        }
        if (isset($this->provision) && [] !== $this->provision) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PROVISION) && 1 === count($this->provision)) {
                $out->provision = $this->provision[0];
            } else {
                $out->provision = $this->provision;
            }
        }
        return $out;
    }
}
