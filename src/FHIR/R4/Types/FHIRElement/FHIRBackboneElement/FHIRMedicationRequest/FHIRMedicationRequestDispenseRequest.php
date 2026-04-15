<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationRequest;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUnsignedIntPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * An order or request for both supply of the medication and the instructions for
 * administration of the medication to a patient. The resource is called
 * "MedicationRequest" rather than "MedicationPrescription" or "MedicationOrder" to
 * generalize the use across inpatient and outpatient settings, including care
 * plans, etc., and to harmonize with workflow patterns.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRMedicationRequestDispenseRequest extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_MEDICATION_REQUEST_DOT_DISPENSE_REQUEST;

    /* class_default.php:56 */
    public const FIELD_INITIAL_FILL = 'initialFill';
    public const FIELD_DISPENSE_INTERVAL = 'dispenseInterval';
    public const FIELD_VALIDITY_PERIOD = 'validityPeriod';
    public const FIELD_NUMBER_OF_REPEATS_ALLOWED = 'numberOfRepeatsAllowed';
    public const FIELD_NUMBER_OF_REPEATS_ALLOWED_EXT = '_numberOfRepeatsAllowed';
    public const FIELD_QUANTITY = 'quantity';
    public const FIELD_EXPECTED_SUPPLY_DURATION = 'expectedSupplyDuration';
    public const FIELD_PERFORMER = 'performer';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_NUMBER_OF_REPEATS_ALLOWED => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * An order or request for both supply of the medication and the instructions for
     * administration of the medication to a patient. The resource is called
     * "MedicationRequest" rather than "MedicationPrescription" or "MedicationOrder" to
     * generalize the use across inpatient and outpatient settings, including care
     * plans, etc., and to harmonize with workflow patterns.
     *
     * Indicates the quantity or duration for the first dispense of the medication.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationRequest\FHIRMedicationRequestInitialFill
     */
    #[FHIRMedicationRequestInitialFill]
    protected FHIRMedicationRequestInitialFill $initialFill;
    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The minimum period of time that must occur between dispenses of the medication.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration
     */
    #[FHIRDuration]
    protected FHIRDuration $dispenseInterval;
    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * This indicates the validity period of a prescription (stale dating the
     * Prescription).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    #[FHIRPeriod]
    protected FHIRPeriod $validityPeriod;
    /**
     * An integer with a value that is not negative (e.g. >= 0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * An integer indicating the number of times, in addition to the original dispense,
     * (aka refills or repeats) that the patient can receive the prescribed medication.
     * Usage Notes: This integer does not include the original order dispense. This
     * means that if an order indicates dispense 30 tablets plus "3 repeats", then the
     * order can be dispensed a total of 4 times and the patient can receive a total of
     * 120 tablets. A prescriber may explicitly say that zero refills are permitted
     * after the initial dispense.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt
     */
    #[FHIRUnsignedInt]
    protected FHIRUnsignedInt $numberOfRepeatsAllowed;
    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The amount that is to be dispensed for one fill.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    #[FHIRQuantity]
    protected FHIRQuantity $quantity;
    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies the period time over which the supplied product is expected to be
     * used, or the length of time the dispense is expected to last.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration
     */
    #[FHIRDuration]
    protected FHIRDuration $expectedSupplyDuration;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates the intended dispensing Organization specified by the prescriber.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $performer;

    /* constructor.php:61 */
    /**
     * FHIRMedicationRequestDispenseRequest Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationRequest\FHIRMedicationRequestInitialFill $initialFill
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration $dispenseInterval
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $validityPeriod
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRUnsignedIntPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt $numberOfRepeatsAllowed
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $quantity
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration $expectedSupplyDuration
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $performer
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|FHIRMedicationRequestInitialFill $initialFill = null,
                                null|FHIRDuration $dispenseInterval = null,
                                null|FHIRPeriod $validityPeriod = null,
                                null|string|int|float|FHIRUnsignedIntPrimitive|FHIRUnsignedInt $numberOfRepeatsAllowed = null,
                                null|FHIRQuantity $quantity = null,
                                null|FHIRDuration $expectedSupplyDuration = null,
                                null|FHIRReference $performer = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $initialFill) {
            $this->setInitialFill($initialFill);
        }
        if (null !== $dispenseInterval) {
            $this->setDispenseInterval($dispenseInterval);
        }
        if (null !== $validityPeriod) {
            $this->setValidityPeriod($validityPeriod);
        }
        if (null !== $numberOfRepeatsAllowed) {
            $this->setNumberOfRepeatsAllowed($numberOfRepeatsAllowed);
        }
        if (null !== $quantity) {
            $this->setQuantity($quantity);
        }
        if (null !== $expectedSupplyDuration) {
            $this->setExpectedSupplyDuration($expectedSupplyDuration);
        }
        if (null !== $performer) {
            $this->setPerformer($performer);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * An order or request for both supply of the medication and the instructions for
     * administration of the medication to a patient. The resource is called
     * "MedicationRequest" rather than "MedicationPrescription" or "MedicationOrder" to
     * generalize the use across inpatient and outpatient settings, including care
     * plans, etc., and to harmonize with workflow patterns.
     *
     * Indicates the quantity or duration for the first dispense of the medication.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationRequest\FHIRMedicationRequestInitialFill
     */
    public function getInitialFill(): null|FHIRMedicationRequestInitialFill
    {
        return $this->initialFill ?? null;
    }

    /**
     * An order or request for both supply of the medication and the instructions for
     * administration of the medication to a patient. The resource is called
     * "MedicationRequest" rather than "MedicationPrescription" or "MedicationOrder" to
     * generalize the use across inpatient and outpatient settings, including care
     * plans, etc., and to harmonize with workflow patterns.
     *
     * Indicates the quantity or duration for the first dispense of the medication.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationRequest\FHIRMedicationRequestInitialFill $initialFill
     * @return static
     */
    public function setInitialFill(null|FHIRMedicationRequestInitialFill $initialFill): self
    {
        if (null === $initialFill) {
            unset($this->initialFill);
            return $this;
        }
        $this->initialFill = $initialFill;
        return $this;
    }

    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The minimum period of time that must occur between dispenses of the medication.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public function getDispenseInterval(): null|FHIRDuration
    {
        return $this->dispenseInterval ?? null;
    }

    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The minimum period of time that must occur between dispenses of the medication.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration $dispenseInterval
     * @return static
     */
    public function setDispenseInterval(null|FHIRDuration $dispenseInterval): self
    {
        if (null === $dispenseInterval) {
            unset($this->dispenseInterval);
            return $this;
        }
        $this->dispenseInterval = $dispenseInterval;
        return $this;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * This indicates the validity period of a prescription (stale dating the
     * Prescription).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    public function getValidityPeriod(): null|FHIRPeriod
    {
        return $this->validityPeriod ?? null;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * This indicates the validity period of a prescription (stale dating the
     * Prescription).
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $validityPeriod
     * @return static
     */
    public function setValidityPeriod(null|FHIRPeriod $validityPeriod): self
    {
        if (null === $validityPeriod) {
            unset($this->validityPeriod);
            return $this;
        }
        $this->validityPeriod = $validityPeriod;
        return $this;
    }

    /**
     * An integer with a value that is not negative (e.g. >= 0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * An integer indicating the number of times, in addition to the original dispense,
     * (aka refills or repeats) that the patient can receive the prescribed medication.
     * Usage Notes: This integer does not include the original order dispense. This
     * means that if an order indicates dispense 30 tablets plus "3 repeats", then the
     * order can be dispensed a total of 4 times and the patient can receive a total of
     * 120 tablets. A prescriber may explicitly say that zero refills are permitted
     * after the initial dispense.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt
     */
    public function getNumberOfRepeatsAllowed(): null|FHIRUnsignedInt
    {
        return $this->numberOfRepeatsAllowed ?? null;
    }

    /**
     * An integer with a value that is not negative (e.g. >= 0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * An integer indicating the number of times, in addition to the original dispense,
     * (aka refills or repeats) that the patient can receive the prescribed medication.
     * Usage Notes: This integer does not include the original order dispense. This
     * means that if an order indicates dispense 30 tablets plus "3 repeats", then the
     * order can be dispensed a total of 4 times and the patient can receive a total of
     * 120 tablets. A prescriber may explicitly say that zero refills are permitted
     * after the initial dispense.
     *
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRUnsignedIntPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt $numberOfRepeatsAllowed
     * @return static
     */
    public function setNumberOfRepeatsAllowed(null|string|int|float|FHIRUnsignedIntPrimitive|FHIRUnsignedInt $numberOfRepeatsAllowed): self
    {
        if (null === $numberOfRepeatsAllowed) {
            unset($this->numberOfRepeatsAllowed);
            return $this;
        }
        if (!($numberOfRepeatsAllowed instanceof FHIRUnsignedInt)) {
            $numberOfRepeatsAllowed = new FHIRUnsignedInt(value: $numberOfRepeatsAllowed);
        }
        $this->numberOfRepeatsAllowed = $numberOfRepeatsAllowed;
        return $this;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The amount that is to be dispensed for one fill.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    public function getQuantity(): null|FHIRQuantity
    {
        return $this->quantity ?? null;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The amount that is to be dispensed for one fill.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $quantity
     * @return static
     */
    public function setQuantity(null|FHIRQuantity $quantity): self
    {
        if (null === $quantity) {
            unset($this->quantity);
            return $this;
        }
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies the period time over which the supplied product is expected to be
     * used, or the length of time the dispense is expected to last.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public function getExpectedSupplyDuration(): null|FHIRDuration
    {
        return $this->expectedSupplyDuration ?? null;
    }

    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies the period time over which the supplied product is expected to be
     * used, or the length of time the dispense is expected to last.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration $expectedSupplyDuration
     * @return static
     */
    public function setExpectedSupplyDuration(null|FHIRDuration $expectedSupplyDuration): self
    {
        if (null === $expectedSupplyDuration) {
            unset($this->expectedSupplyDuration);
            return $this;
        }
        $this->expectedSupplyDuration = $expectedSupplyDuration;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates the intended dispensing Organization specified by the prescriber.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getPerformer(): null|FHIRReference
    {
        return $this->performer ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates the intended dispensing Organization specified by the prescriber.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $performer
     * @return static
     */
    public function setPerformer(null|FHIRReference $performer): self
    {
        if (null === $performer) {
            unset($this->performer);
            return $this;
        }
        $this->performer = $performer;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationRequest\FHIRMedicationRequestDispenseRequest $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationRequest\FHIRMedicationRequestDispenseRequest
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRMedicationRequestDispenseRequest)) {
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
            } else if (self::FIELD_INITIAL_FILL === $cen) {
                $type->setInitialFill(FHIRMedicationRequestInitialFill::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DISPENSE_INTERVAL === $cen) {
                $type->setDispenseInterval(FHIRDuration::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALIDITY_PERIOD === $cen) {
                $type->setValidityPeriod(FHIRPeriod::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NUMBER_OF_REPEATS_ALLOWED === $cen) {
                $type->setNumberOfRepeatsAllowed(FHIRUnsignedInt::xmlUnserialize($ce, $config));
            } else if (self::FIELD_QUANTITY === $cen) {
                $type->setQuantity(FHIRQuantity::xmlUnserialize($ce, $config));
            } else if (self::FIELD_EXPECTED_SUPPLY_DURATION === $cen) {
                $type->setExpectedSupplyDuration(FHIRDuration::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PERFORMER === $cen) {
                $type->setPerformer(FHIRReference::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_NUMBER_OF_REPEATS_ALLOWED])) {
            if (isset($type->numberOfRepeatsAllowed)) {
                $type->numberOfRepeatsAllowed->setValue((string)$attributes[self::FIELD_NUMBER_OF_REPEATS_ALLOWED]);
            } else {
                $type->setNumberOfRepeatsAllowed((string)$attributes[self::FIELD_NUMBER_OF_REPEATS_ALLOWED]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_NUMBER_OF_REPEATS_ALLOWED, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->numberOfRepeatsAllowed) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_NUMBER_OF_REPEATS_ALLOWED]) {
            $xw->writeAttribute(self::FIELD_NUMBER_OF_REPEATS_ALLOWED, $this->numberOfRepeatsAllowed->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->initialFill)) {
            $xw->startElement(self::FIELD_INITIAL_FILL);
            $this->initialFill->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->dispenseInterval)) {
            $xw->startElement(self::FIELD_DISPENSE_INTERVAL);
            $this->dispenseInterval->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->validityPeriod)) {
            $xw->startElement(self::FIELD_VALIDITY_PERIOD);
            $this->validityPeriod->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->numberOfRepeatsAllowed)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_NUMBER_OF_REPEATS_ALLOWED]
                || $this->numberOfRepeatsAllowed->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_NUMBER_OF_REPEATS_ALLOWED);
            $this->numberOfRepeatsAllowed->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_NUMBER_OF_REPEATS_ALLOWED]);
            $xw->endElement();
        }
        if (isset($this->quantity)) {
            $xw->startElement(self::FIELD_QUANTITY);
            $this->quantity->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->expectedSupplyDuration)) {
            $xw->startElement(self::FIELD_EXPECTED_SUPPLY_DURATION);
            $this->expectedSupplyDuration->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->performer)) {
            $xw->startElement(self::FIELD_PERFORMER);
            $this->performer->xmlSerialize($xw, $config);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationRequest\FHIRMedicationRequestDispenseRequest $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicationRequest\FHIRMedicationRequestDispenseRequest
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
        } else if (!($type instanceof FHIRMedicationRequestDispenseRequest)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->initialFill) || property_exists($decoded, self::FIELD_INITIAL_FILL)) {
            if (is_array($decoded->initialFill)) {
                $type->setInitialFill(FHIRMedicationRequestInitialFill::jsonUnserialize(reset($decoded->initialFill), $config));
            } else {
                $type->setInitialFill(FHIRMedicationRequestInitialFill::jsonUnserialize($decoded->initialFill, $config));
            }
        }
        if (isset($decoded->dispenseInterval) || property_exists($decoded, self::FIELD_DISPENSE_INTERVAL)) {
            if (is_array($decoded->dispenseInterval)) {
                $type->setDispenseInterval(FHIRDuration::jsonUnserialize(reset($decoded->dispenseInterval), $config));
            } else {
                $type->setDispenseInterval(FHIRDuration::jsonUnserialize($decoded->dispenseInterval, $config));
            }
        }
        if (isset($decoded->validityPeriod) || property_exists($decoded, self::FIELD_VALIDITY_PERIOD)) {
            if (is_array($decoded->validityPeriod)) {
                $type->setValidityPeriod(FHIRPeriod::jsonUnserialize(reset($decoded->validityPeriod), $config));
            } else {
                $type->setValidityPeriod(FHIRPeriod::jsonUnserialize($decoded->validityPeriod, $config));
            }
        }
        if (isset($decoded->numberOfRepeatsAllowed)
            || isset($decoded->_numberOfRepeatsAllowed)
            || property_exists($decoded, self::FIELD_NUMBER_OF_REPEATS_ALLOWED)
            || property_exists($decoded, self::FIELD_NUMBER_OF_REPEATS_ALLOWED_EXT)) {
            $v = $decoded->_numberOfRepeatsAllowed ?? new \stdClass();
            $v->value = $decoded->numberOfRepeatsAllowed ?? null;
            $type->setNumberOfRepeatsAllowed(FHIRUnsignedInt::jsonUnserialize($v, $config));
        }
        if (isset($decoded->quantity) || property_exists($decoded, self::FIELD_QUANTITY)) {
            if (is_array($decoded->quantity)) {
                $type->setQuantity(FHIRQuantity::jsonUnserialize(reset($decoded->quantity), $config));
            } else {
                $type->setQuantity(FHIRQuantity::jsonUnserialize($decoded->quantity, $config));
            }
        }
        if (isset($decoded->expectedSupplyDuration) || property_exists($decoded, self::FIELD_EXPECTED_SUPPLY_DURATION)) {
            if (is_array($decoded->expectedSupplyDuration)) {
                $type->setExpectedSupplyDuration(FHIRDuration::jsonUnserialize(reset($decoded->expectedSupplyDuration), $config));
            } else {
                $type->setExpectedSupplyDuration(FHIRDuration::jsonUnserialize($decoded->expectedSupplyDuration, $config));
            }
        }
        if (isset($decoded->performer) || property_exists($decoded, self::FIELD_PERFORMER)) {
            if (is_array($decoded->performer)) {
                $type->setPerformer(FHIRReference::jsonUnserialize(reset($decoded->performer), $config));
            } else {
                $type->setPerformer(FHIRReference::jsonUnserialize($decoded->performer, $config));
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
        if (isset($this->initialFill)) {
            $out->initialFill = $this->initialFill;
        }
        if (isset($this->dispenseInterval)) {
            $out->dispenseInterval = $this->dispenseInterval;
        }
        if (isset($this->validityPeriod)) {
            $out->validityPeriod = $this->validityPeriod;
        }
        if (isset($this->numberOfRepeatsAllowed)) {
            if (null !== ($val = $this->numberOfRepeatsAllowed->getValue())) {
                $out->numberOfRepeatsAllowed = $val;
            }
            if ($this->numberOfRepeatsAllowed->_nonValueFieldDefined()) {
                $ext = $this->numberOfRepeatsAllowed->jsonSerialize();
                unset($ext->value);
                $out->_numberOfRepeatsAllowed = $ext;
            }
        }
        if (isset($this->quantity)) {
            $out->quantity = $this->quantity;
        }
        if (isset($this->expectedSupplyDuration)) {
            $out->expectedSupplyDuration = $this->expectedSupplyDuration;
        }
        if (isset($this->performer)) {
            $out->performer = $this->performer;
        }
        return $out;
    }
}
