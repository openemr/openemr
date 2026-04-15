<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVisionPrescription;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRVisionEyesList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRVisionEyes;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * An authorization for the provision of glasses and/or contact lenses to a
 * patient.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRVisionPrescriptionLensSpecification extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_VISION_PRESCRIPTION_DOT_LENS_SPECIFICATION;

    /* class_default.php:56 */
    public const FIELD_PRODUCT = 'product';
    public const FIELD_EYE = 'eye';
    public const FIELD_EYE_EXT = '_eye';
    public const FIELD_SPHERE = 'sphere';
    public const FIELD_SPHERE_EXT = '_sphere';
    public const FIELD_CYLINDER = 'cylinder';
    public const FIELD_CYLINDER_EXT = '_cylinder';
    public const FIELD_AXIS = 'axis';
    public const FIELD_AXIS_EXT = '_axis';
    public const FIELD_PRISM = 'prism';
    public const FIELD_ADD = 'add';
    public const FIELD_ADD_EXT = '_add';
    public const FIELD_POWER = 'power';
    public const FIELD_POWER_EXT = '_power';
    public const FIELD_BACK_CURVE = 'backCurve';
    public const FIELD_BACK_CURVE_EXT = '_backCurve';
    public const FIELD_DIAMETER = 'diameter';
    public const FIELD_DIAMETER_EXT = '_diameter';
    public const FIELD_DURATION = 'duration';
    public const FIELD_COLOR = 'color';
    public const FIELD_COLOR_EXT = '_color';
    public const FIELD_BRAND = 'brand';
    public const FIELD_BRAND_EXT = '_brand';
    public const FIELD_NOTE = 'note';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_PRODUCT => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_EYE => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_EYE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_SPHERE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_CYLINDER => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_AXIS => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_ADD => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_POWER => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_BACK_CURVE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DIAMETER => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_COLOR => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_BRAND => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies the type of vision correction product which is required for the
     * patient.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $product;
    /**
     * A coded concept listing the eye codes.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The eye for which the lens specification applies.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRVisionEyes
     */
    #[FHIRVisionEyes]
    protected FHIRVisionEyes $eye;
    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Lens power measured in dioptres (0.25 units).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    #[FHIRDecimal]
    protected FHIRDecimal $sphere;
    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Power adjustment for astigmatism measured in dioptres (0.25 units).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    #[FHIRDecimal]
    protected FHIRDecimal $cylinder;
    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Adjustment for astigmatism measured in integer degrees.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger
     */
    #[FHIRInteger]
    protected FHIRInteger $axis;
    /**
     * An authorization for the provision of glasses and/or contact lenses to a
     * patient.
     *
     * Allows for adjustment on two axis.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVisionPrescription\FHIRVisionPrescriptionPrism>
     */
    #[FHIRVisionPrescriptionPrism]
    protected array $prism;
    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Power adjustment for multifocal lenses measured in dioptres (0.25 units).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    #[FHIRDecimal]
    protected FHIRDecimal $add;
    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Contact lens power measured in dioptres (0.25 units).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    #[FHIRDecimal]
    protected FHIRDecimal $power;
    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Back curvature measured in millimetres.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    #[FHIRDecimal]
    protected FHIRDecimal $backCurve;
    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Contact lens diameter measured in millimetres.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    #[FHIRDecimal]
    protected FHIRDecimal $diameter;
    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The recommended maximum wear period for the lens.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    #[FHIRQuantity]
    protected FHIRQuantity $duration;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Special color or pattern.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $color;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Brand recommendations or restrictions.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $brand;
    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Notes for special requirements such as coatings and lens materials.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation>
     */
    #[FHIRAnnotation]
    protected array $note;

    /* constructor.php:61 */
    /**
     * FHIRVisionPrescriptionLensSpecification Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $product
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRVisionEyesList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRVisionEyes $eye
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $sphere
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $cylinder
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $axis
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVisionPrescription\FHIRVisionPrescriptionPrism> $prism
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $add
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $power
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $backCurve
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $diameter
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $duration
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $color
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $brand
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation> $note
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|FHIRCodeableConcept $product = null,
                                null|string|FHIRVisionEyesList|FHIRVisionEyes $eye = null,
                                null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $sphere = null,
                                null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $cylinder = null,
                                null|string|float|FHIRIntegerPrimitive|FHIRInteger $axis = null,
                                null|iterable $prism = null,
                                null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $add = null,
                                null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $power = null,
                                null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $backCurve = null,
                                null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $diameter = null,
                                null|FHIRQuantity $duration = null,
                                null|string|FHIRStringPrimitive|FHIRString $color = null,
                                null|string|FHIRStringPrimitive|FHIRString $brand = null,
                                null|iterable $note = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $product) {
            $this->setProduct($product);
        }
        if (null !== $eye) {
            $this->setEye($eye);
        }
        if (null !== $sphere) {
            $this->setSphere($sphere);
        }
        if (null !== $cylinder) {
            $this->setCylinder($cylinder);
        }
        if (null !== $axis) {
            $this->setAxis($axis);
        }
        if (null !== $prism) {
            $this->setPrism(...$prism);
        }
        if (null !== $add) {
            $this->setAdd($add);
        }
        if (null !== $power) {
            $this->setPower($power);
        }
        if (null !== $backCurve) {
            $this->setBackCurve($backCurve);
        }
        if (null !== $diameter) {
            $this->setDiameter($diameter);
        }
        if (null !== $duration) {
            $this->setDuration($duration);
        }
        if (null !== $color) {
            $this->setColor($color);
        }
        if (null !== $brand) {
            $this->setBrand($brand);
        }
        if (null !== $note) {
            $this->setNote(...$note);
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
     * Identifies the type of vision correction product which is required for the
     * patient.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getProduct(): null|FHIRCodeableConcept
    {
        return $this->product ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies the type of vision correction product which is required for the
     * patient.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $product
     * @return static
     */
    public function setProduct(null|FHIRCodeableConcept $product): self
    {
        if (null === $product) {
            unset($this->product);
            return $this;
        }
        $this->product = $product;
        return $this;
    }

    /**
     * A coded concept listing the eye codes.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The eye for which the lens specification applies.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRVisionEyes
     */
    public function getEye(): null|FHIRVisionEyes
    {
        return $this->eye ?? null;
    }

    /**
     * A coded concept listing the eye codes.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The eye for which the lens specification applies.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRVisionEyesList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRVisionEyes $eye
     * @return static
     */
    public function setEye(null|string|FHIRVisionEyesList|FHIRVisionEyes $eye): self
    {
        if (null === $eye) {
            unset($this->eye);
            return $this;
        }
        if (!($eye instanceof FHIRVisionEyes)) {
            $eye = new FHIRVisionEyes(value: $eye);
        }
        $this->eye = $eye;
        return $this;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Lens power measured in dioptres (0.25 units).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    public function getSphere(): null|FHIRDecimal
    {
        return $this->sphere ?? null;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Lens power measured in dioptres (0.25 units).
     *
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $sphere
     * @return static
     */
    public function setSphere(null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $sphere): self
    {
        if (null === $sphere) {
            unset($this->sphere);
            return $this;
        }
        if (!($sphere instanceof FHIRDecimal)) {
            $sphere = new FHIRDecimal(value: $sphere);
        }
        $this->sphere = $sphere;
        return $this;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Power adjustment for astigmatism measured in dioptres (0.25 units).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    public function getCylinder(): null|FHIRDecimal
    {
        return $this->cylinder ?? null;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Power adjustment for astigmatism measured in dioptres (0.25 units).
     *
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $cylinder
     * @return static
     */
    public function setCylinder(null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $cylinder): self
    {
        if (null === $cylinder) {
            unset($this->cylinder);
            return $this;
        }
        if (!($cylinder instanceof FHIRDecimal)) {
            $cylinder = new FHIRDecimal(value: $cylinder);
        }
        $this->cylinder = $cylinder;
        return $this;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Adjustment for astigmatism measured in integer degrees.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger
     */
    public function getAxis(): null|FHIRInteger
    {
        return $this->axis ?? null;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Adjustment for astigmatism measured in integer degrees.
     *
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRIntegerPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInteger $axis
     * @return static
     */
    public function setAxis(null|string|float|FHIRIntegerPrimitive|FHIRInteger $axis): self
    {
        if (null === $axis) {
            unset($this->axis);
            return $this;
        }
        if (!($axis instanceof FHIRInteger)) {
            $axis = new FHIRInteger(value: $axis);
        }
        $this->axis = $axis;
        return $this;
    }

    /**
     * An authorization for the provision of glasses and/or contact lenses to a
     * patient.
     *
     * Allows for adjustment on two axis.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVisionPrescription\FHIRVisionPrescriptionPrism>
     */
    public function getPrism(): array
    {
        return $this->prism ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVisionPrescription\FHIRVisionPrescriptionPrism>
     */
    public function getPrismIterator(): iterable
    {
        if (!isset($this->prism)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->prism);
    }

    /**
     * An authorization for the provision of glasses and/or contact lenses to a
     * patient.
     *
     * Allows for adjustment on two axis.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVisionPrescription\FHIRVisionPrescriptionPrism $prism
     * @return static
     */
    public function addPrism(FHIRVisionPrescriptionPrism $prism): self
    {
        if (!isset($this->prism)) {
            $this->prism = [];
        }
        $this->prism[] = $prism;
        return $this;
    }

    /**
     * An authorization for the provision of glasses and/or contact lenses to a
     * patient.
     *
     * Allows for adjustment on two axis.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVisionPrescription\FHIRVisionPrescriptionPrism ...$prism
     * @return static
     */
    public function setPrism(FHIRVisionPrescriptionPrism ...$prism): self
    {
        if ([] === $prism) {
            unset($this->prism);
            return $this;
        }
        $this->prism = $prism;
        return $this;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Power adjustment for multifocal lenses measured in dioptres (0.25 units).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    public function getAdd(): null|FHIRDecimal
    {
        return $this->add ?? null;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Power adjustment for multifocal lenses measured in dioptres (0.25 units).
     *
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $add
     * @return static
     */
    public function setAdd(null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $add): self
    {
        if (null === $add) {
            unset($this->add);
            return $this;
        }
        if (!($add instanceof FHIRDecimal)) {
            $add = new FHIRDecimal(value: $add);
        }
        $this->add = $add;
        return $this;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Contact lens power measured in dioptres (0.25 units).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    public function getPower(): null|FHIRDecimal
    {
        return $this->power ?? null;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Contact lens power measured in dioptres (0.25 units).
     *
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $power
     * @return static
     */
    public function setPower(null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $power): self
    {
        if (null === $power) {
            unset($this->power);
            return $this;
        }
        if (!($power instanceof FHIRDecimal)) {
            $power = new FHIRDecimal(value: $power);
        }
        $this->power = $power;
        return $this;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Back curvature measured in millimetres.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    public function getBackCurve(): null|FHIRDecimal
    {
        return $this->backCurve ?? null;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Back curvature measured in millimetres.
     *
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $backCurve
     * @return static
     */
    public function setBackCurve(null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $backCurve): self
    {
        if (null === $backCurve) {
            unset($this->backCurve);
            return $this;
        }
        if (!($backCurve instanceof FHIRDecimal)) {
            $backCurve = new FHIRDecimal(value: $backCurve);
        }
        $this->backCurve = $backCurve;
        return $this;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Contact lens diameter measured in millimetres.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    public function getDiameter(): null|FHIRDecimal
    {
        return $this->diameter ?? null;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Contact lens diameter measured in millimetres.
     *
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $diameter
     * @return static
     */
    public function setDiameter(null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $diameter): self
    {
        if (null === $diameter) {
            unset($this->diameter);
            return $this;
        }
        if (!($diameter instanceof FHIRDecimal)) {
            $diameter = new FHIRDecimal(value: $diameter);
        }
        $this->diameter = $diameter;
        return $this;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The recommended maximum wear period for the lens.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    public function getDuration(): null|FHIRQuantity
    {
        return $this->duration ?? null;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The recommended maximum wear period for the lens.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $duration
     * @return static
     */
    public function setDuration(null|FHIRQuantity $duration): self
    {
        if (null === $duration) {
            unset($this->duration);
            return $this;
        }
        $this->duration = $duration;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Special color or pattern.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getColor(): null|FHIRString
    {
        return $this->color ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Special color or pattern.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $color
     * @return static
     */
    public function setColor(null|string|FHIRStringPrimitive|FHIRString $color): self
    {
        if (null === $color) {
            unset($this->color);
            return $this;
        }
        if (!($color instanceof FHIRString)) {
            $color = new FHIRString(value: $color);
        }
        $this->color = $color;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Brand recommendations or restrictions.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getBrand(): null|FHIRString
    {
        return $this->brand ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Brand recommendations or restrictions.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $brand
     * @return static
     */
    public function setBrand(null|string|FHIRStringPrimitive|FHIRString $brand): self
    {
        if (null === $brand) {
            unset($this->brand);
            return $this;
        }
        if (!($brand instanceof FHIRString)) {
            $brand = new FHIRString(value: $brand);
        }
        $this->brand = $brand;
        return $this;
    }

    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Notes for special requirements such as coatings and lens materials.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation>
     */
    public function getNote(): array
    {
        return $this->note ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation>
     */
    public function getNoteIterator(): iterable
    {
        if (!isset($this->note)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->note);
    }

    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Notes for special requirements such as coatings and lens materials.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation $note
     * @return static
     */
    public function addNote(FHIRAnnotation $note): self
    {
        if (!isset($this->note)) {
            $this->note = [];
        }
        $this->note[] = $note;
        return $this;
    }

    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Notes for special requirements such as coatings and lens materials.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation ...$note
     * @return static
     */
    public function setNote(FHIRAnnotation ...$note): self
    {
        if ([] === $note) {
            unset($this->note);
            return $this;
        }
        $this->note = $note;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVisionPrescription\FHIRVisionPrescriptionLensSpecification $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVisionPrescription\FHIRVisionPrescriptionLensSpecification
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRVisionPrescriptionLensSpecification)) {
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
            } else if (self::FIELD_PRODUCT === $cen) {
                $type->setProduct(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_EYE === $cen) {
                $type->setEye(FHIRVisionEyes::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SPHERE === $cen) {
                $type->setSphere(FHIRDecimal::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CYLINDER === $cen) {
                $type->setCylinder(FHIRDecimal::xmlUnserialize($ce, $config));
            } else if (self::FIELD_AXIS === $cen) {
                $type->setAxis(FHIRInteger::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PRISM === $cen) {
                $type->addPrism(FHIRVisionPrescriptionPrism::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ADD === $cen) {
                $type->setAdd(FHIRDecimal::xmlUnserialize($ce, $config));
            } else if (self::FIELD_POWER === $cen) {
                $type->setPower(FHIRDecimal::xmlUnserialize($ce, $config));
            } else if (self::FIELD_BACK_CURVE === $cen) {
                $type->setBackCurve(FHIRDecimal::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DIAMETER === $cen) {
                $type->setDiameter(FHIRDecimal::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DURATION === $cen) {
                $type->setDuration(FHIRQuantity::xmlUnserialize($ce, $config));
            } else if (self::FIELD_COLOR === $cen) {
                $type->setColor(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_BRAND === $cen) {
                $type->setBrand(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NOTE === $cen) {
                $type->addNote(FHIRAnnotation::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_EYE])) {
            if (isset($type->eye)) {
                $type->eye->setValue((string)$attributes[self::FIELD_EYE]);
            } else {
                $type->setEye((string)$attributes[self::FIELD_EYE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_EYE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_SPHERE])) {
            if (isset($type->sphere)) {
                $type->sphere->setValue((string)$attributes[self::FIELD_SPHERE]);
            } else {
                $type->setSphere((string)$attributes[self::FIELD_SPHERE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_SPHERE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_CYLINDER])) {
            if (isset($type->cylinder)) {
                $type->cylinder->setValue((string)$attributes[self::FIELD_CYLINDER]);
            } else {
                $type->setCylinder((string)$attributes[self::FIELD_CYLINDER]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_CYLINDER, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_AXIS])) {
            if (isset($type->axis)) {
                $type->axis->setValue((string)$attributes[self::FIELD_AXIS]);
            } else {
                $type->setAxis((string)$attributes[self::FIELD_AXIS]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_AXIS, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_ADD])) {
            if (isset($type->add)) {
                $type->add->setValue((string)$attributes[self::FIELD_ADD]);
            } else {
                $type->setAdd((string)$attributes[self::FIELD_ADD]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_ADD, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_POWER])) {
            if (isset($type->power)) {
                $type->power->setValue((string)$attributes[self::FIELD_POWER]);
            } else {
                $type->setPower((string)$attributes[self::FIELD_POWER]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_POWER, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_BACK_CURVE])) {
            if (isset($type->backCurve)) {
                $type->backCurve->setValue((string)$attributes[self::FIELD_BACK_CURVE]);
            } else {
                $type->setBackCurve((string)$attributes[self::FIELD_BACK_CURVE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_BACK_CURVE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_DIAMETER])) {
            if (isset($type->diameter)) {
                $type->diameter->setValue((string)$attributes[self::FIELD_DIAMETER]);
            } else {
                $type->setDiameter((string)$attributes[self::FIELD_DIAMETER]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DIAMETER, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_COLOR])) {
            if (isset($type->color)) {
                $type->color->setValue((string)$attributes[self::FIELD_COLOR]);
            } else {
                $type->setColor((string)$attributes[self::FIELD_COLOR]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_COLOR, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_BRAND])) {
            if (isset($type->brand)) {
                $type->brand->setValue((string)$attributes[self::FIELD_BRAND]);
            } else {
                $type->setBrand((string)$attributes[self::FIELD_BRAND]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_BRAND, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->eye) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_EYE]) {
            $xw->writeAttribute(self::FIELD_EYE, $this->eye->_getValueAsString());
        }
        if (isset($this->sphere) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_SPHERE]) {
            $xw->writeAttribute(self::FIELD_SPHERE, $this->sphere->_getValueAsString());
        }
        if (isset($this->cylinder) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_CYLINDER]) {
            $xw->writeAttribute(self::FIELD_CYLINDER, $this->cylinder->_getValueAsString());
        }
        if (isset($this->axis) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_AXIS]) {
            $xw->writeAttribute(self::FIELD_AXIS, $this->axis->_getValueAsString());
        }
        if (isset($this->add) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_ADD]) {
            $xw->writeAttribute(self::FIELD_ADD, $this->add->_getValueAsString());
        }
        if (isset($this->power) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_POWER]) {
            $xw->writeAttribute(self::FIELD_POWER, $this->power->_getValueAsString());
        }
        if (isset($this->backCurve) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_BACK_CURVE]) {
            $xw->writeAttribute(self::FIELD_BACK_CURVE, $this->backCurve->_getValueAsString());
        }
        if (isset($this->diameter) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DIAMETER]) {
            $xw->writeAttribute(self::FIELD_DIAMETER, $this->diameter->_getValueAsString());
        }
        if (isset($this->color) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_COLOR]) {
            $xw->writeAttribute(self::FIELD_COLOR, $this->color->_getValueAsString());
        }
        if (isset($this->brand) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_BRAND]) {
            $xw->writeAttribute(self::FIELD_BRAND, $this->brand->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->product)) {
            $xw->startElement(self::FIELD_PRODUCT);
            $this->product->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->eye)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_EYE]
                || $this->eye->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_EYE);
            $this->eye->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_EYE]);
            $xw->endElement();
        }
        if (isset($this->sphere)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_SPHERE]
                || $this->sphere->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_SPHERE);
            $this->sphere->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_SPHERE]);
            $xw->endElement();
        }
        if (isset($this->cylinder)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_CYLINDER]
                || $this->cylinder->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_CYLINDER);
            $this->cylinder->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_CYLINDER]);
            $xw->endElement();
        }
        if (isset($this->axis)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_AXIS]
                || $this->axis->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_AXIS);
            $this->axis->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_AXIS]);
            $xw->endElement();
        }
        if (isset($this->prism)) {
            foreach ($this->prism as $v) {
                $xw->startElement(self::FIELD_PRISM);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->add)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_ADD]
                || $this->add->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_ADD);
            $this->add->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_ADD]);
            $xw->endElement();
        }
        if (isset($this->power)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_POWER]
                || $this->power->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_POWER);
            $this->power->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_POWER]);
            $xw->endElement();
        }
        if (isset($this->backCurve)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_BACK_CURVE]
                || $this->backCurve->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_BACK_CURVE);
            $this->backCurve->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_BACK_CURVE]);
            $xw->endElement();
        }
        if (isset($this->diameter)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DIAMETER]
                || $this->diameter->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DIAMETER);
            $this->diameter->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DIAMETER]);
            $xw->endElement();
        }
        if (isset($this->duration)) {
            $xw->startElement(self::FIELD_DURATION);
            $this->duration->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->color)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_COLOR]
                || $this->color->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_COLOR);
            $this->color->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_COLOR]);
            $xw->endElement();
        }
        if (isset($this->brand)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_BRAND]
                || $this->brand->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_BRAND);
            $this->brand->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_BRAND]);
            $xw->endElement();
        }
        if (isset($this->note)) {
            foreach ($this->note as $v) {
                $xw->startElement(self::FIELD_NOTE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVisionPrescription\FHIRVisionPrescriptionLensSpecification $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRVisionPrescription\FHIRVisionPrescriptionLensSpecification
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
        } else if (!($type instanceof FHIRVisionPrescriptionLensSpecification)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->product) || property_exists($decoded, self::FIELD_PRODUCT)) {
            if (is_array($decoded->product)) {
                $type->setProduct(FHIRCodeableConcept::jsonUnserialize(reset($decoded->product), $config));
            } else {
                $type->setProduct(FHIRCodeableConcept::jsonUnserialize($decoded->product, $config));
            }
        }
        if (isset($decoded->eye)
            || isset($decoded->_eye)
            || property_exists($decoded, self::FIELD_EYE)
            || property_exists($decoded, self::FIELD_EYE_EXT)) {
            $v = $decoded->_eye ?? new \stdClass();
            $v->value = $decoded->eye ?? null;
            $type->setEye(FHIRVisionEyes::jsonUnserialize($v, $config));
        }
        if (isset($decoded->sphere)
            || isset($decoded->_sphere)
            || property_exists($decoded, self::FIELD_SPHERE)
            || property_exists($decoded, self::FIELD_SPHERE_EXT)) {
            $v = $decoded->_sphere ?? new \stdClass();
            $v->value = $decoded->sphere ?? null;
            $type->setSphere(FHIRDecimal::jsonUnserialize($v, $config));
        }
        if (isset($decoded->cylinder)
            || isset($decoded->_cylinder)
            || property_exists($decoded, self::FIELD_CYLINDER)
            || property_exists($decoded, self::FIELD_CYLINDER_EXT)) {
            $v = $decoded->_cylinder ?? new \stdClass();
            $v->value = $decoded->cylinder ?? null;
            $type->setCylinder(FHIRDecimal::jsonUnserialize($v, $config));
        }
        if (isset($decoded->axis)
            || isset($decoded->_axis)
            || property_exists($decoded, self::FIELD_AXIS)
            || property_exists($decoded, self::FIELD_AXIS_EXT)) {
            $v = $decoded->_axis ?? new \stdClass();
            $v->value = $decoded->axis ?? null;
            $type->setAxis(FHIRInteger::jsonUnserialize($v, $config));
        }
        if (isset($decoded->prism) || property_exists($decoded, self::FIELD_PRISM)) {
            if (is_object($decoded->prism)) {
                $vals = [$decoded->prism];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PRISM, true);
            } else {
                $vals = $decoded->prism;
            }
            foreach($vals as $v) {
                $type->addPrism(FHIRVisionPrescriptionPrism::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->add)
            || isset($decoded->_add)
            || property_exists($decoded, self::FIELD_ADD)
            || property_exists($decoded, self::FIELD_ADD_EXT)) {
            $v = $decoded->_add ?? new \stdClass();
            $v->value = $decoded->add ?? null;
            $type->setAdd(FHIRDecimal::jsonUnserialize($v, $config));
        }
        if (isset($decoded->power)
            || isset($decoded->_power)
            || property_exists($decoded, self::FIELD_POWER)
            || property_exists($decoded, self::FIELD_POWER_EXT)) {
            $v = $decoded->_power ?? new \stdClass();
            $v->value = $decoded->power ?? null;
            $type->setPower(FHIRDecimal::jsonUnserialize($v, $config));
        }
        if (isset($decoded->backCurve)
            || isset($decoded->_backCurve)
            || property_exists($decoded, self::FIELD_BACK_CURVE)
            || property_exists($decoded, self::FIELD_BACK_CURVE_EXT)) {
            $v = $decoded->_backCurve ?? new \stdClass();
            $v->value = $decoded->backCurve ?? null;
            $type->setBackCurve(FHIRDecimal::jsonUnserialize($v, $config));
        }
        if (isset($decoded->diameter)
            || isset($decoded->_diameter)
            || property_exists($decoded, self::FIELD_DIAMETER)
            || property_exists($decoded, self::FIELD_DIAMETER_EXT)) {
            $v = $decoded->_diameter ?? new \stdClass();
            $v->value = $decoded->diameter ?? null;
            $type->setDiameter(FHIRDecimal::jsonUnserialize($v, $config));
        }
        if (isset($decoded->duration) || property_exists($decoded, self::FIELD_DURATION)) {
            if (is_array($decoded->duration)) {
                $type->setDuration(FHIRQuantity::jsonUnserialize(reset($decoded->duration), $config));
            } else {
                $type->setDuration(FHIRQuantity::jsonUnserialize($decoded->duration, $config));
            }
        }
        if (isset($decoded->color)
            || isset($decoded->_color)
            || property_exists($decoded, self::FIELD_COLOR)
            || property_exists($decoded, self::FIELD_COLOR_EXT)) {
            $v = $decoded->_color ?? new \stdClass();
            $v->value = $decoded->color ?? null;
            $type->setColor(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->brand)
            || isset($decoded->_brand)
            || property_exists($decoded, self::FIELD_BRAND)
            || property_exists($decoded, self::FIELD_BRAND_EXT)) {
            $v = $decoded->_brand ?? new \stdClass();
            $v->value = $decoded->brand ?? null;
            $type->setBrand(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->note) || property_exists($decoded, self::FIELD_NOTE)) {
            if (is_object($decoded->note)) {
                $vals = [$decoded->note];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_NOTE, true);
            } else {
                $vals = $decoded->note;
            }
            foreach($vals as $v) {
                $type->addNote(FHIRAnnotation::jsonUnserialize($v, $config));
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
        if (isset($this->product)) {
            $out->product = $this->product;
        }
        if (isset($this->eye)) {
            if (null !== ($val = $this->eye->getValue())) {
                $out->eye = $val;
            }
            if ($this->eye->_nonValueFieldDefined()) {
                $ext = $this->eye->jsonSerialize();
                unset($ext->value);
                $out->_eye = $ext;
            }
        }
        if (isset($this->sphere)) {
            if (null !== ($val = $this->sphere->getValue())) {
                $out->sphere = $val;
            }
            if ($this->sphere->_nonValueFieldDefined()) {
                $ext = $this->sphere->jsonSerialize();
                unset($ext->value);
                $out->_sphere = $ext;
            }
        }
        if (isset($this->cylinder)) {
            if (null !== ($val = $this->cylinder->getValue())) {
                $out->cylinder = $val;
            }
            if ($this->cylinder->_nonValueFieldDefined()) {
                $ext = $this->cylinder->jsonSerialize();
                unset($ext->value);
                $out->_cylinder = $ext;
            }
        }
        if (isset($this->axis)) {
            if (null !== ($val = $this->axis->getValue())) {
                $out->axis = $val;
            }
            if ($this->axis->_nonValueFieldDefined()) {
                $ext = $this->axis->jsonSerialize();
                unset($ext->value);
                $out->_axis = $ext;
            }
        }
        if (isset($this->prism) && [] !== $this->prism) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PRISM) && 1 === count($this->prism)) {
                $out->prism = $this->prism[0];
            } else {
                $out->prism = $this->prism;
            }
        }
        if (isset($this->add)) {
            if (null !== ($val = $this->add->getValue())) {
                $out->add = $val;
            }
            if ($this->add->_nonValueFieldDefined()) {
                $ext = $this->add->jsonSerialize();
                unset($ext->value);
                $out->_add = $ext;
            }
        }
        if (isset($this->power)) {
            if (null !== ($val = $this->power->getValue())) {
                $out->power = $val;
            }
            if ($this->power->_nonValueFieldDefined()) {
                $ext = $this->power->jsonSerialize();
                unset($ext->value);
                $out->_power = $ext;
            }
        }
        if (isset($this->backCurve)) {
            if (null !== ($val = $this->backCurve->getValue())) {
                $out->backCurve = $val;
            }
            if ($this->backCurve->_nonValueFieldDefined()) {
                $ext = $this->backCurve->jsonSerialize();
                unset($ext->value);
                $out->_backCurve = $ext;
            }
        }
        if (isset($this->diameter)) {
            if (null !== ($val = $this->diameter->getValue())) {
                $out->diameter = $val;
            }
            if ($this->diameter->_nonValueFieldDefined()) {
                $ext = $this->diameter->jsonSerialize();
                unset($ext->value);
                $out->_diameter = $ext;
            }
        }
        if (isset($this->duration)) {
            $out->duration = $this->duration;
        }
        if (isset($this->color)) {
            if (null !== ($val = $this->color->getValue())) {
                $out->color = $val;
            }
            if ($this->color->_nonValueFieldDefined()) {
                $ext = $this->color->jsonSerialize();
                unset($ext->value);
                $out->_color = $ext;
            }
        }
        if (isset($this->brand)) {
            if (null !== ($val = $this->brand->getValue())) {
                $out->brand = $val;
            }
            if ($this->brand->_nonValueFieldDefined()) {
                $ext = $this->brand->jsonSerialize();
                unset($ext->value);
                $out->_brand = $ext;
            }
        }
        if (isset($this->note) && [] !== $this->note) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_NOTE) && 1 === count($this->note)) {
                $out->note = $this->note[0];
            } else {
                $out->note = $this->note;
            }
        }
        return $out;
    }
}
