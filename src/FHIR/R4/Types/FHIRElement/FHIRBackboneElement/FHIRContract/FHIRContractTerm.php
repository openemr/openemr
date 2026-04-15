<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract;

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
use OpenEMR\FHIR\Validation\Rules\MinOccursRule;
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
 * policy or agreement.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRContractTerm extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_CONTRACT_DOT_TERM;

    /* class_default.php:56 */
    public const FIELD_IDENTIFIER = 'identifier';
    public const FIELD_ISSUED = 'issued';
    public const FIELD_ISSUED_EXT = '_issued';
    public const FIELD_APPLIES = 'applies';
    public const FIELD_TOPIC_CODEABLE_CONCEPT = 'topicCodeableConcept';
    public const FIELD_TOPIC_REFERENCE = 'topicReference';
    public const FIELD_TYPE = 'type';
    public const FIELD_SUB_TYPE = 'subType';
    public const FIELD_TEXT = 'text';
    public const FIELD_TEXT_EXT = '_text';
    public const FIELD_SECURITY_LABEL = 'securityLabel';
    public const FIELD_OFFER = 'offer';
    public const FIELD_ASSET = 'asset';
    public const FIELD_ACTION = 'action';
    public const FIELD_GROUP = 'group';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_OFFER => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_ISSUED => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_TEXT => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Unique identifier for this particular Contract Provision.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier
     */
    #[FHIRIdentifier]
    protected FHIRIdentifier $identifier;
    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * When this Contract Provision was issued.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $issued;
    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Relevant time or time-period when this Contract Provision is applicable.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    #[FHIRPeriod]
    protected FHIRPeriod $applies;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The entity that the term applies to. (choose any one of topic*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $topicCodeableConcept;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The entity that the term applies to. (choose any one of topic*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $topicReference;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A legal clause or condition contained within a contract that requires one or
     * both parties to perform a particular requirement by some specified time or
     * prevents one or both parties from performing a particular requirement by some
     * specified time.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $type;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A specialized legal clause or condition based on overarching contract type.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $subType;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Statement of a provision in a policy or a contract.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $text;
    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * Security labels that protect the handling of information about the term and its
     * elements, which may be specifically identified..
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractSecurityLabel>
     */
    #[FHIRContractSecurityLabel]
    protected array $securityLabel;
    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * The matter of concern in the context of this provision of the agrement.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractOffer
     */
    #[FHIRContractOffer]
    protected FHIRContractOffer $offer;
    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * Contract Term Asset List.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractAsset>
     */
    #[FHIRContractAsset]
    protected array $asset;
    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * An actor taking a role in an activity for which it can be assigned some degree
     * of responsibility for the activity taking place.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractAction>
     */
    #[FHIRContractAction]
    protected array $action;
    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * Nested group of Contract Provisions.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractTerm>
     */
    #[FHIRContractTerm]
    protected array $group;

    /* constructor.php:61 */
    /**
     * FHIRContractTerm Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $identifier
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $issued
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $applies
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $topicCodeableConcept
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $topicReference
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $type
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $subType
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $text
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractSecurityLabel> $securityLabel
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractOffer $offer
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractAsset> $asset
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractAction> $action
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractTerm> $group
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|FHIRIdentifier $identifier = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $issued = null,
                                null|FHIRPeriod $applies = null,
                                null|FHIRCodeableConcept $topicCodeableConcept = null,
                                null|FHIRReference $topicReference = null,
                                null|FHIRCodeableConcept $type = null,
                                null|FHIRCodeableConcept $subType = null,
                                null|string|FHIRStringPrimitive|FHIRString $text = null,
                                null|iterable $securityLabel = null,
                                null|FHIRContractOffer $offer = null,
                                null|iterable $asset = null,
                                null|iterable $action = null,
                                null|iterable $group = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $identifier) {
            $this->setIdentifier($identifier);
        }
        if (null !== $issued) {
            $this->setIssued($issued);
        }
        if (null !== $applies) {
            $this->setApplies($applies);
        }
        if (null !== $topicCodeableConcept) {
            $this->setTopicCodeableConcept($topicCodeableConcept);
        }
        if (null !== $topicReference) {
            $this->setTopicReference($topicReference);
        }
        if (null !== $type) {
            $this->setType($type);
        }
        if (null !== $subType) {
            $this->setSubType($subType);
        }
        if (null !== $text) {
            $this->setText($text);
        }
        if (null !== $securityLabel) {
            $this->setSecurityLabel(...$securityLabel);
        }
        if (null !== $offer) {
            $this->setOffer($offer);
        }
        if (null !== $asset) {
            $this->setAsset(...$asset);
        }
        if (null !== $action) {
            $this->setAction(...$action);
        }
        if (null !== $group) {
            $this->setGroup(...$group);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Unique identifier for this particular Contract Provision.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier
     */
    public function getIdentifier(): null|FHIRIdentifier
    {
        return $this->identifier ?? null;
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Unique identifier for this particular Contract Provision.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $identifier
     * @return static
     */
    public function setIdentifier(null|FHIRIdentifier $identifier): self
    {
        if (null === $identifier) {
            unset($this->identifier);
            return $this;
        }
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * When this Contract Provision was issued.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    public function getIssued(): null|FHIRDateTime
    {
        return $this->issued ?? null;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * When this Contract Provision was issued.
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $issued
     * @return static
     */
    public function setIssued(null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $issued): self
    {
        if (null === $issued) {
            unset($this->issued);
            return $this;
        }
        if (!($issued instanceof FHIRDateTime)) {
            $issued = new FHIRDateTime(value: $issued);
        }
        $this->issued = $issued;
        return $this;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Relevant time or time-period when this Contract Provision is applicable.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    public function getApplies(): null|FHIRPeriod
    {
        return $this->applies ?? null;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Relevant time or time-period when this Contract Provision is applicable.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $applies
     * @return static
     */
    public function setApplies(null|FHIRPeriod $applies): self
    {
        if (null === $applies) {
            unset($this->applies);
            return $this;
        }
        $this->applies = $applies;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The entity that the term applies to. (choose any one of topic*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getTopicCodeableConcept(): null|FHIRCodeableConcept
    {
        return $this->topicCodeableConcept ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The entity that the term applies to. (choose any one of topic*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $topicCodeableConcept
     * @return static
     */
    public function setTopicCodeableConcept(null|FHIRCodeableConcept $topicCodeableConcept): self
    {
        if (null === $topicCodeableConcept) {
            unset($this->topicCodeableConcept);
            return $this;
        }
        $this->topicCodeableConcept = $topicCodeableConcept;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The entity that the term applies to. (choose any one of topic*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getTopicReference(): null|FHIRReference
    {
        return $this->topicReference ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The entity that the term applies to. (choose any one of topic*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $topicReference
     * @return static
     */
    public function setTopicReference(null|FHIRReference $topicReference): self
    {
        if (null === $topicReference) {
            unset($this->topicReference);
            return $this;
        }
        $this->topicReference = $topicReference;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A legal clause or condition contained within a contract that requires one or
     * both parties to perform a particular requirement by some specified time or
     * prevents one or both parties from performing a particular requirement by some
     * specified time.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getType(): null|FHIRCodeableConcept
    {
        return $this->type ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A legal clause or condition contained within a contract that requires one or
     * both parties to perform a particular requirement by some specified time or
     * prevents one or both parties from performing a particular requirement by some
     * specified time.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $type
     * @return static
     */
    public function setType(null|FHIRCodeableConcept $type): self
    {
        if (null === $type) {
            unset($this->type);
            return $this;
        }
        $this->type = $type;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A specialized legal clause or condition based on overarching contract type.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getSubType(): null|FHIRCodeableConcept
    {
        return $this->subType ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A specialized legal clause or condition based on overarching contract type.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $subType
     * @return static
     */
    public function setSubType(null|FHIRCodeableConcept $subType): self
    {
        if (null === $subType) {
            unset($this->subType);
            return $this;
        }
        $this->subType = $subType;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Statement of a provision in a policy or a contract.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getText(): null|FHIRString
    {
        return $this->text ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Statement of a provision in a policy or a contract.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $text
     * @return static
     */
    public function setText(null|string|FHIRStringPrimitive|FHIRString $text): self
    {
        if (null === $text) {
            unset($this->text);
            return $this;
        }
        if (!($text instanceof FHIRString)) {
            $text = new FHIRString(value: $text);
        }
        $this->text = $text;
        return $this;
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * Security labels that protect the handling of information about the term and its
     * elements, which may be specifically identified..
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractSecurityLabel>
     */
    public function getSecurityLabel(): array
    {
        return $this->securityLabel ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractSecurityLabel>
     */
    public function getSecurityLabelIterator(): iterable
    {
        if (!isset($this->securityLabel)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->securityLabel);
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * Security labels that protect the handling of information about the term and its
     * elements, which may be specifically identified..
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractSecurityLabel $securityLabel
     * @return static
     */
    public function addSecurityLabel(FHIRContractSecurityLabel $securityLabel): self
    {
        if (!isset($this->securityLabel)) {
            $this->securityLabel = [];
        }
        $this->securityLabel[] = $securityLabel;
        return $this;
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * Security labels that protect the handling of information about the term and its
     * elements, which may be specifically identified..
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractSecurityLabel ...$securityLabel
     * @return static
     */
    public function setSecurityLabel(FHIRContractSecurityLabel ...$securityLabel): self
    {
        if ([] === $securityLabel) {
            unset($this->securityLabel);
            return $this;
        }
        $this->securityLabel = $securityLabel;
        return $this;
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * The matter of concern in the context of this provision of the agrement.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractOffer
     */
    public function getOffer(): null|FHIRContractOffer
    {
        return $this->offer ?? null;
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * The matter of concern in the context of this provision of the agrement.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractOffer $offer
     * @return static
     */
    public function setOffer(null|FHIRContractOffer $offer): self
    {
        if (null === $offer) {
            unset($this->offer);
            return $this;
        }
        $this->offer = $offer;
        return $this;
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * Contract Term Asset List.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractAsset>
     */
    public function getAsset(): array
    {
        return $this->asset ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractAsset>
     */
    public function getAssetIterator(): iterable
    {
        if (!isset($this->asset)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->asset);
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * Contract Term Asset List.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractAsset $asset
     * @return static
     */
    public function addAsset(FHIRContractAsset $asset): self
    {
        if (!isset($this->asset)) {
            $this->asset = [];
        }
        $this->asset[] = $asset;
        return $this;
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * Contract Term Asset List.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractAsset ...$asset
     * @return static
     */
    public function setAsset(FHIRContractAsset ...$asset): self
    {
        if ([] === $asset) {
            unset($this->asset);
            return $this;
        }
        $this->asset = $asset;
        return $this;
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * An actor taking a role in an activity for which it can be assigned some degree
     * of responsibility for the activity taking place.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractAction>
     */
    public function getAction(): array
    {
        return $this->action ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractAction>
     */
    public function getActionIterator(): iterable
    {
        if (!isset($this->action)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->action);
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * An actor taking a role in an activity for which it can be assigned some degree
     * of responsibility for the activity taking place.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractAction $action
     * @return static
     */
    public function addAction(FHIRContractAction $action): self
    {
        if (!isset($this->action)) {
            $this->action = [];
        }
        $this->action[] = $action;
        return $this;
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * An actor taking a role in an activity for which it can be assigned some degree
     * of responsibility for the activity taking place.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractAction ...$action
     * @return static
     */
    public function setAction(FHIRContractAction ...$action): self
    {
        if ([] === $action) {
            unset($this->action);
            return $this;
        }
        $this->action = $action;
        return $this;
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * Nested group of Contract Provisions.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractTerm>
     */
    public function getGroup(): array
    {
        return $this->group ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractTerm>
     */
    public function getGroupIterator(): iterable
    {
        if (!isset($this->group)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->group);
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * Nested group of Contract Provisions.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractTerm $group
     * @return static
     */
    public function addGroup(FHIRContractTerm $group): self
    {
        if (!isset($this->group)) {
            $this->group = [];
        }
        $this->group[] = $group;
        return $this;
    }

    /**
     * Legally enforceable, formally recorded unilateral or bilateral directive i.e., a
     * policy or agreement.
     *
     * Nested group of Contract Provisions.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractTerm ...$group
     * @return static
     */
    public function setGroup(FHIRContractTerm ...$group): self
    {
        if ([] === $group) {
            unset($this->group);
            return $this;
        }
        $this->group = $group;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractTerm $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractTerm
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRContractTerm)) {
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
            } else if (self::FIELD_IDENTIFIER === $cen) {
                $type->setIdentifier(FHIRIdentifier::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ISSUED === $cen) {
                $type->setIssued(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_APPLIES === $cen) {
                $type->setApplies(FHIRPeriod::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TOPIC_CODEABLE_CONCEPT === $cen) {
                $type->setTopicCodeableConcept(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TOPIC_REFERENCE === $cen) {
                $type->setTopicReference(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TYPE === $cen) {
                $type->setType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SUB_TYPE === $cen) {
                $type->setSubType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TEXT === $cen) {
                $type->setText(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SECURITY_LABEL === $cen) {
                $type->addSecurityLabel(FHIRContractSecurityLabel::xmlUnserialize($ce, $config));
            } else if (self::FIELD_OFFER === $cen) {
                $type->setOffer(FHIRContractOffer::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ASSET === $cen) {
                $type->addAsset(FHIRContractAsset::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ACTION === $cen) {
                $type->addAction(FHIRContractAction::xmlUnserialize($ce, $config));
            } else if (self::FIELD_GROUP === $cen) {
                $type->addGroup(FHIRContractTerm::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_ISSUED])) {
            if (isset($type->issued)) {
                $type->issued->setValue((string)$attributes[self::FIELD_ISSUED]);
            } else {
                $type->setIssued((string)$attributes[self::FIELD_ISSUED]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_ISSUED, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_TEXT])) {
            if (isset($type->text)) {
                $type->text->setValue((string)$attributes[self::FIELD_TEXT]);
            } else {
                $type->setText((string)$attributes[self::FIELD_TEXT]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_TEXT, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->issued) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_ISSUED]) {
            $xw->writeAttribute(self::FIELD_ISSUED, $this->issued->_getValueAsString());
        }
        if (isset($this->text) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_TEXT]) {
            $xw->writeAttribute(self::FIELD_TEXT, $this->text->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->identifier)) {
            $xw->startElement(self::FIELD_IDENTIFIER);
            $this->identifier->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->issued)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_ISSUED]
                || $this->issued->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_ISSUED);
            $this->issued->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_ISSUED]);
            $xw->endElement();
        }
        if (isset($this->applies)) {
            $xw->startElement(self::FIELD_APPLIES);
            $this->applies->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->topicCodeableConcept)) {
            $xw->startElement(self::FIELD_TOPIC_CODEABLE_CONCEPT);
            $this->topicCodeableConcept->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->topicReference)) {
            $xw->startElement(self::FIELD_TOPIC_REFERENCE);
            $this->topicReference->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->type)) {
            $xw->startElement(self::FIELD_TYPE);
            $this->type->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->subType)) {
            $xw->startElement(self::FIELD_SUB_TYPE);
            $this->subType->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->text)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_TEXT]
                || $this->text->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_TEXT);
            $this->text->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_TEXT]);
            $xw->endElement();
        }
        if (isset($this->securityLabel)) {
            foreach ($this->securityLabel as $v) {
                $xw->startElement(self::FIELD_SECURITY_LABEL);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->offer)) {
            $xw->startElement(self::FIELD_OFFER);
            $this->offer->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->asset)) {
            foreach ($this->asset as $v) {
                $xw->startElement(self::FIELD_ASSET);
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
        if (isset($this->group)) {
            foreach ($this->group as $v) {
                $xw->startElement(self::FIELD_GROUP);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractTerm $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRContract\FHIRContractTerm
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
        } else if (!($type instanceof FHIRContractTerm)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->identifier) || property_exists($decoded, self::FIELD_IDENTIFIER)) {
            if (is_array($decoded->identifier)) {
                $type->setIdentifier(FHIRIdentifier::jsonUnserialize(reset($decoded->identifier), $config));
            } else {
                $type->setIdentifier(FHIRIdentifier::jsonUnserialize($decoded->identifier, $config));
            }
        }
        if (isset($decoded->issued)
            || isset($decoded->_issued)
            || property_exists($decoded, self::FIELD_ISSUED)
            || property_exists($decoded, self::FIELD_ISSUED_EXT)) {
            $v = $decoded->_issued ?? new \stdClass();
            $v->value = $decoded->issued ?? null;
            $type->setIssued(FHIRDateTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->applies) || property_exists($decoded, self::FIELD_APPLIES)) {
            if (is_array($decoded->applies)) {
                $type->setApplies(FHIRPeriod::jsonUnserialize(reset($decoded->applies), $config));
            } else {
                $type->setApplies(FHIRPeriod::jsonUnserialize($decoded->applies, $config));
            }
        }
        if (isset($decoded->topicCodeableConcept) || property_exists($decoded, self::FIELD_TOPIC_CODEABLE_CONCEPT)) {
            if (is_array($decoded->topicCodeableConcept)) {
                $type->setTopicCodeableConcept(FHIRCodeableConcept::jsonUnserialize(reset($decoded->topicCodeableConcept), $config));
            } else {
                $type->setTopicCodeableConcept(FHIRCodeableConcept::jsonUnserialize($decoded->topicCodeableConcept, $config));
            }
        }
        if (isset($decoded->topicReference) || property_exists($decoded, self::FIELD_TOPIC_REFERENCE)) {
            if (is_array($decoded->topicReference)) {
                $type->setTopicReference(FHIRReference::jsonUnserialize(reset($decoded->topicReference), $config));
            } else {
                $type->setTopicReference(FHIRReference::jsonUnserialize($decoded->topicReference, $config));
            }
        }
        if (isset($decoded->type) || property_exists($decoded, self::FIELD_TYPE)) {
            if (is_array($decoded->type)) {
                $type->setType(FHIRCodeableConcept::jsonUnserialize(reset($decoded->type), $config));
            } else {
                $type->setType(FHIRCodeableConcept::jsonUnserialize($decoded->type, $config));
            }
        }
        if (isset($decoded->subType) || property_exists($decoded, self::FIELD_SUB_TYPE)) {
            if (is_array($decoded->subType)) {
                $type->setSubType(FHIRCodeableConcept::jsonUnserialize(reset($decoded->subType), $config));
            } else {
                $type->setSubType(FHIRCodeableConcept::jsonUnserialize($decoded->subType, $config));
            }
        }
        if (isset($decoded->text)
            || isset($decoded->_text)
            || property_exists($decoded, self::FIELD_TEXT)
            || property_exists($decoded, self::FIELD_TEXT_EXT)) {
            $v = $decoded->_text ?? new \stdClass();
            $v->value = $decoded->text ?? null;
            $type->setText(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->securityLabel) || property_exists($decoded, self::FIELD_SECURITY_LABEL)) {
            if (is_object($decoded->securityLabel)) {
                $vals = [$decoded->securityLabel];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_SECURITY_LABEL, true);
            } else {
                $vals = $decoded->securityLabel;
            }
            foreach($vals as $v) {
                $type->addSecurityLabel(FHIRContractSecurityLabel::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->offer) || property_exists($decoded, self::FIELD_OFFER)) {
            if (is_array($decoded->offer)) {
                $type->setOffer(FHIRContractOffer::jsonUnserialize(reset($decoded->offer), $config));
            } else {
                $type->setOffer(FHIRContractOffer::jsonUnserialize($decoded->offer, $config));
            }
        }
        if (isset($decoded->asset) || property_exists($decoded, self::FIELD_ASSET)) {
            if (is_object($decoded->asset)) {
                $vals = [$decoded->asset];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_ASSET, true);
            } else {
                $vals = $decoded->asset;
            }
            foreach($vals as $v) {
                $type->addAsset(FHIRContractAsset::jsonUnserialize($v, $config));
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
                $type->addAction(FHIRContractAction::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->group) || property_exists($decoded, self::FIELD_GROUP)) {
            if (is_object($decoded->group)) {
                $vals = [$decoded->group];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_GROUP, true);
            } else {
                $vals = $decoded->group;
            }
            foreach($vals as $v) {
                $type->addGroup(FHIRContractTerm::jsonUnserialize($v, $config));
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
        if (isset($this->identifier)) {
            $out->identifier = $this->identifier;
        }
        if (isset($this->issued)) {
            if (null !== ($val = $this->issued->getValue())) {
                $out->issued = $val;
            }
            if ($this->issued->_nonValueFieldDefined()) {
                $ext = $this->issued->jsonSerialize();
                unset($ext->value);
                $out->_issued = $ext;
            }
        }
        if (isset($this->applies)) {
            $out->applies = $this->applies;
        }
        if (isset($this->topicCodeableConcept)) {
            $out->topicCodeableConcept = $this->topicCodeableConcept;
        }
        if (isset($this->topicReference)) {
            $out->topicReference = $this->topicReference;
        }
        if (isset($this->type)) {
            $out->type = $this->type;
        }
        if (isset($this->subType)) {
            $out->subType = $this->subType;
        }
        if (isset($this->text)) {
            if (null !== ($val = $this->text->getValue())) {
                $out->text = $val;
            }
            if ($this->text->_nonValueFieldDefined()) {
                $ext = $this->text->jsonSerialize();
                unset($ext->value);
                $out->_text = $ext;
            }
        }
        if (isset($this->securityLabel) && [] !== $this->securityLabel) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_SECURITY_LABEL) && 1 === count($this->securityLabel)) {
                $out->securityLabel = $this->securityLabel[0];
            } else {
                $out->securityLabel = $this->securityLabel;
            }
        }
        if (isset($this->offer)) {
            $out->offer = $this->offer;
        }
        if (isset($this->asset) && [] !== $this->asset) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_ASSET) && 1 === count($this->asset)) {
                $out->asset = $this->asset[0];
            } else {
                $out->asset = $this->asset;
            }
        }
        if (isset($this->action) && [] !== $this->action) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_ACTION) && 1 === count($this->action)) {
                $out->action = $this->action[0];
            } else {
                $out->action = $this->action;
            }
        }
        if (isset($this->group) && [] !== $this->group) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_GROUP) && 1 === count($this->group)) {
                $out->group = $this->group[0];
            } else {
                $out->group = $this->group;
            }
        }
        return $out;
    }
}
