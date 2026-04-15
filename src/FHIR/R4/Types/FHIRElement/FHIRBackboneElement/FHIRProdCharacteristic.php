<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * The marketing status describes the date when a medicinal product is actually put
 * on the market or the date as of which it is no longer available.
 * If the element is present, it must have a value for at least one of the defined
 * elements, an \@id referenced from the Narrative, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRProdCharacteristic extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_PROD_CHARACTERISTIC;

    /* class_default.php:56 */
    public const FIELD_HEIGHT = 'height';
    public const FIELD_WIDTH = 'width';
    public const FIELD_DEPTH = 'depth';
    public const FIELD_WEIGHT = 'weight';
    public const FIELD_NOMINAL_VOLUME = 'nominalVolume';
    public const FIELD_EXTERNAL_DIAMETER = 'externalDiameter';
    public const FIELD_SHAPE = 'shape';
    public const FIELD_SHAPE_EXT = '_shape';
    public const FIELD_COLOR = 'color';
    public const FIELD_COLOR_EXT = '_color';
    public const FIELD_IMPRINT = 'imprint';
    public const FIELD_IMPRINT_EXT = '_imprint';
    public const FIELD_IMAGE = 'image';
    public const FIELD_SCORING = 'scoring';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_SHAPE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Where applicable, the height can be specified using a numerical value and its
     * unit of measurement The unit of measurement shall be specified in accordance
     * with ISO 11240 and the resulting terminology The symbol and the symbol
     * identifier shall be used.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    #[FHIRQuantity]
    protected FHIRQuantity $height;
    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Where applicable, the width can be specified using a numerical value and its
     * unit of measurement The unit of measurement shall be specified in accordance
     * with ISO 11240 and the resulting terminology The symbol and the symbol
     * identifier shall be used.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    #[FHIRQuantity]
    protected FHIRQuantity $width;
    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Where applicable, the depth can be specified using a numerical value and its
     * unit of measurement The unit of measurement shall be specified in accordance
     * with ISO 11240 and the resulting terminology The symbol and the symbol
     * identifier shall be used.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    #[FHIRQuantity]
    protected FHIRQuantity $depth;
    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Where applicable, the weight can be specified using a numerical value and its
     * unit of measurement The unit of measurement shall be specified in accordance
     * with ISO 11240 and the resulting terminology The symbol and the symbol
     * identifier shall be used.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    #[FHIRQuantity]
    protected FHIRQuantity $weight;
    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Where applicable, the nominal volume can be specified using a numerical value
     * and its unit of measurement The unit of measurement shall be specified in
     * accordance with ISO 11240 and the resulting terminology The symbol and the
     * symbol identifier shall be used.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    #[FHIRQuantity]
    protected FHIRQuantity $nominalVolume;
    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Where applicable, the external diameter can be specified using a numerical value
     * and its unit of measurement The unit of measurement shall be specified in
     * accordance with ISO 11240 and the resulting terminology The symbol and the
     * symbol identifier shall be used.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    #[FHIRQuantity]
    protected FHIRQuantity $externalDiameter;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Where applicable, the shape can be specified An appropriate controlled
     * vocabulary shall be used The term and the term identifier shall be used.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $shape;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Where applicable, the color can be specified An appropriate controlled
     * vocabulary shall be used The term and the term identifier shall be used.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    #[FHIRString]
    protected array $color;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Where applicable, the imprint can be specified as text.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    #[FHIRString]
    protected array $imprint;
    /**
     * For referring to data content defined in other formats.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Where applicable, the image can be provided The format of the image attachment
     * shall be specified by regional implementations.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment>
     */
    #[FHIRAttachment]
    protected array $image;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Where applicable, the scoring can be specified An appropriate controlled
     * vocabulary shall be used The term and the term identifier shall be used.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $scoring;

    /* constructor.php:61 */
    /**
     * FHIRProdCharacteristic Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $height
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $width
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $depth
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $weight
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $nominalVolume
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $externalDiameter
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $shape
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString> $color
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString> $imprint
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment> $image
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $scoring
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|FHIRQuantity $height = null,
                                null|FHIRQuantity $width = null,
                                null|FHIRQuantity $depth = null,
                                null|FHIRQuantity $weight = null,
                                null|FHIRQuantity $nominalVolume = null,
                                null|FHIRQuantity $externalDiameter = null,
                                null|string|FHIRStringPrimitive|FHIRString $shape = null,
                                null|iterable $color = null,
                                null|iterable $imprint = null,
                                null|iterable $image = null,
                                null|FHIRCodeableConcept $scoring = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $height) {
            $this->setHeight($height);
        }
        if (null !== $width) {
            $this->setWidth($width);
        }
        if (null !== $depth) {
            $this->setDepth($depth);
        }
        if (null !== $weight) {
            $this->setWeight($weight);
        }
        if (null !== $nominalVolume) {
            $this->setNominalVolume($nominalVolume);
        }
        if (null !== $externalDiameter) {
            $this->setExternalDiameter($externalDiameter);
        }
        if (null !== $shape) {
            $this->setShape($shape);
        }
        if (null !== $color) {
            $this->setColor(...$color);
        }
        if (null !== $imprint) {
            $this->setImprint(...$imprint);
        }
        if (null !== $image) {
            $this->setImage(...$image);
        }
        if (null !== $scoring) {
            $this->setScoring($scoring);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Where applicable, the height can be specified using a numerical value and its
     * unit of measurement The unit of measurement shall be specified in accordance
     * with ISO 11240 and the resulting terminology The symbol and the symbol
     * identifier shall be used.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    public function getHeight(): null|FHIRQuantity
    {
        return $this->height ?? null;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Where applicable, the height can be specified using a numerical value and its
     * unit of measurement The unit of measurement shall be specified in accordance
     * with ISO 11240 and the resulting terminology The symbol and the symbol
     * identifier shall be used.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $height
     * @return static
     */
    public function setHeight(null|FHIRQuantity $height): self
    {
        if (null === $height) {
            unset($this->height);
            return $this;
        }
        $this->height = $height;
        return $this;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Where applicable, the width can be specified using a numerical value and its
     * unit of measurement The unit of measurement shall be specified in accordance
     * with ISO 11240 and the resulting terminology The symbol and the symbol
     * identifier shall be used.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    public function getWidth(): null|FHIRQuantity
    {
        return $this->width ?? null;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Where applicable, the width can be specified using a numerical value and its
     * unit of measurement The unit of measurement shall be specified in accordance
     * with ISO 11240 and the resulting terminology The symbol and the symbol
     * identifier shall be used.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $width
     * @return static
     */
    public function setWidth(null|FHIRQuantity $width): self
    {
        if (null === $width) {
            unset($this->width);
            return $this;
        }
        $this->width = $width;
        return $this;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Where applicable, the depth can be specified using a numerical value and its
     * unit of measurement The unit of measurement shall be specified in accordance
     * with ISO 11240 and the resulting terminology The symbol and the symbol
     * identifier shall be used.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    public function getDepth(): null|FHIRQuantity
    {
        return $this->depth ?? null;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Where applicable, the depth can be specified using a numerical value and its
     * unit of measurement The unit of measurement shall be specified in accordance
     * with ISO 11240 and the resulting terminology The symbol and the symbol
     * identifier shall be used.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $depth
     * @return static
     */
    public function setDepth(null|FHIRQuantity $depth): self
    {
        if (null === $depth) {
            unset($this->depth);
            return $this;
        }
        $this->depth = $depth;
        return $this;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Where applicable, the weight can be specified using a numerical value and its
     * unit of measurement The unit of measurement shall be specified in accordance
     * with ISO 11240 and the resulting terminology The symbol and the symbol
     * identifier shall be used.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    public function getWeight(): null|FHIRQuantity
    {
        return $this->weight ?? null;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Where applicable, the weight can be specified using a numerical value and its
     * unit of measurement The unit of measurement shall be specified in accordance
     * with ISO 11240 and the resulting terminology The symbol and the symbol
     * identifier shall be used.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $weight
     * @return static
     */
    public function setWeight(null|FHIRQuantity $weight): self
    {
        if (null === $weight) {
            unset($this->weight);
            return $this;
        }
        $this->weight = $weight;
        return $this;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Where applicable, the nominal volume can be specified using a numerical value
     * and its unit of measurement The unit of measurement shall be specified in
     * accordance with ISO 11240 and the resulting terminology The symbol and the
     * symbol identifier shall be used.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    public function getNominalVolume(): null|FHIRQuantity
    {
        return $this->nominalVolume ?? null;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Where applicable, the nominal volume can be specified using a numerical value
     * and its unit of measurement The unit of measurement shall be specified in
     * accordance with ISO 11240 and the resulting terminology The symbol and the
     * symbol identifier shall be used.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $nominalVolume
     * @return static
     */
    public function setNominalVolume(null|FHIRQuantity $nominalVolume): self
    {
        if (null === $nominalVolume) {
            unset($this->nominalVolume);
            return $this;
        }
        $this->nominalVolume = $nominalVolume;
        return $this;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Where applicable, the external diameter can be specified using a numerical value
     * and its unit of measurement The unit of measurement shall be specified in
     * accordance with ISO 11240 and the resulting terminology The symbol and the
     * symbol identifier shall be used.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    public function getExternalDiameter(): null|FHIRQuantity
    {
        return $this->externalDiameter ?? null;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Where applicable, the external diameter can be specified using a numerical value
     * and its unit of measurement The unit of measurement shall be specified in
     * accordance with ISO 11240 and the resulting terminology The symbol and the
     * symbol identifier shall be used.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $externalDiameter
     * @return static
     */
    public function setExternalDiameter(null|FHIRQuantity $externalDiameter): self
    {
        if (null === $externalDiameter) {
            unset($this->externalDiameter);
            return $this;
        }
        $this->externalDiameter = $externalDiameter;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Where applicable, the shape can be specified An appropriate controlled
     * vocabulary shall be used The term and the term identifier shall be used.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getShape(): null|FHIRString
    {
        return $this->shape ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Where applicable, the shape can be specified An appropriate controlled
     * vocabulary shall be used The term and the term identifier shall be used.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $shape
     * @return static
     */
    public function setShape(null|string|FHIRStringPrimitive|FHIRString $shape): self
    {
        if (null === $shape) {
            unset($this->shape);
            return $this;
        }
        if (!($shape instanceof FHIRString)) {
            $shape = new FHIRString(value: $shape);
        }
        $this->shape = $shape;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Where applicable, the color can be specified An appropriate controlled
     * vocabulary shall be used The term and the term identifier shall be used.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    public function getColor(): array
    {
        return $this->color ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    public function getColorIterator(): iterable
    {
        if (!isset($this->color)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->color);
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Where applicable, the color can be specified An appropriate controlled
     * vocabulary shall be used The term and the term identifier shall be used.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $color
     * @return static
     */
    public function addColor(string|FHIRStringPrimitive|FHIRString $color): self
    {
        if (!($color instanceof FHIRString)) {
            $color = new FHIRString(value: $color);
        }
        if (!isset($this->color)) {
            $this->color = [];
        }
        $this->color[] = $color;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Where applicable, the color can be specified An appropriate controlled
     * vocabulary shall be used The term and the term identifier shall be used.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString ...$color
     * @return static
     */
    public function setColor(string|FHIRStringPrimitive|FHIRString ...$color): self
    {
        if ([] === $color) {
            unset($this->color);
            return $this;
        }
        $this->color = [];
        foreach($color as $v) {
            if ($v instanceof FHIRString) {
                $this->color[] = $v;
            } else {
                $this->color[] = new FHIRString(value: $v);
            }
        }
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Where applicable, the imprint can be specified as text.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    public function getImprint(): array
    {
        return $this->imprint ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    public function getImprintIterator(): iterable
    {
        if (!isset($this->imprint)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->imprint);
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Where applicable, the imprint can be specified as text.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $imprint
     * @return static
     */
    public function addImprint(string|FHIRStringPrimitive|FHIRString $imprint): self
    {
        if (!($imprint instanceof FHIRString)) {
            $imprint = new FHIRString(value: $imprint);
        }
        if (!isset($this->imprint)) {
            $this->imprint = [];
        }
        $this->imprint[] = $imprint;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Where applicable, the imprint can be specified as text.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString ...$imprint
     * @return static
     */
    public function setImprint(string|FHIRStringPrimitive|FHIRString ...$imprint): self
    {
        if ([] === $imprint) {
            unset($this->imprint);
            return $this;
        }
        $this->imprint = [];
        foreach($imprint as $v) {
            if ($v instanceof FHIRString) {
                $this->imprint[] = $v;
            } else {
                $this->imprint[] = new FHIRString(value: $v);
            }
        }
        return $this;
    }

    /**
     * For referring to data content defined in other formats.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Where applicable, the image can be provided The format of the image attachment
     * shall be specified by regional implementations.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment>
     */
    public function getImage(): array
    {
        return $this->image ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment>
     */
    public function getImageIterator(): iterable
    {
        if (!isset($this->image)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->image);
    }

    /**
     * For referring to data content defined in other formats.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Where applicable, the image can be provided The format of the image attachment
     * shall be specified by regional implementations.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment $image
     * @return static
     */
    public function addImage(FHIRAttachment $image): self
    {
        if (!isset($this->image)) {
            $this->image = [];
        }
        $this->image[] = $image;
        return $this;
    }

    /**
     * For referring to data content defined in other formats.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Where applicable, the image can be provided The format of the image attachment
     * shall be specified by regional implementations.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment ...$image
     * @return static
     */
    public function setImage(FHIRAttachment ...$image): self
    {
        if ([] === $image) {
            unset($this->image);
            return $this;
        }
        $this->image = $image;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Where applicable, the scoring can be specified An appropriate controlled
     * vocabulary shall be used The term and the term identifier shall be used.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getScoring(): null|FHIRCodeableConcept
    {
        return $this->scoring ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Where applicable, the scoring can be specified An appropriate controlled
     * vocabulary shall be used The term and the term identifier shall be used.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $scoring
     * @return static
     */
    public function setScoring(null|FHIRCodeableConcept $scoring): self
    {
        if (null === $scoring) {
            unset($this->scoring);
            return $this;
        }
        $this->scoring = $scoring;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRProdCharacteristic $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRProdCharacteristic
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRProdCharacteristic)) {
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
            } else if (self::FIELD_HEIGHT === $cen) {
                $type->setHeight(FHIRQuantity::xmlUnserialize($ce, $config));
            } else if (self::FIELD_WIDTH === $cen) {
                $type->setWidth(FHIRQuantity::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DEPTH === $cen) {
                $type->setDepth(FHIRQuantity::xmlUnserialize($ce, $config));
            } else if (self::FIELD_WEIGHT === $cen) {
                $type->setWeight(FHIRQuantity::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NOMINAL_VOLUME === $cen) {
                $type->setNominalVolume(FHIRQuantity::xmlUnserialize($ce, $config));
            } else if (self::FIELD_EXTERNAL_DIAMETER === $cen) {
                $type->setExternalDiameter(FHIRQuantity::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SHAPE === $cen) {
                $type->setShape(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_COLOR === $cen) {
                $type->addColor(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_IMPRINT === $cen) {
                $type->addImprint(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_IMAGE === $cen) {
                $type->addImage(FHIRAttachment::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SCORING === $cen) {
                $type->setScoring(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_SHAPE])) {
            if (isset($type->shape)) {
                $type->shape->setValue((string)$attributes[self::FIELD_SHAPE]);
            } else {
                $type->setShape((string)$attributes[self::FIELD_SHAPE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_SHAPE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->shape) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_SHAPE]) {
            $xw->writeAttribute(self::FIELD_SHAPE, $this->shape->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->height)) {
            $xw->startElement(self::FIELD_HEIGHT);
            $this->height->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->width)) {
            $xw->startElement(self::FIELD_WIDTH);
            $this->width->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->depth)) {
            $xw->startElement(self::FIELD_DEPTH);
            $this->depth->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->weight)) {
            $xw->startElement(self::FIELD_WEIGHT);
            $this->weight->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->nominalVolume)) {
            $xw->startElement(self::FIELD_NOMINAL_VOLUME);
            $this->nominalVolume->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->externalDiameter)) {
            $xw->startElement(self::FIELD_EXTERNAL_DIAMETER);
            $this->externalDiameter->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->shape)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_SHAPE]
                || $this->shape->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_SHAPE);
            $this->shape->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_SHAPE]);
            $xw->endElement();
        }
        if (isset($this->color) && [] !== $this->color) {
            foreach($this->color as $v) {
                $xw->startElement(self::FIELD_COLOR);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->imprint) && [] !== $this->imprint) {
            foreach($this->imprint as $v) {
                $xw->startElement(self::FIELD_IMPRINT);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->image)) {
            foreach ($this->image as $v) {
                $xw->startElement(self::FIELD_IMAGE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->scoring)) {
            $xw->startElement(self::FIELD_SCORING);
            $this->scoring->xmlSerialize($xw, $config);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRProdCharacteristic $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRProdCharacteristic
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
        } else if (!($type instanceof FHIRProdCharacteristic)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->height) || property_exists($decoded, self::FIELD_HEIGHT)) {
            if (is_array($decoded->height)) {
                $type->setHeight(FHIRQuantity::jsonUnserialize(reset($decoded->height), $config));
            } else {
                $type->setHeight(FHIRQuantity::jsonUnserialize($decoded->height, $config));
            }
        }
        if (isset($decoded->width) || property_exists($decoded, self::FIELD_WIDTH)) {
            if (is_array($decoded->width)) {
                $type->setWidth(FHIRQuantity::jsonUnserialize(reset($decoded->width), $config));
            } else {
                $type->setWidth(FHIRQuantity::jsonUnserialize($decoded->width, $config));
            }
        }
        if (isset($decoded->depth) || property_exists($decoded, self::FIELD_DEPTH)) {
            if (is_array($decoded->depth)) {
                $type->setDepth(FHIRQuantity::jsonUnserialize(reset($decoded->depth), $config));
            } else {
                $type->setDepth(FHIRQuantity::jsonUnserialize($decoded->depth, $config));
            }
        }
        if (isset($decoded->weight) || property_exists($decoded, self::FIELD_WEIGHT)) {
            if (is_array($decoded->weight)) {
                $type->setWeight(FHIRQuantity::jsonUnserialize(reset($decoded->weight), $config));
            } else {
                $type->setWeight(FHIRQuantity::jsonUnserialize($decoded->weight, $config));
            }
        }
        if (isset($decoded->nominalVolume) || property_exists($decoded, self::FIELD_NOMINAL_VOLUME)) {
            if (is_array($decoded->nominalVolume)) {
                $type->setNominalVolume(FHIRQuantity::jsonUnserialize(reset($decoded->nominalVolume), $config));
            } else {
                $type->setNominalVolume(FHIRQuantity::jsonUnserialize($decoded->nominalVolume, $config));
            }
        }
        if (isset($decoded->externalDiameter) || property_exists($decoded, self::FIELD_EXTERNAL_DIAMETER)) {
            if (is_array($decoded->externalDiameter)) {
                $type->setExternalDiameter(FHIRQuantity::jsonUnserialize(reset($decoded->externalDiameter), $config));
            } else {
                $type->setExternalDiameter(FHIRQuantity::jsonUnserialize($decoded->externalDiameter, $config));
            }
        }
        if (isset($decoded->shape)
            || isset($decoded->_shape)
            || property_exists($decoded, self::FIELD_SHAPE)
            || property_exists($decoded, self::FIELD_SHAPE_EXT)) {
            $v = $decoded->_shape ?? new \stdClass();
            $v->value = $decoded->shape ?? null;
            $type->setShape(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->color)
            || isset($decoded->_color)
            || property_exists($decoded, self::FIELD_COLOR)
            || property_exists($decoded, self::FIELD_COLOR_EXT)) {
            $vals = (array)($decoded->color ?? []);
            $exts = (array)($decoded->_color ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addColor(FHIRString::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->imprint)
            || isset($decoded->_imprint)
            || property_exists($decoded, self::FIELD_IMPRINT)
            || property_exists($decoded, self::FIELD_IMPRINT_EXT)) {
            $vals = (array)($decoded->imprint ?? []);
            $exts = (array)($decoded->_imprint ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addImprint(FHIRString::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->image) || property_exists($decoded, self::FIELD_IMAGE)) {
            if (is_object($decoded->image)) {
                $vals = [$decoded->image];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_IMAGE, true);
            } else {
                $vals = $decoded->image;
            }
            foreach($vals as $v) {
                $type->addImage(FHIRAttachment::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->scoring) || property_exists($decoded, self::FIELD_SCORING)) {
            if (is_array($decoded->scoring)) {
                $type->setScoring(FHIRCodeableConcept::jsonUnserialize(reset($decoded->scoring), $config));
            } else {
                $type->setScoring(FHIRCodeableConcept::jsonUnserialize($decoded->scoring, $config));
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
        if (isset($this->height)) {
            $out->height = $this->height;
        }
        if (isset($this->width)) {
            $out->width = $this->width;
        }
        if (isset($this->depth)) {
            $out->depth = $this->depth;
        }
        if (isset($this->weight)) {
            $out->weight = $this->weight;
        }
        if (isset($this->nominalVolume)) {
            $out->nominalVolume = $this->nominalVolume;
        }
        if (isset($this->externalDiameter)) {
            $out->externalDiameter = $this->externalDiameter;
        }
        if (isset($this->shape)) {
            if (null !== ($val = $this->shape->getValue())) {
                $out->shape = $val;
            }
            if ($this->shape->_nonValueFieldDefined()) {
                $ext = $this->shape->jsonSerialize();
                unset($ext->value);
                $out->_shape = $ext;
            }
        }
        if (isset($this->color) && [] !== $this->color) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->color as $v) {
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
                $out->color = $vals;
            }
            if ($hasExts) {
                $out->_color = $exts;
            }
        }
        if (isset($this->imprint) && [] !== $this->imprint) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->imprint as $v) {
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
                $out->imprint = $vals;
            }
            if ($hasExts) {
                $out->_imprint = $exts;
            }
        }
        if (isset($this->image) && [] !== $this->image) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_IMAGE) && 1 === count($this->image)) {
                $out->image = $this->image[0];
            } else {
                $out->image = $this->image;
            }
        }
        if (isset($this->scoring)) {
            $out->scoring = $this->scoring;
        }
        return $out;
    }
}
