<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCoverageEligibilityResponse;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * This resource provides eligibility and plan details from the processing of an
 * CoverageEligibilityRequest resource.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRCoverageEligibilityResponseItem extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_COVERAGE_ELIGIBILITY_RESPONSE_DOT_ITEM;

    /* class_default.php:56 */
    public const FIELD_CATEGORY = 'category';
    public const FIELD_PRODUCT_OR_SERVICE = 'productOrService';
    public const FIELD_MODIFIER = 'modifier';
    public const FIELD_PROVIDER = 'provider';
    public const FIELD_EXCLUDED = 'excluded';
    public const FIELD_EXCLUDED_EXT = '_excluded';
    public const FIELD_NAME = 'name';
    public const FIELD_NAME_EXT = '_name';
    public const FIELD_DESCRIPTION = 'description';
    public const FIELD_DESCRIPTION_EXT = '_description';
    public const FIELD_NETWORK = 'network';
    public const FIELD_UNIT = 'unit';
    public const FIELD_TERM = 'term';
    public const FIELD_BENEFIT = 'benefit';
    public const FIELD_AUTHORIZATION_REQUIRED = 'authorizationRequired';
    public const FIELD_AUTHORIZATION_REQUIRED_EXT = '_authorizationRequired';
    public const FIELD_AUTHORIZATION_SUPPORTING = 'authorizationSupporting';
    public const FIELD_AUTHORIZATION_URL = 'authorizationUrl';
    public const FIELD_AUTHORIZATION_URL_EXT = '_authorizationUrl';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_EXCLUDED => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_NAME => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DESCRIPTION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_AUTHORIZATION_REQUIRED => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_AUTHORIZATION_URL => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Code to identify the general type of benefits under which products and services
     * are provided.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $category;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * This contains the product, service, drug or other billing code for the item.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $productOrService;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Item typification or modifiers codes to convey additional context for the
     * product or service.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $modifier;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The practitioner who is eligible for the provision of the product or service.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $provider;
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * True if the indicated class of service is excluded from the plan, missing or
     * False indicates the product or service is included in the coverage.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    #[FHIRBoolean]
    protected FHIRBoolean $excluded;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A short name or tag for the benefit.
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
     * A richer description of the benefit or services covered.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $description;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Is a flag to indicate whether the benefits refer to in-network providers or
     * out-of-network providers.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $network;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates if the benefits apply to an individual or to the family.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $unit;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The term or period of the values such as 'maximum lifetime benefit' or 'maximum
     * annual visits'.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $term;
    /**
     * This resource provides eligibility and plan details from the processing of an
     * CoverageEligibilityRequest resource.
     *
     * Benefits used to date.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCoverageEligibilityResponse\FHIRCoverageEligibilityResponseBenefit>
     */
    #[FHIRCoverageEligibilityResponseBenefit]
    protected array $benefit;
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A boolean flag indicating whether a preauthorization is required prior to actual
     * service delivery.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    #[FHIRBoolean]
    protected FHIRBoolean $authorizationRequired;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Codes or comments regarding information or actions associated with the
     * preauthorization.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $authorizationSupporting;
    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A web location for obtaining requirements or descriptive information regarding
     * the preauthorization.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    #[FHIRUri]
    protected FHIRUri $authorizationUrl;

    /* constructor.php:61 */
    /**
     * FHIRCoverageEligibilityResponseItem Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $category
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $productOrService
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $modifier
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $provider
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $excluded
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $name
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $description
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $network
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $unit
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $term
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCoverageEligibilityResponse\FHIRCoverageEligibilityResponseBenefit> $benefit
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $authorizationRequired
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $authorizationSupporting
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $authorizationUrl
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|FHIRCodeableConcept $category = null,
                                null|FHIRCodeableConcept $productOrService = null,
                                null|iterable $modifier = null,
                                null|FHIRReference $provider = null,
                                null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $excluded = null,
                                null|string|FHIRStringPrimitive|FHIRString $name = null,
                                null|string|FHIRStringPrimitive|FHIRString $description = null,
                                null|FHIRCodeableConcept $network = null,
                                null|FHIRCodeableConcept $unit = null,
                                null|FHIRCodeableConcept $term = null,
                                null|iterable $benefit = null,
                                null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $authorizationRequired = null,
                                null|iterable $authorizationSupporting = null,
                                null|string|FHIRUriPrimitive|FHIRUri $authorizationUrl = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $category) {
            $this->setCategory($category);
        }
        if (null !== $productOrService) {
            $this->setProductOrService($productOrService);
        }
        if (null !== $modifier) {
            $this->setModifier(...$modifier);
        }
        if (null !== $provider) {
            $this->setProvider($provider);
        }
        if (null !== $excluded) {
            $this->setExcluded($excluded);
        }
        if (null !== $name) {
            $this->setName($name);
        }
        if (null !== $description) {
            $this->setDescription($description);
        }
        if (null !== $network) {
            $this->setNetwork($network);
        }
        if (null !== $unit) {
            $this->setUnit($unit);
        }
        if (null !== $term) {
            $this->setTerm($term);
        }
        if (null !== $benefit) {
            $this->setBenefit(...$benefit);
        }
        if (null !== $authorizationRequired) {
            $this->setAuthorizationRequired($authorizationRequired);
        }
        if (null !== $authorizationSupporting) {
            $this->setAuthorizationSupporting(...$authorizationSupporting);
        }
        if (null !== $authorizationUrl) {
            $this->setAuthorizationUrl($authorizationUrl);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Code to identify the general type of benefits under which products and services
     * are provided.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getCategory(): null|FHIRCodeableConcept
    {
        return $this->category ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Code to identify the general type of benefits under which products and services
     * are provided.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $category
     * @return static
     */
    public function setCategory(null|FHIRCodeableConcept $category): self
    {
        if (null === $category) {
            unset($this->category);
            return $this;
        }
        $this->category = $category;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * This contains the product, service, drug or other billing code for the item.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getProductOrService(): null|FHIRCodeableConcept
    {
        return $this->productOrService ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * This contains the product, service, drug or other billing code for the item.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $productOrService
     * @return static
     */
    public function setProductOrService(null|FHIRCodeableConcept $productOrService): self
    {
        if (null === $productOrService) {
            unset($this->productOrService);
            return $this;
        }
        $this->productOrService = $productOrService;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Item typification or modifiers codes to convey additional context for the
     * product or service.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getModifier(): array
    {
        return $this->modifier ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getModifierIterator(): iterable
    {
        if (!isset($this->modifier)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->modifier);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Item typification or modifiers codes to convey additional context for the
     * product or service.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $modifier
     * @return static
     */
    public function addModifier(FHIRCodeableConcept $modifier): self
    {
        if (!isset($this->modifier)) {
            $this->modifier = [];
        }
        $this->modifier[] = $modifier;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Item typification or modifiers codes to convey additional context for the
     * product or service.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$modifier
     * @return static
     */
    public function setModifier(FHIRCodeableConcept ...$modifier): self
    {
        if ([] === $modifier) {
            unset($this->modifier);
            return $this;
        }
        $this->modifier = $modifier;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The practitioner who is eligible for the provision of the product or service.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getProvider(): null|FHIRReference
    {
        return $this->provider ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The practitioner who is eligible for the provision of the product or service.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $provider
     * @return static
     */
    public function setProvider(null|FHIRReference $provider): self
    {
        if (null === $provider) {
            unset($this->provider);
            return $this;
        }
        $this->provider = $provider;
        return $this;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * True if the indicated class of service is excluded from the plan, missing or
     * False indicates the product or service is included in the coverage.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    public function getExcluded(): null|FHIRBoolean
    {
        return $this->excluded ?? null;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * True if the indicated class of service is excluded from the plan, missing or
     * False indicates the product or service is included in the coverage.
     *
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $excluded
     * @return static
     */
    public function setExcluded(null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $excluded): self
    {
        if (null === $excluded) {
            unset($this->excluded);
            return $this;
        }
        if (!($excluded instanceof FHIRBoolean)) {
            $excluded = new FHIRBoolean(value: $excluded);
        }
        $this->excluded = $excluded;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A short name or tag for the benefit.
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
     * A short name or tag for the benefit.
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
     * A richer description of the benefit or services covered.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getDescription(): null|FHIRString
    {
        return $this->description ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A richer description of the benefit or services covered.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $description
     * @return static
     */
    public function setDescription(null|string|FHIRStringPrimitive|FHIRString $description): self
    {
        if (null === $description) {
            unset($this->description);
            return $this;
        }
        if (!($description instanceof FHIRString)) {
            $description = new FHIRString(value: $description);
        }
        $this->description = $description;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Is a flag to indicate whether the benefits refer to in-network providers or
     * out-of-network providers.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getNetwork(): null|FHIRCodeableConcept
    {
        return $this->network ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Is a flag to indicate whether the benefits refer to in-network providers or
     * out-of-network providers.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $network
     * @return static
     */
    public function setNetwork(null|FHIRCodeableConcept $network): self
    {
        if (null === $network) {
            unset($this->network);
            return $this;
        }
        $this->network = $network;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates if the benefits apply to an individual or to the family.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getUnit(): null|FHIRCodeableConcept
    {
        return $this->unit ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates if the benefits apply to an individual or to the family.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $unit
     * @return static
     */
    public function setUnit(null|FHIRCodeableConcept $unit): self
    {
        if (null === $unit) {
            unset($this->unit);
            return $this;
        }
        $this->unit = $unit;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The term or period of the values such as 'maximum lifetime benefit' or 'maximum
     * annual visits'.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getTerm(): null|FHIRCodeableConcept
    {
        return $this->term ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The term or period of the values such as 'maximum lifetime benefit' or 'maximum
     * annual visits'.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $term
     * @return static
     */
    public function setTerm(null|FHIRCodeableConcept $term): self
    {
        if (null === $term) {
            unset($this->term);
            return $this;
        }
        $this->term = $term;
        return $this;
    }

    /**
     * This resource provides eligibility and plan details from the processing of an
     * CoverageEligibilityRequest resource.
     *
     * Benefits used to date.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCoverageEligibilityResponse\FHIRCoverageEligibilityResponseBenefit>
     */
    public function getBenefit(): array
    {
        return $this->benefit ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCoverageEligibilityResponse\FHIRCoverageEligibilityResponseBenefit>
     */
    public function getBenefitIterator(): iterable
    {
        if (!isset($this->benefit)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->benefit);
    }

    /**
     * This resource provides eligibility and plan details from the processing of an
     * CoverageEligibilityRequest resource.
     *
     * Benefits used to date.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCoverageEligibilityResponse\FHIRCoverageEligibilityResponseBenefit $benefit
     * @return static
     */
    public function addBenefit(FHIRCoverageEligibilityResponseBenefit $benefit): self
    {
        if (!isset($this->benefit)) {
            $this->benefit = [];
        }
        $this->benefit[] = $benefit;
        return $this;
    }

    /**
     * This resource provides eligibility and plan details from the processing of an
     * CoverageEligibilityRequest resource.
     *
     * Benefits used to date.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCoverageEligibilityResponse\FHIRCoverageEligibilityResponseBenefit ...$benefit
     * @return static
     */
    public function setBenefit(FHIRCoverageEligibilityResponseBenefit ...$benefit): self
    {
        if ([] === $benefit) {
            unset($this->benefit);
            return $this;
        }
        $this->benefit = $benefit;
        return $this;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A boolean flag indicating whether a preauthorization is required prior to actual
     * service delivery.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    public function getAuthorizationRequired(): null|FHIRBoolean
    {
        return $this->authorizationRequired ?? null;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A boolean flag indicating whether a preauthorization is required prior to actual
     * service delivery.
     *
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $authorizationRequired
     * @return static
     */
    public function setAuthorizationRequired(null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $authorizationRequired): self
    {
        if (null === $authorizationRequired) {
            unset($this->authorizationRequired);
            return $this;
        }
        if (!($authorizationRequired instanceof FHIRBoolean)) {
            $authorizationRequired = new FHIRBoolean(value: $authorizationRequired);
        }
        $this->authorizationRequired = $authorizationRequired;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Codes or comments regarding information or actions associated with the
     * preauthorization.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getAuthorizationSupporting(): array
    {
        return $this->authorizationSupporting ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getAuthorizationSupportingIterator(): iterable
    {
        if (!isset($this->authorizationSupporting)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->authorizationSupporting);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Codes or comments regarding information or actions associated with the
     * preauthorization.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $authorizationSupporting
     * @return static
     */
    public function addAuthorizationSupporting(FHIRCodeableConcept $authorizationSupporting): self
    {
        if (!isset($this->authorizationSupporting)) {
            $this->authorizationSupporting = [];
        }
        $this->authorizationSupporting[] = $authorizationSupporting;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Codes or comments regarding information or actions associated with the
     * preauthorization.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$authorizationSupporting
     * @return static
     */
    public function setAuthorizationSupporting(FHIRCodeableConcept ...$authorizationSupporting): self
    {
        if ([] === $authorizationSupporting) {
            unset($this->authorizationSupporting);
            return $this;
        }
        $this->authorizationSupporting = $authorizationSupporting;
        return $this;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A web location for obtaining requirements or descriptive information regarding
     * the preauthorization.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    public function getAuthorizationUrl(): null|FHIRUri
    {
        return $this->authorizationUrl ?? null;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A web location for obtaining requirements or descriptive information regarding
     * the preauthorization.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $authorizationUrl
     * @return static
     */
    public function setAuthorizationUrl(null|string|FHIRUriPrimitive|FHIRUri $authorizationUrl): self
    {
        if (null === $authorizationUrl) {
            unset($this->authorizationUrl);
            return $this;
        }
        if (!($authorizationUrl instanceof FHIRUri)) {
            $authorizationUrl = new FHIRUri(value: $authorizationUrl);
        }
        $this->authorizationUrl = $authorizationUrl;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCoverageEligibilityResponse\FHIRCoverageEligibilityResponseItem $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCoverageEligibilityResponse\FHIRCoverageEligibilityResponseItem
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRCoverageEligibilityResponseItem)) {
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
            } else if (self::FIELD_CATEGORY === $cen) {
                $type->setCategory(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PRODUCT_OR_SERVICE === $cen) {
                $type->setProductOrService(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MODIFIER === $cen) {
                $type->addModifier(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PROVIDER === $cen) {
                $type->setProvider(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_EXCLUDED === $cen) {
                $type->setExcluded(FHIRBoolean::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NAME === $cen) {
                $type->setName(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DESCRIPTION === $cen) {
                $type->setDescription(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NETWORK === $cen) {
                $type->setNetwork(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_UNIT === $cen) {
                $type->setUnit(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TERM === $cen) {
                $type->setTerm(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_BENEFIT === $cen) {
                $type->addBenefit(FHIRCoverageEligibilityResponseBenefit::xmlUnserialize($ce, $config));
            } else if (self::FIELD_AUTHORIZATION_REQUIRED === $cen) {
                $type->setAuthorizationRequired(FHIRBoolean::xmlUnserialize($ce, $config));
            } else if (self::FIELD_AUTHORIZATION_SUPPORTING === $cen) {
                $type->addAuthorizationSupporting(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_AUTHORIZATION_URL === $cen) {
                $type->setAuthorizationUrl(FHIRUri::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_EXCLUDED])) {
            if (isset($type->excluded)) {
                $type->excluded->setValue((string)$attributes[self::FIELD_EXCLUDED]);
            } else {
                $type->setExcluded((string)$attributes[self::FIELD_EXCLUDED]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_EXCLUDED, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_NAME])) {
            if (isset($type->name)) {
                $type->name->setValue((string)$attributes[self::FIELD_NAME]);
            } else {
                $type->setName((string)$attributes[self::FIELD_NAME]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_NAME, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_DESCRIPTION])) {
            if (isset($type->description)) {
                $type->description->setValue((string)$attributes[self::FIELD_DESCRIPTION]);
            } else {
                $type->setDescription((string)$attributes[self::FIELD_DESCRIPTION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DESCRIPTION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_AUTHORIZATION_REQUIRED])) {
            if (isset($type->authorizationRequired)) {
                $type->authorizationRequired->setValue((string)$attributes[self::FIELD_AUTHORIZATION_REQUIRED]);
            } else {
                $type->setAuthorizationRequired((string)$attributes[self::FIELD_AUTHORIZATION_REQUIRED]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_AUTHORIZATION_REQUIRED, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_AUTHORIZATION_URL])) {
            if (isset($type->authorizationUrl)) {
                $type->authorizationUrl->setValue((string)$attributes[self::FIELD_AUTHORIZATION_URL]);
            } else {
                $type->setAuthorizationUrl((string)$attributes[self::FIELD_AUTHORIZATION_URL]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_AUTHORIZATION_URL, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->excluded) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_EXCLUDED]) {
            $xw->writeAttribute(self::FIELD_EXCLUDED, $this->excluded->_getValueAsString());
        }
        if (isset($this->name) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_NAME]) {
            $xw->writeAttribute(self::FIELD_NAME, $this->name->_getValueAsString());
        }
        if (isset($this->description) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DESCRIPTION]) {
            $xw->writeAttribute(self::FIELD_DESCRIPTION, $this->description->_getValueAsString());
        }
        if (isset($this->authorizationRequired) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_AUTHORIZATION_REQUIRED]) {
            $xw->writeAttribute(self::FIELD_AUTHORIZATION_REQUIRED, $this->authorizationRequired->_getValueAsString());
        }
        if (isset($this->authorizationUrl) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_AUTHORIZATION_URL]) {
            $xw->writeAttribute(self::FIELD_AUTHORIZATION_URL, $this->authorizationUrl->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->category)) {
            $xw->startElement(self::FIELD_CATEGORY);
            $this->category->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->productOrService)) {
            $xw->startElement(self::FIELD_PRODUCT_OR_SERVICE);
            $this->productOrService->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->modifier)) {
            foreach ($this->modifier as $v) {
                $xw->startElement(self::FIELD_MODIFIER);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->provider)) {
            $xw->startElement(self::FIELD_PROVIDER);
            $this->provider->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->excluded)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_EXCLUDED]
                || $this->excluded->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_EXCLUDED);
            $this->excluded->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_EXCLUDED]);
            $xw->endElement();
        }
        if (isset($this->name)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_NAME]
                || $this->name->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_NAME);
            $this->name->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_NAME]);
            $xw->endElement();
        }
        if (isset($this->description)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DESCRIPTION]
                || $this->description->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DESCRIPTION);
            $this->description->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DESCRIPTION]);
            $xw->endElement();
        }
        if (isset($this->network)) {
            $xw->startElement(self::FIELD_NETWORK);
            $this->network->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->unit)) {
            $xw->startElement(self::FIELD_UNIT);
            $this->unit->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->term)) {
            $xw->startElement(self::FIELD_TERM);
            $this->term->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->benefit)) {
            foreach ($this->benefit as $v) {
                $xw->startElement(self::FIELD_BENEFIT);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->authorizationRequired)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_AUTHORIZATION_REQUIRED]
                || $this->authorizationRequired->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_AUTHORIZATION_REQUIRED);
            $this->authorizationRequired->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_AUTHORIZATION_REQUIRED]);
            $xw->endElement();
        }
        if (isset($this->authorizationSupporting)) {
            foreach ($this->authorizationSupporting as $v) {
                $xw->startElement(self::FIELD_AUTHORIZATION_SUPPORTING);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->authorizationUrl)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_AUTHORIZATION_URL]
                || $this->authorizationUrl->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_AUTHORIZATION_URL);
            $this->authorizationUrl->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_AUTHORIZATION_URL]);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCoverageEligibilityResponse\FHIRCoverageEligibilityResponseItem $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCoverageEligibilityResponse\FHIRCoverageEligibilityResponseItem
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
        } else if (!($type instanceof FHIRCoverageEligibilityResponseItem)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->category) || property_exists($decoded, self::FIELD_CATEGORY)) {
            if (is_array($decoded->category)) {
                $type->setCategory(FHIRCodeableConcept::jsonUnserialize(reset($decoded->category), $config));
            } else {
                $type->setCategory(FHIRCodeableConcept::jsonUnserialize($decoded->category, $config));
            }
        }
        if (isset($decoded->productOrService) || property_exists($decoded, self::FIELD_PRODUCT_OR_SERVICE)) {
            if (is_array($decoded->productOrService)) {
                $type->setProductOrService(FHIRCodeableConcept::jsonUnserialize(reset($decoded->productOrService), $config));
            } else {
                $type->setProductOrService(FHIRCodeableConcept::jsonUnserialize($decoded->productOrService, $config));
            }
        }
        if (isset($decoded->modifier) || property_exists($decoded, self::FIELD_MODIFIER)) {
            if (is_object($decoded->modifier)) {
                $vals = [$decoded->modifier];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_MODIFIER, true);
            } else {
                $vals = $decoded->modifier;
            }
            foreach($vals as $v) {
                $type->addModifier(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->provider) || property_exists($decoded, self::FIELD_PROVIDER)) {
            if (is_array($decoded->provider)) {
                $type->setProvider(FHIRReference::jsonUnserialize(reset($decoded->provider), $config));
            } else {
                $type->setProvider(FHIRReference::jsonUnserialize($decoded->provider, $config));
            }
        }
        if (isset($decoded->excluded)
            || isset($decoded->_excluded)
            || property_exists($decoded, self::FIELD_EXCLUDED)
            || property_exists($decoded, self::FIELD_EXCLUDED_EXT)) {
            $v = $decoded->_excluded ?? new \stdClass();
            $v->value = $decoded->excluded ?? null;
            $type->setExcluded(FHIRBoolean::jsonUnserialize($v, $config));
        }
        if (isset($decoded->name)
            || isset($decoded->_name)
            || property_exists($decoded, self::FIELD_NAME)
            || property_exists($decoded, self::FIELD_NAME_EXT)) {
            $v = $decoded->_name ?? new \stdClass();
            $v->value = $decoded->name ?? null;
            $type->setName(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->description)
            || isset($decoded->_description)
            || property_exists($decoded, self::FIELD_DESCRIPTION)
            || property_exists($decoded, self::FIELD_DESCRIPTION_EXT)) {
            $v = $decoded->_description ?? new \stdClass();
            $v->value = $decoded->description ?? null;
            $type->setDescription(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->network) || property_exists($decoded, self::FIELD_NETWORK)) {
            if (is_array($decoded->network)) {
                $type->setNetwork(FHIRCodeableConcept::jsonUnserialize(reset($decoded->network), $config));
            } else {
                $type->setNetwork(FHIRCodeableConcept::jsonUnserialize($decoded->network, $config));
            }
        }
        if (isset($decoded->unit) || property_exists($decoded, self::FIELD_UNIT)) {
            if (is_array($decoded->unit)) {
                $type->setUnit(FHIRCodeableConcept::jsonUnserialize(reset($decoded->unit), $config));
            } else {
                $type->setUnit(FHIRCodeableConcept::jsonUnserialize($decoded->unit, $config));
            }
        }
        if (isset($decoded->term) || property_exists($decoded, self::FIELD_TERM)) {
            if (is_array($decoded->term)) {
                $type->setTerm(FHIRCodeableConcept::jsonUnserialize(reset($decoded->term), $config));
            } else {
                $type->setTerm(FHIRCodeableConcept::jsonUnserialize($decoded->term, $config));
            }
        }
        if (isset($decoded->benefit) || property_exists($decoded, self::FIELD_BENEFIT)) {
            if (is_object($decoded->benefit)) {
                $vals = [$decoded->benefit];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_BENEFIT, true);
            } else {
                $vals = $decoded->benefit;
            }
            foreach($vals as $v) {
                $type->addBenefit(FHIRCoverageEligibilityResponseBenefit::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->authorizationRequired)
            || isset($decoded->_authorizationRequired)
            || property_exists($decoded, self::FIELD_AUTHORIZATION_REQUIRED)
            || property_exists($decoded, self::FIELD_AUTHORIZATION_REQUIRED_EXT)) {
            $v = $decoded->_authorizationRequired ?? new \stdClass();
            $v->value = $decoded->authorizationRequired ?? null;
            $type->setAuthorizationRequired(FHIRBoolean::jsonUnserialize($v, $config));
        }
        if (isset($decoded->authorizationSupporting) || property_exists($decoded, self::FIELD_AUTHORIZATION_SUPPORTING)) {
            if (is_object($decoded->authorizationSupporting)) {
                $vals = [$decoded->authorizationSupporting];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_AUTHORIZATION_SUPPORTING, true);
            } else {
                $vals = $decoded->authorizationSupporting;
            }
            foreach($vals as $v) {
                $type->addAuthorizationSupporting(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->authorizationUrl)
            || isset($decoded->_authorizationUrl)
            || property_exists($decoded, self::FIELD_AUTHORIZATION_URL)
            || property_exists($decoded, self::FIELD_AUTHORIZATION_URL_EXT)) {
            $v = $decoded->_authorizationUrl ?? new \stdClass();
            $v->value = $decoded->authorizationUrl ?? null;
            $type->setAuthorizationUrl(FHIRUri::jsonUnserialize($v, $config));
        }
        return $type;
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize(): mixed
    {
        $out = parent::jsonSerialize();
        if (isset($this->category)) {
            $out->category = $this->category;
        }
        if (isset($this->productOrService)) {
            $out->productOrService = $this->productOrService;
        }
        if (isset($this->modifier) && [] !== $this->modifier) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_MODIFIER) && 1 === count($this->modifier)) {
                $out->modifier = $this->modifier[0];
            } else {
                $out->modifier = $this->modifier;
            }
        }
        if (isset($this->provider)) {
            $out->provider = $this->provider;
        }
        if (isset($this->excluded)) {
            if (null !== ($val = $this->excluded->getValue())) {
                $out->excluded = $val;
            }
            if ($this->excluded->_nonValueFieldDefined()) {
                $ext = $this->excluded->jsonSerialize();
                unset($ext->value);
                $out->_excluded = $ext;
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
        if (isset($this->description)) {
            if (null !== ($val = $this->description->getValue())) {
                $out->description = $val;
            }
            if ($this->description->_nonValueFieldDefined()) {
                $ext = $this->description->jsonSerialize();
                unset($ext->value);
                $out->_description = $ext;
            }
        }
        if (isset($this->network)) {
            $out->network = $this->network;
        }
        if (isset($this->unit)) {
            $out->unit = $this->unit;
        }
        if (isset($this->term)) {
            $out->term = $this->term;
        }
        if (isset($this->benefit) && [] !== $this->benefit) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_BENEFIT) && 1 === count($this->benefit)) {
                $out->benefit = $this->benefit[0];
            } else {
                $out->benefit = $this->benefit;
            }
        }
        if (isset($this->authorizationRequired)) {
            if (null !== ($val = $this->authorizationRequired->getValue())) {
                $out->authorizationRequired = $val;
            }
            if ($this->authorizationRequired->_nonValueFieldDefined()) {
                $ext = $this->authorizationRequired->jsonSerialize();
                unset($ext->value);
                $out->_authorizationRequired = $ext;
            }
        }
        if (isset($this->authorizationSupporting) && [] !== $this->authorizationSupporting) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_AUTHORIZATION_SUPPORTING) && 1 === count($this->authorizationSupporting)) {
                $out->authorizationSupporting = $this->authorizationSupporting[0];
            } else {
                $out->authorizationSupporting = $this->authorizationSupporting;
            }
        }
        if (isset($this->authorizationUrl)) {
            if (null !== ($val = $this->authorizationUrl->getValue())) {
                $out->authorizationUrl = $val;
            }
            if ($this->authorizationUrl->_nonValueFieldDefined()) {
                $ext = $this->authorizationUrl->jsonSerialize();
                unset($ext->value);
                $out->_authorizationUrl = $ext;
            }
        }
        return $out;
    }
}
