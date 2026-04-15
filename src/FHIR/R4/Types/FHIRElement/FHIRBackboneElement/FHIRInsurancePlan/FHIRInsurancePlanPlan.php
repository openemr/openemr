<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRInsurancePlan;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * Details of a Health Insurance product/plan provided by an organization.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRInsurancePlanPlan extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_INSURANCE_PLAN_DOT_PLAN;

    /* class_default.php:56 */
    public const FIELD_IDENTIFIER = 'identifier';
    public const FIELD_TYPE = 'type';
    public const FIELD_COVERAGE_AREA = 'coverageArea';
    public const FIELD_NETWORK = 'network';
    public const FIELD_GENERAL_COST = 'generalCost';
    public const FIELD_SPECIFIC_COST = 'specificCost';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
    ];

    /* class_default.php:112 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Business identifiers assigned to this health insurance plan which remain
     * constant as the resource is updated and propagates from server to server.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    #[FHIRIdentifier]
    protected array $identifier;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Type of plan. For example, "Platinum" or "High Deductable".
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $type;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The geographic region in which a health insurance plan's benefits apply.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $coverageArea;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reference to the network that providing the type of coverage.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $network;
    /**
     * Details of a Health Insurance product/plan provided by an organization.
     *
     * Overall costs associated with the plan.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRInsurancePlan\FHIRInsurancePlanGeneralCost>
     */
    #[FHIRInsurancePlanGeneralCost]
    protected array $generalCost;
    /**
     * Details of a Health Insurance product/plan provided by an organization.
     *
     * Costs associated with the coverage provided by the product.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRInsurancePlan\FHIRInsurancePlanSpecificCost>
     */
    #[FHIRInsurancePlanSpecificCost]
    protected array $specificCost;

    /* constructor.php:61 */
    /**
     * FHIRInsurancePlanPlan Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier> $identifier
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $type
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $coverageArea
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $network
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRInsurancePlan\FHIRInsurancePlanGeneralCost> $generalCost
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRInsurancePlan\FHIRInsurancePlanSpecificCost> $specificCost
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|iterable $identifier = null,
                                null|FHIRCodeableConcept $type = null,
                                null|iterable $coverageArea = null,
                                null|iterable $network = null,
                                null|iterable $generalCost = null,
                                null|iterable $specificCost = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $identifier) {
            $this->setIdentifier(...$identifier);
        }
        if (null !== $type) {
            $this->setType($type);
        }
        if (null !== $coverageArea) {
            $this->setCoverageArea(...$coverageArea);
        }
        if (null !== $network) {
            $this->setNetwork(...$network);
        }
        if (null !== $generalCost) {
            $this->setGeneralCost(...$generalCost);
        }
        if (null !== $specificCost) {
            $this->setSpecificCost(...$specificCost);
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
     * Business identifiers assigned to this health insurance plan which remain
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
     * Business identifiers assigned to this health insurance plan which remain
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
     * Business identifiers assigned to this health insurance plan which remain
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
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Type of plan. For example, "Platinum" or "High Deductable".
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
     * Type of plan. For example, "Platinum" or "High Deductable".
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
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The geographic region in which a health insurance plan's benefits apply.
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
     * The geographic region in which a health insurance plan's benefits apply.
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
     * The geographic region in which a health insurance plan's benefits apply.
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
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reference to the network that providing the type of coverage.
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
     * Reference to the network that providing the type of coverage.
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
     * Reference to the network that providing the type of coverage.
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
     * Overall costs associated with the plan.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRInsurancePlan\FHIRInsurancePlanGeneralCost>
     */
    public function getGeneralCost(): array
    {
        return $this->generalCost ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRInsurancePlan\FHIRInsurancePlanGeneralCost>
     */
    public function getGeneralCostIterator(): iterable
    {
        if (!isset($this->generalCost)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->generalCost);
    }

    /**
     * Details of a Health Insurance product/plan provided by an organization.
     *
     * Overall costs associated with the plan.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRInsurancePlan\FHIRInsurancePlanGeneralCost $generalCost
     * @return static
     */
    public function addGeneralCost(FHIRInsurancePlanGeneralCost $generalCost): self
    {
        if (!isset($this->generalCost)) {
            $this->generalCost = [];
        }
        $this->generalCost[] = $generalCost;
        return $this;
    }

    /**
     * Details of a Health Insurance product/plan provided by an organization.
     *
     * Overall costs associated with the plan.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRInsurancePlan\FHIRInsurancePlanGeneralCost ...$generalCost
     * @return static
     */
    public function setGeneralCost(FHIRInsurancePlanGeneralCost ...$generalCost): self
    {
        if ([] === $generalCost) {
            unset($this->generalCost);
            return $this;
        }
        $this->generalCost = $generalCost;
        return $this;
    }

    /**
     * Details of a Health Insurance product/plan provided by an organization.
     *
     * Costs associated with the coverage provided by the product.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRInsurancePlan\FHIRInsurancePlanSpecificCost>
     */
    public function getSpecificCost(): array
    {
        return $this->specificCost ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRInsurancePlan\FHIRInsurancePlanSpecificCost>
     */
    public function getSpecificCostIterator(): iterable
    {
        if (!isset($this->specificCost)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->specificCost);
    }

    /**
     * Details of a Health Insurance product/plan provided by an organization.
     *
     * Costs associated with the coverage provided by the product.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRInsurancePlan\FHIRInsurancePlanSpecificCost $specificCost
     * @return static
     */
    public function addSpecificCost(FHIRInsurancePlanSpecificCost $specificCost): self
    {
        if (!isset($this->specificCost)) {
            $this->specificCost = [];
        }
        $this->specificCost[] = $specificCost;
        return $this;
    }

    /**
     * Details of a Health Insurance product/plan provided by an organization.
     *
     * Costs associated with the coverage provided by the product.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRInsurancePlan\FHIRInsurancePlanSpecificCost ...$specificCost
     * @return static
     */
    public function setSpecificCost(FHIRInsurancePlanSpecificCost ...$specificCost): self
    {
        if ([] === $specificCost) {
            unset($this->specificCost);
            return $this;
        }
        $this->specificCost = $specificCost;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRInsurancePlan\FHIRInsurancePlanPlan $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRInsurancePlan\FHIRInsurancePlanPlan
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRInsurancePlanPlan)) {
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
                $type->addIdentifier(FHIRIdentifier::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TYPE === $cen) {
                $type->setType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_COVERAGE_AREA === $cen) {
                $type->addCoverageArea(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NETWORK === $cen) {
                $type->addNetwork(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_GENERAL_COST === $cen) {
                $type->addGeneralCost(FHIRInsurancePlanGeneralCost::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SPECIFIC_COST === $cen) {
                $type->addSpecificCost(FHIRInsurancePlanSpecificCost::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        parent::xmlSerialize($xw, $config);
        if (isset($this->identifier)) {
            foreach ($this->identifier as $v) {
                $xw->startElement(self::FIELD_IDENTIFIER);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->type)) {
            $xw->startElement(self::FIELD_TYPE);
            $this->type->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->coverageArea)) {
            foreach ($this->coverageArea as $v) {
                $xw->startElement(self::FIELD_COVERAGE_AREA);
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
        if (isset($this->generalCost)) {
            foreach ($this->generalCost as $v) {
                $xw->startElement(self::FIELD_GENERAL_COST);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->specificCost)) {
            foreach ($this->specificCost as $v) {
                $xw->startElement(self::FIELD_SPECIFIC_COST);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRInsurancePlan\FHIRInsurancePlanPlan $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRInsurancePlan\FHIRInsurancePlanPlan
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
        } else if (!($type instanceof FHIRInsurancePlanPlan)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
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
        if (isset($decoded->type) || property_exists($decoded, self::FIELD_TYPE)) {
            if (is_array($decoded->type)) {
                $type->setType(FHIRCodeableConcept::jsonUnserialize(reset($decoded->type), $config));
            } else {
                $type->setType(FHIRCodeableConcept::jsonUnserialize($decoded->type, $config));
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
        if (isset($decoded->generalCost) || property_exists($decoded, self::FIELD_GENERAL_COST)) {
            if (is_object($decoded->generalCost)) {
                $vals = [$decoded->generalCost];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_GENERAL_COST, true);
            } else {
                $vals = $decoded->generalCost;
            }
            foreach($vals as $v) {
                $type->addGeneralCost(FHIRInsurancePlanGeneralCost::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->specificCost) || property_exists($decoded, self::FIELD_SPECIFIC_COST)) {
            if (is_object($decoded->specificCost)) {
                $vals = [$decoded->specificCost];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_SPECIFIC_COST, true);
            } else {
                $vals = $decoded->specificCost;
            }
            foreach($vals as $v) {
                $type->addSpecificCost(FHIRInsurancePlanSpecificCost::jsonUnserialize($v, $config));
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
        if (isset($this->type)) {
            $out->type = $this->type;
        }
        if (isset($this->coverageArea) && [] !== $this->coverageArea) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_COVERAGE_AREA) && 1 === count($this->coverageArea)) {
                $out->coverageArea = $this->coverageArea[0];
            } else {
                $out->coverageArea = $this->coverageArea;
            }
        }
        if (isset($this->network) && [] !== $this->network) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_NETWORK) && 1 === count($this->network)) {
                $out->network = $this->network[0];
            } else {
                $out->network = $this->network;
            }
        }
        if (isset($this->generalCost) && [] !== $this->generalCost) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_GENERAL_COST) && 1 === count($this->generalCost)) {
                $out->generalCost = $this->generalCost[0];
            } else {
                $out->generalCost = $this->generalCost;
            }
        }
        if (isset($this->specificCost) && [] !== $this->specificCost) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_SPECIFIC_COST) && 1 === count($this->specificCost)) {
                $out->specificCost = $this->specificCost[0];
            } else {
                $out->specificCost = $this->specificCost;
            }
        }
        return $out;
    }
}
