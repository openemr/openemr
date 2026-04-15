<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource;

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
use OpenEMR\FHIR\Types\ResourceTypeInterface;
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRPublicationStatusList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRInsurancePlan\FHIRInsurancePlanContact;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRInsurancePlan\FHIRInsurancePlanCoverage;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRInsurancePlan\FHIRInsurancePlanPlan;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPublicationStatus;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive;
use OpenEMR\FHIR\Versions\R4\Version;
use OpenEMR\FHIR\Versions\R4\VersionConstants;
use OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface;
use OpenEMR\FHIR\Versions\R4\VersionTypeMap;

/**
 * Details of a Health Insurance product/plan provided by an organization.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRInsurancePlan extends FHIRDomainResource implements VersionContainedTypeInterface
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_INSURANCE_PLAN;

    /* class_default.php:56 */
    public const FIELD_IDENTIFIER = 'identifier';
    public const FIELD_STATUS = 'status';
    public const FIELD_STATUS_EXT = '_status';
    public const FIELD_TYPE = 'type';
    public const FIELD_NAME = 'name';
    public const FIELD_NAME_EXT = '_name';
    public const FIELD_ALIAS = 'alias';
    public const FIELD_ALIAS_EXT = '_alias';
    public const FIELD_PERIOD = 'period';
    public const FIELD_OWNED_BY = 'ownedBy';
    public const FIELD_ADMINISTERED_BY = 'administeredBy';
    public const FIELD_COVERAGE_AREA = 'coverageArea';
    public const FIELD_CONTACT = 'contact';
    public const FIELD_ENDPOINT = 'endpoint';
    public const FIELD_NETWORK = 'network';
    public const FIELD_COVERAGE = 'coverage';
    public const FIELD_PLAN = 'plan';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_STATUS => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_NAME => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Business identifiers assigned to this health insurance product which remain
     * constant as the resource is updated and propagates from server to server.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    #[FHIRIdentifier]
    protected array $identifier;
    /**
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The current state of the health insurance product.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPublicationStatus
     */
    #[FHIRPublicationStatus]
    protected FHIRPublicationStatus $status;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The kind of health insurance product.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $type;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Official name of the health insurance product as designated by the owner.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $name;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A list of alternate names that the product is known as, or was known as in the
     * past.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    #[FHIRString]
    protected array $alias;
    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The period of time that the health insurance product is available.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    #[FHIRPeriod]
    protected FHIRPeriod $period;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The entity that is providing the health insurance product and underwriting the
     * risk. This is typically an insurance carriers, other third-party payers, or
     * health plan sponsors comonly referred to as 'payers'.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $ownedBy;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * An organization which administer other services such as underwriting, customer
     * service and/or claims processing on behalf of the health insurance product
     * owner.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $administeredBy;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The geographic region in which a health insurance product's benefits apply.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $coverageArea;
    /**
     * Details of a Health Insurance product/plan provided by an organization.
     *
     * The contact for the health insurance product for a certain purpose.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRInsurancePlan\FHIRInsurancePlanContact>
     */
    #[FHIRInsurancePlanContact]
    protected array $contact;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The technical endpoints providing access to services operated for the health
     * insurance product.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $endpoint;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reference to the network included in the health insurance product.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $network;
    /**
     * Details of a Health Insurance product/plan provided by an organization.
     *
     * Details about the coverage offered by the insurance product.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRInsurancePlan\FHIRInsurancePlanCoverage>
     */
    #[FHIRInsurancePlanCoverage]
    protected array $coverage;
    /**
     * Details of a Health Insurance product/plan provided by an organization.
     *
     * Details about an insurance plan.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRInsurancePlan\FHIRInsurancePlanPlan>
     */
    #[FHIRInsurancePlanPlan]
    protected array $plan;

    /* constructor.php:61 */
    /**
     * FHIRInsurancePlan Constructor
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $id
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $meta
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $implicitRules
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative $text
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer>|iterable<\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface> $contained
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier> $identifier
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRPublicationStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPublicationStatus $status
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $type
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $name
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString> $alias
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $period
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $ownedBy
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $administeredBy
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $coverageArea
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRInsurancePlan\FHIRInsurancePlanContact> $contact
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $endpoint
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $network
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRInsurancePlan\FHIRInsurancePlanCoverage> $coverage
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRInsurancePlan\FHIRInsurancePlanPlan> $plan
     * @param null|string[] $fhirComments
     */
    public function __construct(null|string|FHIRIdPrimitive|FHIRId $id = null,
                                null|FHIRMeta $meta = null,
                                null|string|FHIRUriPrimitive|FHIRUri $implicitRules = null,
                                null|string|FHIRCodePrimitive|FHIRCode $language = null,
                                null|FHIRNarrative $text = null,
                                null|iterable $contained = null,
                                null|iterable $extension = null,
                                null|iterable $modifierExtension = null,
                                null|iterable $identifier = null,
                                null|string|FHIRPublicationStatusList|FHIRPublicationStatus $status = null,
                                null|iterable $type = null,
                                null|string|FHIRStringPrimitive|FHIRString $name = null,
                                null|iterable $alias = null,
                                null|FHIRPeriod $period = null,
                                null|FHIRReference $ownedBy = null,
                                null|FHIRReference $administeredBy = null,
                                null|iterable $coverageArea = null,
                                null|iterable $contact = null,
                                null|iterable $endpoint = null,
                                null|iterable $network = null,
                                null|iterable $coverage = null,
                                null|iterable $plan = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(id: $id,
                            meta: $meta,
                            implicitRules: $implicitRules,
                            language: $language,
                            text: $text,
                            contained: $contained,
                            extension: $extension,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $identifier) {
            $this->setIdentifier(...$identifier);
        }
        if (null !== $status) {
            $this->setStatus($status);
        }
        if (null !== $type) {
            $this->setType(...$type);
        }
        if (null !== $name) {
            $this->setName($name);
        }
        if (null !== $alias) {
            $this->setAlias(...$alias);
        }
        if (null !== $period) {
            $this->setPeriod($period);
        }
        if (null !== $ownedBy) {
            $this->setOwnedBy($ownedBy);
        }
        if (null !== $administeredBy) {
            $this->setAdministeredBy($administeredBy);
        }
        if (null !== $coverageArea) {
            $this->setCoverageArea(...$coverageArea);
        }
        if (null !== $contact) {
            $this->setContact(...$contact);
        }
        if (null !== $endpoint) {
            $this->setEndpoint(...$endpoint);
        }
        if (null !== $network) {
            $this->setNetwork(...$network);
        }
        if (null !== $coverage) {
            $this->setCoverage(...$coverage);
        }
        if (null !== $plan) {
            $this->setPlan(...$plan);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:163 */
    public function _getResourceType(): string
    {
        return static::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Business identifiers assigned to this health insurance product which remain
     * constant as the resource is updated and propagates from server to server.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    public function getIdentifier(): array
    {
        return $this->identifier ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    public function getIdentifierIterator(): iterable
    {
        if (!isset($this->identifier)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->identifier);
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Business identifiers assigned to this health insurance product which remain
     * constant as the resource is updated and propagates from server to server.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $identifier
     * @return static
     */
    public function addIdentifier(FHIRIdentifier $identifier): self
    {
        if (!isset($this->identifier)) {
            $this->identifier = [];
        }
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Business identifiers assigned to this health insurance product which remain
     * constant as the resource is updated and propagates from server to server.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier ...$identifier
     * @return static
     */
    public function setIdentifier(FHIRIdentifier ...$identifier): self
    {
        if ([] === $identifier) {
            unset($this->identifier);
            return $this;
        }
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The current state of the health insurance product.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPublicationStatus
     */
    public function getStatus(): null|FHIRPublicationStatus
    {
        return $this->status ?? null;
    }

    /**
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The current state of the health insurance product.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRPublicationStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPublicationStatus $status
     * @return static
     */
    public function setStatus(null|string|FHIRPublicationStatusList|FHIRPublicationStatus $status): self
    {
        if (null === $status) {
            unset($this->status);
            return $this;
        }
        if (!($status instanceof FHIRPublicationStatus)) {
            $status = new FHIRPublicationStatus(value: $status);
        }
        $this->status = $status;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The kind of health insurance product.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getType(): array
    {
        return $this->type ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getTypeIterator(): iterable
    {
        if (!isset($this->type)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->type);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The kind of health insurance product.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $type
     * @return static
     */
    public function addType(FHIRCodeableConcept $type): self
    {
        if (!isset($this->type)) {
            $this->type = [];
        }
        $this->type[] = $type;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The kind of health insurance product.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$type
     * @return static
     */
    public function setType(FHIRCodeableConcept ...$type): self
    {
        if ([] === $type) {
            unset($this->type);
            return $this;
        }
        $this->type = $type;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Official name of the health insurance product as designated by the owner.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getName(): null|FHIRString
    {
        return $this->name ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Official name of the health insurance product as designated by the owner.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $name
     * @return static
     */
    public function setName(null|string|FHIRStringPrimitive|FHIRString $name): self
    {
        if (null === $name) {
            unset($this->name);
            return $this;
        }
        if (!($name instanceof FHIRString)) {
            $name = new FHIRString(value: $name);
        }
        $this->name = $name;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A list of alternate names that the product is known as, or was known as in the
     * past.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    public function getAlias(): array
    {
        return $this->alias ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    public function getAliasIterator(): iterable
    {
        if (!isset($this->alias)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->alias);
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A list of alternate names that the product is known as, or was known as in the
     * past.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $alias
     * @return static
     */
    public function addAlias(string|FHIRStringPrimitive|FHIRString $alias): self
    {
        if (!($alias instanceof FHIRString)) {
            $alias = new FHIRString(value: $alias);
        }
        if (!isset($this->alias)) {
            $this->alias = [];
        }
        $this->alias[] = $alias;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A list of alternate names that the product is known as, or was known as in the
     * past.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString ...$alias
     * @return static
     */
    public function setAlias(string|FHIRStringPrimitive|FHIRString ...$alias): self
    {
        if ([] === $alias) {
            unset($this->alias);
            return $this;
        }
        $this->alias = [];
        foreach($alias as $v) {
            if ($v instanceof FHIRString) {
                $this->alias[] = $v;
            } else {
                $this->alias[] = new FHIRString(value: $v);
            }
        }
        return $this;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The period of time that the health insurance product is available.
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
     * The period of time that the health insurance product is available.
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
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The entity that is providing the health insurance product and underwriting the
     * risk. This is typically an insurance carriers, other third-party payers, or
     * health plan sponsors comonly referred to as 'payers'.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getOwnedBy(): null|FHIRReference
    {
        return $this->ownedBy ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The entity that is providing the health insurance product and underwriting the
     * risk. This is typically an insurance carriers, other third-party payers, or
     * health plan sponsors comonly referred to as 'payers'.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $ownedBy
     * @return static
     */
    public function setOwnedBy(null|FHIRReference $ownedBy): self
    {
        if (null === $ownedBy) {
            unset($this->ownedBy);
            return $this;
        }
        $this->ownedBy = $ownedBy;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * An organization which administer other services such as underwriting, customer
     * service and/or claims processing on behalf of the health insurance product
     * owner.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getAdministeredBy(): null|FHIRReference
    {
        return $this->administeredBy ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * An organization which administer other services such as underwriting, customer
     * service and/or claims processing on behalf of the health insurance product
     * owner.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $administeredBy
     * @return static
     */
    public function setAdministeredBy(null|FHIRReference $administeredBy): self
    {
        if (null === $administeredBy) {
            unset($this->administeredBy);
            return $this;
        }
        $this->administeredBy = $administeredBy;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The geographic region in which a health insurance product's benefits apply.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getCoverageArea(): array
    {
        return $this->coverageArea ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getCoverageAreaIterator(): iterable
    {
        if (!isset($this->coverageArea)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->coverageArea);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The geographic region in which a health insurance product's benefits apply.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $coverageArea
     * @return static
     */
    public function addCoverageArea(FHIRReference $coverageArea): self
    {
        if (!isset($this->coverageArea)) {
            $this->coverageArea = [];
        }
        $this->coverageArea[] = $coverageArea;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The geographic region in which a health insurance product's benefits apply.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$coverageArea
     * @return static
     */
    public function setCoverageArea(FHIRReference ...$coverageArea): self
    {
        if ([] === $coverageArea) {
            unset($this->coverageArea);
            return $this;
        }
        $this->coverageArea = $coverageArea;
        return $this;
    }

    /**
     * Details of a Health Insurance product/plan provided by an organization.
     *
     * The contact for the health insurance product for a certain purpose.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRInsurancePlan\FHIRInsurancePlanContact>
     */
    public function getContact(): array
    {
        return $this->contact ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRInsurancePlan\FHIRInsurancePlanContact>
     */
    public function getContactIterator(): iterable
    {
        if (!isset($this->contact)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->contact);
    }

    /**
     * Details of a Health Insurance product/plan provided by an organization.
     *
     * The contact for the health insurance product for a certain purpose.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRInsurancePlan\FHIRInsurancePlanContact $contact
     * @return static
     */
    public function addContact(FHIRInsurancePlanContact $contact): self
    {
        if (!isset($this->contact)) {
            $this->contact = [];
        }
        $this->contact[] = $contact;
        return $this;
    }

    /**
     * Details of a Health Insurance product/plan provided by an organization.
     *
     * The contact for the health insurance product for a certain purpose.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRInsurancePlan\FHIRInsurancePlanContact ...$contact
     * @return static
     */
    public function setContact(FHIRInsurancePlanContact ...$contact): self
    {
        if ([] === $contact) {
            unset($this->contact);
            return $this;
        }
        $this->contact = $contact;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The technical endpoints providing access to services operated for the health
     * insurance product.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getEndpoint(): array
    {
        return $this->endpoint ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getEndpointIterator(): iterable
    {
        if (!isset($this->endpoint)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->endpoint);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The technical endpoints providing access to services operated for the health
     * insurance product.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $endpoint
     * @return static
     */
    public function addEndpoint(FHIRReference $endpoint): self
    {
        if (!isset($this->endpoint)) {
            $this->endpoint = [];
        }
        $this->endpoint[] = $endpoint;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The technical endpoints providing access to services operated for the health
     * insurance product.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$endpoint
     * @return static
     */
    public function setEndpoint(FHIRReference ...$endpoint): self
    {
        if ([] === $endpoint) {
            unset($this->endpoint);
            return $this;
        }
        $this->endpoint = $endpoint;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reference to the network included in the health insurance product.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getNetwork(): array
    {
        return $this->network ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getNetworkIterator(): iterable
    {
        if (!isset($this->network)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->network);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reference to the network included in the health insurance product.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $network
     * @return static
     */
    public function addNetwork(FHIRReference $network): self
    {
        if (!isset($this->network)) {
            $this->network = [];
        }
        $this->network[] = $network;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reference to the network included in the health insurance product.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$network
     * @return static
     */
    public function setNetwork(FHIRReference ...$network): self
    {
        if ([] === $network) {
            unset($this->network);
            return $this;
        }
        $this->network = $network;
        return $this;
    }

    /**
     * Details of a Health Insurance product/plan provided by an organization.
     *
     * Details about the coverage offered by the insurance product.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRInsurancePlan\FHIRInsurancePlanCoverage>
     */
    public function getCoverage(): array
    {
        return $this->coverage ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRInsurancePlan\FHIRInsurancePlanCoverage>
     */
    public function getCoverageIterator(): iterable
    {
        if (!isset($this->coverage)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->coverage);
    }

    /**
     * Details of a Health Insurance product/plan provided by an organization.
     *
     * Details about the coverage offered by the insurance product.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRInsurancePlan\FHIRInsurancePlanCoverage $coverage
     * @return static
     */
    public function addCoverage(FHIRInsurancePlanCoverage $coverage): self
    {
        if (!isset($this->coverage)) {
            $this->coverage = [];
        }
        $this->coverage[] = $coverage;
        return $this;
    }

    /**
     * Details of a Health Insurance product/plan provided by an organization.
     *
     * Details about the coverage offered by the insurance product.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRInsurancePlan\FHIRInsurancePlanCoverage ...$coverage
     * @return static
     */
    public function setCoverage(FHIRInsurancePlanCoverage ...$coverage): self
    {
        if ([] === $coverage) {
            unset($this->coverage);
            return $this;
        }
        $this->coverage = $coverage;
        return $this;
    }

    /**
     * Details of a Health Insurance product/plan provided by an organization.
     *
     * Details about an insurance plan.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRInsurancePlan\FHIRInsurancePlanPlan>
     */
    public function getPlan(): array
    {
        return $this->plan ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRInsurancePlan\FHIRInsurancePlanPlan>
     */
    public function getPlanIterator(): iterable
    {
        if (!isset($this->plan)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->plan);
    }

    /**
     * Details of a Health Insurance product/plan provided by an organization.
     *
     * Details about an insurance plan.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRInsurancePlan\FHIRInsurancePlanPlan $plan
     * @return static
     */
    public function addPlan(FHIRInsurancePlanPlan $plan): self
    {
        if (!isset($this->plan)) {
            $this->plan = [];
        }
        $this->plan[] = $plan;
        return $this;
    }

    /**
     * Details of a Health Insurance product/plan provided by an organization.
     *
     * Details about an insurance plan.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRInsurancePlan\FHIRInsurancePlanPlan ...$plan
     * @return static
     */
    public function setPlan(FHIRInsurancePlanPlan ...$plan): self
    {
        if ([] === $plan) {
            unset($this->plan);
            return $this;
        }
        $this->plan = $plan;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRInsurancePlan $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRInsurancePlan
     * @throws \Exception
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRInsurancePlan)) {
            throw new \RuntimeException(sprintf(
                '%s::xmlUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        if (null === $config) {
            $config = (new Version())->getConfig()->getUnserializeConfig();
        }
        if (is_string($element)) {
            $element = new \SimpleXMLElement($element, $config->getLibxmlOpts());
        }
        if (null !== ($ns = $element->getNamespaces()[''] ?? null)) {
            $type->_setSourceXMLNS((string)$ns);
        }
        foreach ($element->children() as $ce) {
            $cen = $ce->getName();
            if (self::FIELD_ID === $cen) {
                $type->setId(FHIRId::xmlUnserialize($ce, $config));
            } else if (self::FIELD_META === $cen) {
                $type->setMeta(FHIRMeta::xmlUnserialize($ce, $config));
            } else if (self::FIELD_IMPLICIT_RULES === $cen) {
                $type->setImplicitRules(FHIRUri::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LANGUAGE === $cen) {
                $type->setLanguage(FHIRCode::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TEXT === $cen) {
                $type->setText(FHIRNarrative::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CONTAINED === $cen) {
                foreach ($ce->children() as $cen) {
                    /** @var \OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface $cn */
                    $cn = VersionTypeMap::mustGetContainedTypeClassnameFromXML($cen);
                    $type->addContained($cn::xmlUnserialize($cen, $config));
                }
            } else if (self::FIELD_EXTENSION === $cen) {
                $type->addExtension(FHIRExtension::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MODIFIER_EXTENSION === $cen) {
                $type->addModifierExtension(FHIRExtension::xmlUnserialize($ce, $config));
            } else if (self::FIELD_IDENTIFIER === $cen) {
                $type->addIdentifier(FHIRIdentifier::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STATUS === $cen) {
                $type->setStatus(FHIRPublicationStatus::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TYPE === $cen) {
                $type->addType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NAME === $cen) {
                $type->setName(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ALIAS === $cen) {
                $type->addAlias(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PERIOD === $cen) {
                $type->setPeriod(FHIRPeriod::xmlUnserialize($ce, $config));
            } else if (self::FIELD_OWNED_BY === $cen) {
                $type->setOwnedBy(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ADMINISTERED_BY === $cen) {
                $type->setAdministeredBy(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_COVERAGE_AREA === $cen) {
                $type->addCoverageArea(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CONTACT === $cen) {
                $type->addContact(FHIRInsurancePlanContact::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ENDPOINT === $cen) {
                $type->addEndpoint(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NETWORK === $cen) {
                $type->addNetwork(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_COVERAGE === $cen) {
                $type->addCoverage(FHIRInsurancePlanCoverage::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PLAN === $cen) {
                $type->addPlan(FHIRInsurancePlanPlan::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            if (isset($type->id)) {
                $type->id->setValue((string)$attributes[self::FIELD_ID]);
            } else {
                $type->setId((string)$attributes[self::FIELD_ID]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_IMPLICIT_RULES])) {
            if (isset($type->implicitRules)) {
                $type->implicitRules->setValue((string)$attributes[self::FIELD_IMPLICIT_RULES]);
            } else {
                $type->setImplicitRules((string)$attributes[self::FIELD_IMPLICIT_RULES]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_IMPLICIT_RULES, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_LANGUAGE])) {
            if (isset($type->language)) {
                $type->language->setValue((string)$attributes[self::FIELD_LANGUAGE]);
            } else {
                $type->setLanguage((string)$attributes[self::FIELD_LANGUAGE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_LANGUAGE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_STATUS])) {
            if (isset($type->status)) {
                $type->status->setValue((string)$attributes[self::FIELD_STATUS]);
            } else {
                $type->setStatus((string)$attributes[self::FIELD_STATUS]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_STATUS, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_NAME])) {
            if (isset($type->name)) {
                $type->name->setValue((string)$attributes[self::FIELD_NAME]);
            } else {
                $type->setName((string)$attributes[self::FIELD_NAME]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_NAME, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        return $type;
    }

    /**
     * @param null|\OpenEMR\FHIR\Encoding\XMLWriter $xw
     * @param null|\OpenEMR\FHIR\Encoding\SerializeConfig $config
     * @return \OpenEMR\FHIR\Encoding\XMLWriter
     */
    public function xmlSerialize(null|XMLWriter $xw = null,
                                 null|SerializeConfig $config = null): XMLWriter
    {
        if (null === $config) {
            $config = (new Version())->getConfig()->getSerializeConfig();
        }
        if (null === $xw) {
            $xw = new XMLWriter($config);
        }
        if (!$xw->isOpen()) {
            $xw->openMemory();
        }
        if (!$xw->isDocStarted()) {
            $docStarted = true;
            $xw->startDocument();
        }
        if (!$xw->isRootOpen()) {
            $rootOpened = true;
            $xw->openRootNode('InsurancePlan', $this->_getSourceXMLNS());
        }
        if (isset($this->status) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_STATUS]) {
            $xw->writeAttribute(self::FIELD_STATUS, $this->status->_getValueAsString());
        }
        if (isset($this->name) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_NAME]) {
            $xw->writeAttribute(self::FIELD_NAME, $this->name->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->identifier)) {
            foreach ($this->identifier as $v) {
                $xw->startElement(self::FIELD_IDENTIFIER);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->status)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_STATUS]
                || $this->status->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_STATUS);
            $this->status->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_STATUS]);
            $xw->endElement();
        }
        if (isset($this->type)) {
            foreach ($this->type as $v) {
                $xw->startElement(self::FIELD_TYPE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->name)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_NAME]
                || $this->name->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_NAME);
            $this->name->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_NAME]);
            $xw->endElement();
        }
        if (isset($this->alias) && [] !== $this->alias) {
            foreach($this->alias as $v) {
                $xw->startElement(self::FIELD_ALIAS);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->period)) {
            $xw->startElement(self::FIELD_PERIOD);
            $this->period->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->ownedBy)) {
            $xw->startElement(self::FIELD_OWNED_BY);
            $this->ownedBy->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->administeredBy)) {
            $xw->startElement(self::FIELD_ADMINISTERED_BY);
            $this->administeredBy->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->coverageArea)) {
            foreach ($this->coverageArea as $v) {
                $xw->startElement(self::FIELD_COVERAGE_AREA);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->contact)) {
            foreach ($this->contact as $v) {
                $xw->startElement(self::FIELD_CONTACT);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->endpoint)) {
            foreach ($this->endpoint as $v) {
                $xw->startElement(self::FIELD_ENDPOINT);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->network)) {
            foreach ($this->network as $v) {
                $xw->startElement(self::FIELD_NETWORK);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->coverage)) {
            foreach ($this->coverage as $v) {
                $xw->startElement(self::FIELD_COVERAGE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->plan)) {
            foreach ($this->plan as $v) {
                $xw->startElement(self::FIELD_PLAN);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if ($rootOpened ?? false) {
            $xw->endElement();
        }
        if ($docStarted ?? false) {
            $xw->endDocument();
        }
        return $xw;
    }

    /**
     * @param string|\stdClass $decoded
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRInsurancePlan $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRInsurancePlan
     * @throws \Exception
     */
    public static function jsonUnserialize(string|\stdClass $decoded,
                                           null|UnserializeConfig $config = null,
                                           null|ResourceTypeInterface $type = null): self
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
        } else if (!($type instanceof FHIRInsurancePlan)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        if (null === $config) {
            $config = (new Version())->getConfig()->getUnserializeConfig();
        }
        if (is_string($decoded)) {
            $decoded = json_decode(json: $decoded,
                                associative: false,
                                depth: $config->getJSONDecodeMaxDepth(),
                                flags: $config->getJSONDecodeOpts());
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->identifier) || property_exists($decoded, self::FIELD_IDENTIFIER)) {
            if (is_object($decoded->identifier)) {
                $vals = [$decoded->identifier];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_IDENTIFIER, true);
            } else {
                $vals = $decoded->identifier;
            }
            foreach($vals as $v) {
                $type->addIdentifier(FHIRIdentifier::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->status)
            || isset($decoded->_status)
            || property_exists($decoded, self::FIELD_STATUS)
            || property_exists($decoded, self::FIELD_STATUS_EXT)) {
            $v = $decoded->_status ?? new \stdClass();
            $v->value = $decoded->status ?? null;
            $type->setStatus(FHIRPublicationStatus::jsonUnserialize($v, $config));
        }
        if (isset($decoded->type) || property_exists($decoded, self::FIELD_TYPE)) {
            if (is_object($decoded->type)) {
                $vals = [$decoded->type];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_TYPE, true);
            } else {
                $vals = $decoded->type;
            }
            foreach($vals as $v) {
                $type->addType(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->name)
            || isset($decoded->_name)
            || property_exists($decoded, self::FIELD_NAME)
            || property_exists($decoded, self::FIELD_NAME_EXT)) {
            $v = $decoded->_name ?? new \stdClass();
            $v->value = $decoded->name ?? null;
            $type->setName(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->alias)
            || isset($decoded->_alias)
            || property_exists($decoded, self::FIELD_ALIAS)
            || property_exists($decoded, self::FIELD_ALIAS_EXT)) {
            $vals = (array)($decoded->alias ?? []);
            $exts = (array)($decoded->_alias ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addAlias(FHIRString::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->period) || property_exists($decoded, self::FIELD_PERIOD)) {
            if (is_array($decoded->period)) {
                $type->setPeriod(FHIRPeriod::jsonUnserialize(reset($decoded->period), $config));
            } else {
                $type->setPeriod(FHIRPeriod::jsonUnserialize($decoded->period, $config));
            }
        }
        if (isset($decoded->ownedBy) || property_exists($decoded, self::FIELD_OWNED_BY)) {
            if (is_array($decoded->ownedBy)) {
                $type->setOwnedBy(FHIRReference::jsonUnserialize(reset($decoded->ownedBy), $config));
            } else {
                $type->setOwnedBy(FHIRReference::jsonUnserialize($decoded->ownedBy, $config));
            }
        }
        if (isset($decoded->administeredBy) || property_exists($decoded, self::FIELD_ADMINISTERED_BY)) {
            if (is_array($decoded->administeredBy)) {
                $type->setAdministeredBy(FHIRReference::jsonUnserialize(reset($decoded->administeredBy), $config));
            } else {
                $type->setAdministeredBy(FHIRReference::jsonUnserialize($decoded->administeredBy, $config));
            }
        }
        if (isset($decoded->coverageArea) || property_exists($decoded, self::FIELD_COVERAGE_AREA)) {
            if (is_object($decoded->coverageArea)) {
                $vals = [$decoded->coverageArea];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_COVERAGE_AREA, true);
            } else {
                $vals = $decoded->coverageArea;
            }
            foreach($vals as $v) {
                $type->addCoverageArea(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->contact) || property_exists($decoded, self::FIELD_CONTACT)) {
            if (is_object($decoded->contact)) {
                $vals = [$decoded->contact];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_CONTACT, true);
            } else {
                $vals = $decoded->contact;
            }
            foreach($vals as $v) {
                $type->addContact(FHIRInsurancePlanContact::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->endpoint) || property_exists($decoded, self::FIELD_ENDPOINT)) {
            if (is_object($decoded->endpoint)) {
                $vals = [$decoded->endpoint];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_ENDPOINT, true);
            } else {
                $vals = $decoded->endpoint;
            }
            foreach($vals as $v) {
                $type->addEndpoint(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->network) || property_exists($decoded, self::FIELD_NETWORK)) {
            if (is_object($decoded->network)) {
                $vals = [$decoded->network];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_NETWORK, true);
            } else {
                $vals = $decoded->network;
            }
            foreach($vals as $v) {
                $type->addNetwork(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->coverage) || property_exists($decoded, self::FIELD_COVERAGE)) {
            if (is_object($decoded->coverage)) {
                $vals = [$decoded->coverage];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_COVERAGE, true);
            } else {
                $vals = $decoded->coverage;
            }
            foreach($vals as $v) {
                $type->addCoverage(FHIRInsurancePlanCoverage::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->plan) || property_exists($decoded, self::FIELD_PLAN)) {
            if (is_object($decoded->plan)) {
                $vals = [$decoded->plan];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PLAN, true);
            } else {
                $vals = $decoded->plan;
            }
            foreach($vals as $v) {
                $type->addPlan(FHIRInsurancePlanPlan::jsonUnserialize($v, $config));
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
        if (isset($this->identifier) && [] !== $this->identifier) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_IDENTIFIER) && 1 === count($this->identifier)) {
                $out->identifier = $this->identifier[0];
            } else {
                $out->identifier = $this->identifier;
            }
        }
        if (isset($this->status)) {
            if (null !== ($val = $this->status->getValue())) {
                $out->status = $val;
            }
            if ($this->status->_nonValueFieldDefined()) {
                $ext = $this->status->jsonSerialize();
                unset($ext->value);
                $out->_status = $ext;
            }
        }
        if (isset($this->type) && [] !== $this->type) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_TYPE) && 1 === count($this->type)) {
                $out->type = $this->type[0];
            } else {
                $out->type = $this->type;
            }
        }
        if (isset($this->name)) {
            if (null !== ($val = $this->name->getValue())) {
                $out->name = $val;
            }
            if ($this->name->_nonValueFieldDefined()) {
                $ext = $this->name->jsonSerialize();
                unset($ext->value);
                $out->_name = $ext;
            }
        }
        if (isset($this->alias) && [] !== $this->alias) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->alias as $v) {
                $val = $v->getValue();
                if (null !== $val) {
                    $hasVals = true;
                    $vals[] = $val;
                } else {
                    $vals[] = null;
                }
                if ($v->_nonValueFieldDefined()) {
                    $hasExts = true;
                    $ext = $v->jsonSerialize();
                    unset($ext->value);
                    $exts[] = $ext;
                } else {
                    $exts[] = null;
                }
            }
            if ($hasVals) {
                $out->alias = $vals;
            }
            if ($hasExts) {
                $out->_alias = $exts;
            }
        }
        if (isset($this->period)) {
            $out->period = $this->period;
        }
        if (isset($this->ownedBy)) {
            $out->ownedBy = $this->ownedBy;
        }
        if (isset($this->administeredBy)) {
            $out->administeredBy = $this->administeredBy;
        }
        if (isset($this->coverageArea) && [] !== $this->coverageArea) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_COVERAGE_AREA) && 1 === count($this->coverageArea)) {
                $out->coverageArea = $this->coverageArea[0];
            } else {
                $out->coverageArea = $this->coverageArea;
            }
        }
        if (isset($this->contact) && [] !== $this->contact) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_CONTACT) && 1 === count($this->contact)) {
                $out->contact = $this->contact[0];
            } else {
                $out->contact = $this->contact;
            }
        }
        if (isset($this->endpoint) && [] !== $this->endpoint) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_ENDPOINT) && 1 === count($this->endpoint)) {
                $out->endpoint = $this->endpoint[0];
            } else {
                $out->endpoint = $this->endpoint;
            }
        }
        if (isset($this->network) && [] !== $this->network) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_NETWORK) && 1 === count($this->network)) {
                $out->network = $this->network[0];
            } else {
                $out->network = $this->network;
            }
        }
        if (isset($this->coverage) && [] !== $this->coverage) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_COVERAGE) && 1 === count($this->coverage)) {
                $out->coverage = $this->coverage[0];
            } else {
                $out->coverage = $this->coverage;
            }
        }
        if (isset($this->plan) && [] !== $this->plan) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PLAN) && 1 === count($this->plan)) {
                $out->plan = $this->plan[0];
            } else {
                $out->plan = $this->plan;
            }
        }
        $out->resourceType = $this->_getResourceType();
        return $out;
    }
}
