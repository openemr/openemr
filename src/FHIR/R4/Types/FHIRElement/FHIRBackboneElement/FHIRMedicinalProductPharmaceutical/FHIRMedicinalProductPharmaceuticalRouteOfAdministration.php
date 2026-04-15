<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductPharmaceutical;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRatio;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * A pharmaceutical product described in terms of its composition and dose form.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRMedicinalProductPharmaceuticalRouteOfAdministration extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_MEDICINAL_PRODUCT_PHARMACEUTICAL_DOT_ROUTE_OF_ADMINISTRATION;

    /* class_default.php:56 */
    public const FIELD_CODE = 'code';
    public const FIELD_FIRST_DOSE = 'firstDose';
    public const FIELD_MAX_SINGLE_DOSE = 'maxSingleDose';
    public const FIELD_MAX_DOSE_PER_DAY = 'maxDosePerDay';
    public const FIELD_MAX_DOSE_PER_TREATMENT_PERIOD = 'maxDosePerTreatmentPeriod';
    public const FIELD_MAX_TREATMENT_PERIOD = 'maxTreatmentPeriod';
    public const FIELD_TARGET_SPECIES = 'targetSpecies';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_CODE => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
    ];

    /* class_default.php:112 */
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Coded expression for the route.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $code;
    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The first dose (dose quantity) administered in humans can be specified, for a
     * product under investigation, using a numerical value and its unit of
     * measurement.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    #[FHIRQuantity]
    protected FHIRQuantity $firstDose;
    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The maximum single dose that can be administered as per the protocol of a
     * clinical trial can be specified using a numerical value and its unit of
     * measurement.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    #[FHIRQuantity]
    protected FHIRQuantity $maxSingleDose;
    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The maximum dose per day (maximum dose quantity to be administered in any one
     * 24-h period) that can be administered as per the protocol referenced in the
     * clinical trial authorisation.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    #[FHIRQuantity]
    protected FHIRQuantity $maxDosePerDay;
    /**
     * A relationship of two Quantity values - expressed as a numerator and a
     * denominator.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The maximum dose per treatment period that can be administered as per the
     * protocol referenced in the clinical trial authorisation.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRatio
     */
    #[FHIRRatio]
    protected FHIRRatio $maxDosePerTreatmentPeriod;
    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The maximum treatment period during which an Investigational Medicinal Product
     * can be administered as per the protocol referenced in the clinical trial
     * authorisation.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration
     */
    #[FHIRDuration]
    protected FHIRDuration $maxTreatmentPeriod;
    /**
     * A pharmaceutical product described in terms of its composition and dose form.
     *
     * A species for which this route applies.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductPharmaceutical\FHIRMedicinalProductPharmaceuticalTargetSpecies>
     */
    #[FHIRMedicinalProductPharmaceuticalTargetSpecies]
    protected array $targetSpecies;

    /* constructor.php:61 */
    /**
     * FHIRMedicinalProductPharmaceuticalRouteOfAdministration Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $code
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $firstDose
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $maxSingleDose
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $maxDosePerDay
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRatio $maxDosePerTreatmentPeriod
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration $maxTreatmentPeriod
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductPharmaceutical\FHIRMedicinalProductPharmaceuticalTargetSpecies> $targetSpecies
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|FHIRCodeableConcept $code = null,
                                null|FHIRQuantity $firstDose = null,
                                null|FHIRQuantity $maxSingleDose = null,
                                null|FHIRQuantity $maxDosePerDay = null,
                                null|FHIRRatio $maxDosePerTreatmentPeriod = null,
                                null|FHIRDuration $maxTreatmentPeriod = null,
                                null|iterable $targetSpecies = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $code) {
            $this->setCode($code);
        }
        if (null !== $firstDose) {
            $this->setFirstDose($firstDose);
        }
        if (null !== $maxSingleDose) {
            $this->setMaxSingleDose($maxSingleDose);
        }
        if (null !== $maxDosePerDay) {
            $this->setMaxDosePerDay($maxDosePerDay);
        }
        if (null !== $maxDosePerTreatmentPeriod) {
            $this->setMaxDosePerTreatmentPeriod($maxDosePerTreatmentPeriod);
        }
        if (null !== $maxTreatmentPeriod) {
            $this->setMaxTreatmentPeriod($maxTreatmentPeriod);
        }
        if (null !== $targetSpecies) {
            $this->setTargetSpecies(...$targetSpecies);
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
     * Coded expression for the route.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getCode(): null|FHIRCodeableConcept
    {
        return $this->code ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Coded expression for the route.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $code
     * @return static
     */
    public function setCode(null|FHIRCodeableConcept $code): self
    {
        if (null === $code) {
            unset($this->code);
            return $this;
        }
        $this->code = $code;
        return $this;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The first dose (dose quantity) administered in humans can be specified, for a
     * product under investigation, using a numerical value and its unit of
     * measurement.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    public function getFirstDose(): null|FHIRQuantity
    {
        return $this->firstDose ?? null;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The first dose (dose quantity) administered in humans can be specified, for a
     * product under investigation, using a numerical value and its unit of
     * measurement.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $firstDose
     * @return static
     */
    public function setFirstDose(null|FHIRQuantity $firstDose): self
    {
        if (null === $firstDose) {
            unset($this->firstDose);
            return $this;
        }
        $this->firstDose = $firstDose;
        return $this;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The maximum single dose that can be administered as per the protocol of a
     * clinical trial can be specified using a numerical value and its unit of
     * measurement.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    public function getMaxSingleDose(): null|FHIRQuantity
    {
        return $this->maxSingleDose ?? null;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The maximum single dose that can be administered as per the protocol of a
     * clinical trial can be specified using a numerical value and its unit of
     * measurement.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $maxSingleDose
     * @return static
     */
    public function setMaxSingleDose(null|FHIRQuantity $maxSingleDose): self
    {
        if (null === $maxSingleDose) {
            unset($this->maxSingleDose);
            return $this;
        }
        $this->maxSingleDose = $maxSingleDose;
        return $this;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The maximum dose per day (maximum dose quantity to be administered in any one
     * 24-h period) that can be administered as per the protocol referenced in the
     * clinical trial authorisation.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    public function getMaxDosePerDay(): null|FHIRQuantity
    {
        return $this->maxDosePerDay ?? null;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The maximum dose per day (maximum dose quantity to be administered in any one
     * 24-h period) that can be administered as per the protocol referenced in the
     * clinical trial authorisation.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $maxDosePerDay
     * @return static
     */
    public function setMaxDosePerDay(null|FHIRQuantity $maxDosePerDay): self
    {
        if (null === $maxDosePerDay) {
            unset($this->maxDosePerDay);
            return $this;
        }
        $this->maxDosePerDay = $maxDosePerDay;
        return $this;
    }

    /**
     * A relationship of two Quantity values - expressed as a numerator and a
     * denominator.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The maximum dose per treatment period that can be administered as per the
     * protocol referenced in the clinical trial authorisation.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRatio
     */
    public function getMaxDosePerTreatmentPeriod(): null|FHIRRatio
    {
        return $this->maxDosePerTreatmentPeriod ?? null;
    }

    /**
     * A relationship of two Quantity values - expressed as a numerator and a
     * denominator.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The maximum dose per treatment period that can be administered as per the
     * protocol referenced in the clinical trial authorisation.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRatio $maxDosePerTreatmentPeriod
     * @return static
     */
    public function setMaxDosePerTreatmentPeriod(null|FHIRRatio $maxDosePerTreatmentPeriod): self
    {
        if (null === $maxDosePerTreatmentPeriod) {
            unset($this->maxDosePerTreatmentPeriod);
            return $this;
        }
        $this->maxDosePerTreatmentPeriod = $maxDosePerTreatmentPeriod;
        return $this;
    }

    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The maximum treatment period during which an Investigational Medicinal Product
     * can be administered as per the protocol referenced in the clinical trial
     * authorisation.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public function getMaxTreatmentPeriod(): null|FHIRDuration
    {
        return $this->maxTreatmentPeriod ?? null;
    }

    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The maximum treatment period during which an Investigational Medicinal Product
     * can be administered as per the protocol referenced in the clinical trial
     * authorisation.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration $maxTreatmentPeriod
     * @return static
     */
    public function setMaxTreatmentPeriod(null|FHIRDuration $maxTreatmentPeriod): self
    {
        if (null === $maxTreatmentPeriod) {
            unset($this->maxTreatmentPeriod);
            return $this;
        }
        $this->maxTreatmentPeriod = $maxTreatmentPeriod;
        return $this;
    }

    /**
     * A pharmaceutical product described in terms of its composition and dose form.
     *
     * A species for which this route applies.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductPharmaceutical\FHIRMedicinalProductPharmaceuticalTargetSpecies>
     */
    public function getTargetSpecies(): array
    {
        return $this->targetSpecies ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductPharmaceutical\FHIRMedicinalProductPharmaceuticalTargetSpecies>
     */
    public function getTargetSpeciesIterator(): iterable
    {
        if (!isset($this->targetSpecies)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->targetSpecies);
    }

    /**
     * A pharmaceutical product described in terms of its composition and dose form.
     *
     * A species for which this route applies.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductPharmaceutical\FHIRMedicinalProductPharmaceuticalTargetSpecies $targetSpecies
     * @return static
     */
    public function addTargetSpecies(FHIRMedicinalProductPharmaceuticalTargetSpecies $targetSpecies): self
    {
        if (!isset($this->targetSpecies)) {
            $this->targetSpecies = [];
        }
        $this->targetSpecies[] = $targetSpecies;
        return $this;
    }

    /**
     * A pharmaceutical product described in terms of its composition and dose form.
     *
     * A species for which this route applies.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductPharmaceutical\FHIRMedicinalProductPharmaceuticalTargetSpecies ...$targetSpecies
     * @return static
     */
    public function setTargetSpecies(FHIRMedicinalProductPharmaceuticalTargetSpecies ...$targetSpecies): self
    {
        if ([] === $targetSpecies) {
            unset($this->targetSpecies);
            return $this;
        }
        $this->targetSpecies = $targetSpecies;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductPharmaceutical\FHIRMedicinalProductPharmaceuticalRouteOfAdministration $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductPharmaceutical\FHIRMedicinalProductPharmaceuticalRouteOfAdministration
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRMedicinalProductPharmaceuticalRouteOfAdministration)) {
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
            } else if (self::FIELD_CODE === $cen) {
                $type->setCode(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_FIRST_DOSE === $cen) {
                $type->setFirstDose(FHIRQuantity::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MAX_SINGLE_DOSE === $cen) {
                $type->setMaxSingleDose(FHIRQuantity::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MAX_DOSE_PER_DAY === $cen) {
                $type->setMaxDosePerDay(FHIRQuantity::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MAX_DOSE_PER_TREATMENT_PERIOD === $cen) {
                $type->setMaxDosePerTreatmentPeriod(FHIRRatio::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MAX_TREATMENT_PERIOD === $cen) {
                $type->setMaxTreatmentPeriod(FHIRDuration::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TARGET_SPECIES === $cen) {
                $type->addTargetSpecies(FHIRMedicinalProductPharmaceuticalTargetSpecies::xmlUnserialize($ce, $config));
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
        if (isset($this->code)) {
            $xw->startElement(self::FIELD_CODE);
            $this->code->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->firstDose)) {
            $xw->startElement(self::FIELD_FIRST_DOSE);
            $this->firstDose->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->maxSingleDose)) {
            $xw->startElement(self::FIELD_MAX_SINGLE_DOSE);
            $this->maxSingleDose->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->maxDosePerDay)) {
            $xw->startElement(self::FIELD_MAX_DOSE_PER_DAY);
            $this->maxDosePerDay->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->maxDosePerTreatmentPeriod)) {
            $xw->startElement(self::FIELD_MAX_DOSE_PER_TREATMENT_PERIOD);
            $this->maxDosePerTreatmentPeriod->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->maxTreatmentPeriod)) {
            $xw->startElement(self::FIELD_MAX_TREATMENT_PERIOD);
            $this->maxTreatmentPeriod->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->targetSpecies)) {
            foreach ($this->targetSpecies as $v) {
                $xw->startElement(self::FIELD_TARGET_SPECIES);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductPharmaceutical\FHIRMedicinalProductPharmaceuticalRouteOfAdministration $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductPharmaceutical\FHIRMedicinalProductPharmaceuticalRouteOfAdministration
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
        } else if (!($type instanceof FHIRMedicinalProductPharmaceuticalRouteOfAdministration)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->code) || property_exists($decoded, self::FIELD_CODE)) {
            if (is_array($decoded->code)) {
                $type->setCode(FHIRCodeableConcept::jsonUnserialize(reset($decoded->code), $config));
            } else {
                $type->setCode(FHIRCodeableConcept::jsonUnserialize($decoded->code, $config));
            }
        }
        if (isset($decoded->firstDose) || property_exists($decoded, self::FIELD_FIRST_DOSE)) {
            if (is_array($decoded->firstDose)) {
                $type->setFirstDose(FHIRQuantity::jsonUnserialize(reset($decoded->firstDose), $config));
            } else {
                $type->setFirstDose(FHIRQuantity::jsonUnserialize($decoded->firstDose, $config));
            }
        }
        if (isset($decoded->maxSingleDose) || property_exists($decoded, self::FIELD_MAX_SINGLE_DOSE)) {
            if (is_array($decoded->maxSingleDose)) {
                $type->setMaxSingleDose(FHIRQuantity::jsonUnserialize(reset($decoded->maxSingleDose), $config));
            } else {
                $type->setMaxSingleDose(FHIRQuantity::jsonUnserialize($decoded->maxSingleDose, $config));
            }
        }
        if (isset($decoded->maxDosePerDay) || property_exists($decoded, self::FIELD_MAX_DOSE_PER_DAY)) {
            if (is_array($decoded->maxDosePerDay)) {
                $type->setMaxDosePerDay(FHIRQuantity::jsonUnserialize(reset($decoded->maxDosePerDay), $config));
            } else {
                $type->setMaxDosePerDay(FHIRQuantity::jsonUnserialize($decoded->maxDosePerDay, $config));
            }
        }
        if (isset($decoded->maxDosePerTreatmentPeriod) || property_exists($decoded, self::FIELD_MAX_DOSE_PER_TREATMENT_PERIOD)) {
            if (is_array($decoded->maxDosePerTreatmentPeriod)) {
                $type->setMaxDosePerTreatmentPeriod(FHIRRatio::jsonUnserialize(reset($decoded->maxDosePerTreatmentPeriod), $config));
            } else {
                $type->setMaxDosePerTreatmentPeriod(FHIRRatio::jsonUnserialize($decoded->maxDosePerTreatmentPeriod, $config));
            }
        }
        if (isset($decoded->maxTreatmentPeriod) || property_exists($decoded, self::FIELD_MAX_TREATMENT_PERIOD)) {
            if (is_array($decoded->maxTreatmentPeriod)) {
                $type->setMaxTreatmentPeriod(FHIRDuration::jsonUnserialize(reset($decoded->maxTreatmentPeriod), $config));
            } else {
                $type->setMaxTreatmentPeriod(FHIRDuration::jsonUnserialize($decoded->maxTreatmentPeriod, $config));
            }
        }
        if (isset($decoded->targetSpecies) || property_exists($decoded, self::FIELD_TARGET_SPECIES)) {
            if (is_object($decoded->targetSpecies)) {
                $vals = [$decoded->targetSpecies];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_TARGET_SPECIES, true);
            } else {
                $vals = $decoded->targetSpecies;
            }
            foreach($vals as $v) {
                $type->addTargetSpecies(FHIRMedicinalProductPharmaceuticalTargetSpecies::jsonUnserialize($v, $config));
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
        if (isset($this->code)) {
            $out->code = $this->code;
        }
        if (isset($this->firstDose)) {
            $out->firstDose = $this->firstDose;
        }
        if (isset($this->maxSingleDose)) {
            $out->maxSingleDose = $this->maxSingleDose;
        }
        if (isset($this->maxDosePerDay)) {
            $out->maxDosePerDay = $this->maxDosePerDay;
        }
        if (isset($this->maxDosePerTreatmentPeriod)) {
            $out->maxDosePerTreatmentPeriod = $this->maxDosePerTreatmentPeriod;
        }
        if (isset($this->maxTreatmentPeriod)) {
            $out->maxTreatmentPeriod = $this->maxTreatmentPeriod;
        }
        if (isset($this->targetSpecies) && [] !== $this->targetSpecies) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_TARGET_SPECIES) && 1 === count($this->targetSpecies)) {
                $out->targetSpecies = $this->targetSpecies[0];
            } else {
                $out->targetSpecies = $this->targetSpecies;
            }
        }
        return $out;
    }
}
