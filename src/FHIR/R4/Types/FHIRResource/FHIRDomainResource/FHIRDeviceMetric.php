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
use OpenEMR\FHIR\Validation\Rules\MinOccursRule;
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRDeviceMetricCategoryList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRDeviceMetricColorList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRDeviceMetricOperationalStatusList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceMetric\FHIRDeviceMetricCalibration;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDeviceMetricCategory;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDeviceMetricColor;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDeviceMetricOperationalStatus;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive;
use OpenEMR\FHIR\Versions\R4\Version;
use OpenEMR\FHIR\Versions\R4\VersionConstants;
use OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface;
use OpenEMR\FHIR\Versions\R4\VersionTypeMap;

/**
 * Describes a measurement, calculation or setting capability of a medical device.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRDeviceMetric extends FHIRDomainResource implements VersionContainedTypeInterface
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_DEVICE_METRIC;

    /* class_default.php:56 */
    public const FIELD_IDENTIFIER = 'identifier';
    public const FIELD_TYPE = 'type';
    public const FIELD_UNIT = 'unit';
    public const FIELD_SOURCE = 'source';
    public const FIELD_PARENT = 'parent';
    public const FIELD_OPERATIONAL_STATUS = 'operationalStatus';
    public const FIELD_OPERATIONAL_STATUS_EXT = '_operationalStatus';
    public const FIELD_COLOR = 'color';
    public const FIELD_COLOR_EXT = '_color';
    public const FIELD_CATEGORY = 'category';
    public const FIELD_CATEGORY_EXT = '_category';
    public const FIELD_MEASUREMENT_PERIOD = 'measurementPeriod';
    public const FIELD_CALIBRATION = 'calibration';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_TYPE => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_CATEGORY => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_OPERATIONAL_STATUS => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_COLOR => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_CATEGORY => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Unique instance identifiers assigned to a device by the device or gateway
     * software, manufacturers, other organizations or owners. For example: handle ID.
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
     * Describes the type of the metric. For example: Heart Rate, PEEP Setting, etc.
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
     * Describes the unit that an observed value determined for this metric will have.
     * For example: Percent, Seconds, etc.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $unit;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Describes the link to the Device that this DeviceMetric belongs to and that
     * contains administrative device information such as manufacturer, serial number,
     * etc.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $source;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Describes the link to the Device that this DeviceMetric belongs to and that
     * provide information about the location of this DeviceMetric in the containment
     * structure of the parent Device. An example would be a Device that represents a
     * Channel. This reference can be used by a client application to distinguish
     * DeviceMetrics that have the same type, but should be interpreted based on their
     * containment location.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $parent;
    /**
     * Describes the operational status of the DeviceMetric.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates current operational state of the device. For example: On, Off,
     * Standby, etc.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDeviceMetricOperationalStatus
     */
    #[FHIRDeviceMetricOperationalStatus]
    protected FHIRDeviceMetricOperationalStatus $operationalStatus;
    /**
     * Describes the typical color of representation.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Describes the color representation for the metric. This is often used to aid
     * clinicians to track and identify parameter types by color. In practice, consider
     * a Patient Monitor that has ECG/HR and Pleth for example; the parameters are
     * displayed in different characteristic colors, such as HR-blue, BP-green, and PR
     * and SpO2- magenta.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDeviceMetricColor
     */
    #[FHIRDeviceMetricColor]
    protected FHIRDeviceMetricColor $color;
    /**
     * Describes the category of the metric.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates the category of the observation generation process. A DeviceMetric can
     * be for example a setting, measurement, or calculation.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDeviceMetricCategory
     */
    #[FHIRDeviceMetricCategory]
    protected FHIRDeviceMetricCategory $category;
    /**
     * Specifies an event that may occur multiple times. Timing schedules are used to
     * record when things are planned, expected or requested to occur. The most common
     * usage is in dosage instructions for medications. They are also used when
     * planning care of various kinds, and may be used for reporting the schedule to
     * which past regular activities were carried out.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Describes the measurement repetition time. This is not necessarily the same as
     * the update period. The measurement repetition time can range from milliseconds
     * up to hours. An example for a measurement repetition time in the range of
     * milliseconds is the sampling rate of an ECG. An example for a measurement
     * repetition time in the range of hours is a NIBP that is triggered automatically
     * every hour. The update period may be different than the measurement repetition
     * time, if the device does not update the published observed value with the same
     * frequency as it was measured.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming
     */
    #[FHIRTiming]
    protected FHIRTiming $measurementPeriod;
    /**
     * Describes a measurement, calculation or setting capability of a medical device.
     *
     * Describes the calibrations that have been performed or that are required to be
     * performed.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceMetric\FHIRDeviceMetricCalibration>
     */
    #[FHIRDeviceMetricCalibration]
    protected array $calibration;

    /* constructor.php:61 */
    /**
     * FHIRDeviceMetric Constructor
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $id
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $meta
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $implicitRules
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative $text
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer>|iterable<\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface> $contained
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier> $identifier
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $type
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $unit
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $source
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $parent
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRDeviceMetricOperationalStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDeviceMetricOperationalStatus $operationalStatus
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRDeviceMetricColorList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDeviceMetricColor $color
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRDeviceMetricCategoryList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDeviceMetricCategory $category
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming $measurementPeriod
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceMetric\FHIRDeviceMetricCalibration> $calibration
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
                                null|FHIRCodeableConcept $type = null,
                                null|FHIRCodeableConcept $unit = null,
                                null|FHIRReference $source = null,
                                null|FHIRReference $parent = null,
                                null|string|FHIRDeviceMetricOperationalStatusList|FHIRDeviceMetricOperationalStatus $operationalStatus = null,
                                null|string|FHIRDeviceMetricColorList|FHIRDeviceMetricColor $color = null,
                                null|string|FHIRDeviceMetricCategoryList|FHIRDeviceMetricCategory $category = null,
                                null|FHIRTiming $measurementPeriod = null,
                                null|iterable $calibration = null,
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
        if (null !== $type) {
            $this->setType($type);
        }
        if (null !== $unit) {
            $this->setUnit($unit);
        }
        if (null !== $source) {
            $this->setSource($source);
        }
        if (null !== $parent) {
            $this->setParent($parent);
        }
        if (null !== $operationalStatus) {
            $this->setOperationalStatus($operationalStatus);
        }
        if (null !== $color) {
            $this->setColor($color);
        }
        if (null !== $category) {
            $this->setCategory($category);
        }
        if (null !== $measurementPeriod) {
            $this->setMeasurementPeriod($measurementPeriod);
        }
        if (null !== $calibration) {
            $this->setCalibration(...$calibration);
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
     * Unique instance identifiers assigned to a device by the device or gateway
     * software, manufacturers, other organizations or owners. For example: handle ID.
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
     * Unique instance identifiers assigned to a device by the device or gateway
     * software, manufacturers, other organizations or owners. For example: handle ID.
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
     * Unique instance identifiers assigned to a device by the device or gateway
     * software, manufacturers, other organizations or owners. For example: handle ID.
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
     * Describes the type of the metric. For example: Heart Rate, PEEP Setting, etc.
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
     * Describes the type of the metric. For example: Heart Rate, PEEP Setting, etc.
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
     * Describes the unit that an observed value determined for this metric will have.
     * For example: Percent, Seconds, etc.
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
     * Describes the unit that an observed value determined for this metric will have.
     * For example: Percent, Seconds, etc.
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
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Describes the link to the Device that this DeviceMetric belongs to and that
     * contains administrative device information such as manufacturer, serial number,
     * etc.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getSource(): null|FHIRReference
    {
        return $this->source ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Describes the link to the Device that this DeviceMetric belongs to and that
     * contains administrative device information such as manufacturer, serial number,
     * etc.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $source
     * @return static
     */
    public function setSource(null|FHIRReference $source): self
    {
        if (null === $source) {
            unset($this->source);
            return $this;
        }
        $this->source = $source;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Describes the link to the Device that this DeviceMetric belongs to and that
     * provide information about the location of this DeviceMetric in the containment
     * structure of the parent Device. An example would be a Device that represents a
     * Channel. This reference can be used by a client application to distinguish
     * DeviceMetrics that have the same type, but should be interpreted based on their
     * containment location.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getParent(): null|FHIRReference
    {
        return $this->parent ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Describes the link to the Device that this DeviceMetric belongs to and that
     * provide information about the location of this DeviceMetric in the containment
     * structure of the parent Device. An example would be a Device that represents a
     * Channel. This reference can be used by a client application to distinguish
     * DeviceMetrics that have the same type, but should be interpreted based on their
     * containment location.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $parent
     * @return static
     */
    public function setParent(null|FHIRReference $parent): self
    {
        if (null === $parent) {
            unset($this->parent);
            return $this;
        }
        $this->parent = $parent;
        return $this;
    }

    /**
     * Describes the operational status of the DeviceMetric.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates current operational state of the device. For example: On, Off,
     * Standby, etc.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDeviceMetricOperationalStatus
     */
    public function getOperationalStatus(): null|FHIRDeviceMetricOperationalStatus
    {
        return $this->operationalStatus ?? null;
    }

    /**
     * Describes the operational status of the DeviceMetric.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates current operational state of the device. For example: On, Off,
     * Standby, etc.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRDeviceMetricOperationalStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDeviceMetricOperationalStatus $operationalStatus
     * @return static
     */
    public function setOperationalStatus(null|string|FHIRDeviceMetricOperationalStatusList|FHIRDeviceMetricOperationalStatus $operationalStatus): self
    {
        if (null === $operationalStatus) {
            unset($this->operationalStatus);
            return $this;
        }
        if (!($operationalStatus instanceof FHIRDeviceMetricOperationalStatus)) {
            $operationalStatus = new FHIRDeviceMetricOperationalStatus(value: $operationalStatus);
        }
        $this->operationalStatus = $operationalStatus;
        return $this;
    }

    /**
     * Describes the typical color of representation.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Describes the color representation for the metric. This is often used to aid
     * clinicians to track and identify parameter types by color. In practice, consider
     * a Patient Monitor that has ECG/HR and Pleth for example; the parameters are
     * displayed in different characteristic colors, such as HR-blue, BP-green, and PR
     * and SpO2- magenta.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDeviceMetricColor
     */
    public function getColor(): null|FHIRDeviceMetricColor
    {
        return $this->color ?? null;
    }

    /**
     * Describes the typical color of representation.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Describes the color representation for the metric. This is often used to aid
     * clinicians to track and identify parameter types by color. In practice, consider
     * a Patient Monitor that has ECG/HR and Pleth for example; the parameters are
     * displayed in different characteristic colors, such as HR-blue, BP-green, and PR
     * and SpO2- magenta.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRDeviceMetricColorList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDeviceMetricColor $color
     * @return static
     */
    public function setColor(null|string|FHIRDeviceMetricColorList|FHIRDeviceMetricColor $color): self
    {
        if (null === $color) {
            unset($this->color);
            return $this;
        }
        if (!($color instanceof FHIRDeviceMetricColor)) {
            $color = new FHIRDeviceMetricColor(value: $color);
        }
        $this->color = $color;
        return $this;
    }

    /**
     * Describes the category of the metric.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates the category of the observation generation process. A DeviceMetric can
     * be for example a setting, measurement, or calculation.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDeviceMetricCategory
     */
    public function getCategory(): null|FHIRDeviceMetricCategory
    {
        return $this->category ?? null;
    }

    /**
     * Describes the category of the metric.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates the category of the observation generation process. A DeviceMetric can
     * be for example a setting, measurement, or calculation.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRDeviceMetricCategoryList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDeviceMetricCategory $category
     * @return static
     */
    public function setCategory(null|string|FHIRDeviceMetricCategoryList|FHIRDeviceMetricCategory $category): self
    {
        if (null === $category) {
            unset($this->category);
            return $this;
        }
        if (!($category instanceof FHIRDeviceMetricCategory)) {
            $category = new FHIRDeviceMetricCategory(value: $category);
        }
        $this->category = $category;
        return $this;
    }

    /**
     * Specifies an event that may occur multiple times. Timing schedules are used to
     * record when things are planned, expected or requested to occur. The most common
     * usage is in dosage instructions for medications. They are also used when
     * planning care of various kinds, and may be used for reporting the schedule to
     * which past regular activities were carried out.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Describes the measurement repetition time. This is not necessarily the same as
     * the update period. The measurement repetition time can range from milliseconds
     * up to hours. An example for a measurement repetition time in the range of
     * milliseconds is the sampling rate of an ECG. An example for a measurement
     * repetition time in the range of hours is a NIBP that is triggered automatically
     * every hour. The update period may be different than the measurement repetition
     * time, if the device does not update the published observed value with the same
     * frequency as it was measured.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming
     */
    public function getMeasurementPeriod(): null|FHIRTiming
    {
        return $this->measurementPeriod ?? null;
    }

    /**
     * Specifies an event that may occur multiple times. Timing schedules are used to
     * record when things are planned, expected or requested to occur. The most common
     * usage is in dosage instructions for medications. They are also used when
     * planning care of various kinds, and may be used for reporting the schedule to
     * which past regular activities were carried out.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Describes the measurement repetition time. This is not necessarily the same as
     * the update period. The measurement repetition time can range from milliseconds
     * up to hours. An example for a measurement repetition time in the range of
     * milliseconds is the sampling rate of an ECG. An example for a measurement
     * repetition time in the range of hours is a NIBP that is triggered automatically
     * every hour. The update period may be different than the measurement repetition
     * time, if the device does not update the published observed value with the same
     * frequency as it was measured.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming $measurementPeriod
     * @return static
     */
    public function setMeasurementPeriod(null|FHIRTiming $measurementPeriod): self
    {
        if (null === $measurementPeriod) {
            unset($this->measurementPeriod);
            return $this;
        }
        $this->measurementPeriod = $measurementPeriod;
        return $this;
    }

    /**
     * Describes a measurement, calculation or setting capability of a medical device.
     *
     * Describes the calibrations that have been performed or that are required to be
     * performed.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceMetric\FHIRDeviceMetricCalibration>
     */
    public function getCalibration(): array
    {
        return $this->calibration ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceMetric\FHIRDeviceMetricCalibration>
     */
    public function getCalibrationIterator(): iterable
    {
        if (!isset($this->calibration)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->calibration);
    }

    /**
     * Describes a measurement, calculation or setting capability of a medical device.
     *
     * Describes the calibrations that have been performed or that are required to be
     * performed.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceMetric\FHIRDeviceMetricCalibration $calibration
     * @return static
     */
    public function addCalibration(FHIRDeviceMetricCalibration $calibration): self
    {
        if (!isset($this->calibration)) {
            $this->calibration = [];
        }
        $this->calibration[] = $calibration;
        return $this;
    }

    /**
     * Describes a measurement, calculation or setting capability of a medical device.
     *
     * Describes the calibrations that have been performed or that are required to be
     * performed.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceMetric\FHIRDeviceMetricCalibration ...$calibration
     * @return static
     */
    public function setCalibration(FHIRDeviceMetricCalibration ...$calibration): self
    {
        if ([] === $calibration) {
            unset($this->calibration);
            return $this;
        }
        $this->calibration = $calibration;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRDeviceMetric $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRDeviceMetric
     * @throws \Exception
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRDeviceMetric)) {
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
            } else if (self::FIELD_TYPE === $cen) {
                $type->setType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_UNIT === $cen) {
                $type->setUnit(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SOURCE === $cen) {
                $type->setSource(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PARENT === $cen) {
                $type->setParent(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_OPERATIONAL_STATUS === $cen) {
                $type->setOperationalStatus(FHIRDeviceMetricOperationalStatus::xmlUnserialize($ce, $config));
            } else if (self::FIELD_COLOR === $cen) {
                $type->setColor(FHIRDeviceMetricColor::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CATEGORY === $cen) {
                $type->setCategory(FHIRDeviceMetricCategory::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MEASUREMENT_PERIOD === $cen) {
                $type->setMeasurementPeriod(FHIRTiming::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CALIBRATION === $cen) {
                $type->addCalibration(FHIRDeviceMetricCalibration::xmlUnserialize($ce, $config));
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
        if (isset($attributes[self::FIELD_OPERATIONAL_STATUS])) {
            if (isset($type->operationalStatus)) {
                $type->operationalStatus->setValue((string)$attributes[self::FIELD_OPERATIONAL_STATUS]);
            } else {
                $type->setOperationalStatus((string)$attributes[self::FIELD_OPERATIONAL_STATUS]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_OPERATIONAL_STATUS, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_COLOR])) {
            if (isset($type->color)) {
                $type->color->setValue((string)$attributes[self::FIELD_COLOR]);
            } else {
                $type->setColor((string)$attributes[self::FIELD_COLOR]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_COLOR, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_CATEGORY])) {
            if (isset($type->category)) {
                $type->category->setValue((string)$attributes[self::FIELD_CATEGORY]);
            } else {
                $type->setCategory((string)$attributes[self::FIELD_CATEGORY]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_CATEGORY, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
            $xw->openRootNode('DeviceMetric', $this->_getSourceXMLNS());
        }
        if (isset($this->operationalStatus) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_OPERATIONAL_STATUS]) {
            $xw->writeAttribute(self::FIELD_OPERATIONAL_STATUS, $this->operationalStatus->_getValueAsString());
        }
        if (isset($this->color) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_COLOR]) {
            $xw->writeAttribute(self::FIELD_COLOR, $this->color->_getValueAsString());
        }
        if (isset($this->category) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_CATEGORY]) {
            $xw->writeAttribute(self::FIELD_CATEGORY, $this->category->_getValueAsString());
        }
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
        if (isset($this->unit)) {
            $xw->startElement(self::FIELD_UNIT);
            $this->unit->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->source)) {
            $xw->startElement(self::FIELD_SOURCE);
            $this->source->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->parent)) {
            $xw->startElement(self::FIELD_PARENT);
            $this->parent->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->operationalStatus)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_OPERATIONAL_STATUS]
                || $this->operationalStatus->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_OPERATIONAL_STATUS);
            $this->operationalStatus->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_OPERATIONAL_STATUS]);
            $xw->endElement();
        }
        if (isset($this->color)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_COLOR]
                || $this->color->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_COLOR);
            $this->color->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_COLOR]);
            $xw->endElement();
        }
        if (isset($this->category)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_CATEGORY]
                || $this->category->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_CATEGORY);
            $this->category->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_CATEGORY]);
            $xw->endElement();
        }
        if (isset($this->measurementPeriod)) {
            $xw->startElement(self::FIELD_MEASUREMENT_PERIOD);
            $this->measurementPeriod->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->calibration)) {
            foreach ($this->calibration as $v) {
                $xw->startElement(self::FIELD_CALIBRATION);
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
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRDeviceMetric $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRDeviceMetric
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
        } else if (!($type instanceof FHIRDeviceMetric)) {
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
        if (isset($decoded->type) || property_exists($decoded, self::FIELD_TYPE)) {
            if (is_array($decoded->type)) {
                $type->setType(FHIRCodeableConcept::jsonUnserialize(reset($decoded->type), $config));
            } else {
                $type->setType(FHIRCodeableConcept::jsonUnserialize($decoded->type, $config));
            }
        }
        if (isset($decoded->unit) || property_exists($decoded, self::FIELD_UNIT)) {
            if (is_array($decoded->unit)) {
                $type->setUnit(FHIRCodeableConcept::jsonUnserialize(reset($decoded->unit), $config));
            } else {
                $type->setUnit(FHIRCodeableConcept::jsonUnserialize($decoded->unit, $config));
            }
        }
        if (isset($decoded->source) || property_exists($decoded, self::FIELD_SOURCE)) {
            if (is_array($decoded->source)) {
                $type->setSource(FHIRReference::jsonUnserialize(reset($decoded->source), $config));
            } else {
                $type->setSource(FHIRReference::jsonUnserialize($decoded->source, $config));
            }
        }
        if (isset($decoded->parent) || property_exists($decoded, self::FIELD_PARENT)) {
            if (is_array($decoded->parent)) {
                $type->setParent(FHIRReference::jsonUnserialize(reset($decoded->parent), $config));
            } else {
                $type->setParent(FHIRReference::jsonUnserialize($decoded->parent, $config));
            }
        }
        if (isset($decoded->operationalStatus)
            || isset($decoded->_operationalStatus)
            || property_exists($decoded, self::FIELD_OPERATIONAL_STATUS)
            || property_exists($decoded, self::FIELD_OPERATIONAL_STATUS_EXT)) {
            $v = $decoded->_operationalStatus ?? new \stdClass();
            $v->value = $decoded->operationalStatus ?? null;
            $type->setOperationalStatus(FHIRDeviceMetricOperationalStatus::jsonUnserialize($v, $config));
        }
        if (isset($decoded->color)
            || isset($decoded->_color)
            || property_exists($decoded, self::FIELD_COLOR)
            || property_exists($decoded, self::FIELD_COLOR_EXT)) {
            $v = $decoded->_color ?? new \stdClass();
            $v->value = $decoded->color ?? null;
            $type->setColor(FHIRDeviceMetricColor::jsonUnserialize($v, $config));
        }
        if (isset($decoded->category)
            || isset($decoded->_category)
            || property_exists($decoded, self::FIELD_CATEGORY)
            || property_exists($decoded, self::FIELD_CATEGORY_EXT)) {
            $v = $decoded->_category ?? new \stdClass();
            $v->value = $decoded->category ?? null;
            $type->setCategory(FHIRDeviceMetricCategory::jsonUnserialize($v, $config));
        }
        if (isset($decoded->measurementPeriod) || property_exists($decoded, self::FIELD_MEASUREMENT_PERIOD)) {
            if (is_array($decoded->measurementPeriod)) {
                $type->setMeasurementPeriod(FHIRTiming::jsonUnserialize(reset($decoded->measurementPeriod), $config));
            } else {
                $type->setMeasurementPeriod(FHIRTiming::jsonUnserialize($decoded->measurementPeriod, $config));
            }
        }
        if (isset($decoded->calibration) || property_exists($decoded, self::FIELD_CALIBRATION)) {
            if (is_object($decoded->calibration)) {
                $vals = [$decoded->calibration];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_CALIBRATION, true);
            } else {
                $vals = $decoded->calibration;
            }
            foreach($vals as $v) {
                $type->addCalibration(FHIRDeviceMetricCalibration::jsonUnserialize($v, $config));
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
        if (isset($this->unit)) {
            $out->unit = $this->unit;
        }
        if (isset($this->source)) {
            $out->source = $this->source;
        }
        if (isset($this->parent)) {
            $out->parent = $this->parent;
        }
        if (isset($this->operationalStatus)) {
            if (null !== ($val = $this->operationalStatus->getValue())) {
                $out->operationalStatus = $val;
            }
            if ($this->operationalStatus->_nonValueFieldDefined()) {
                $ext = $this->operationalStatus->jsonSerialize();
                unset($ext->value);
                $out->_operationalStatus = $ext;
            }
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
        if (isset($this->category)) {
            if (null !== ($val = $this->category->getValue())) {
                $out->category = $val;
            }
            if ($this->category->_nonValueFieldDefined()) {
                $ext = $this->category->jsonSerialize();
                unset($ext->value);
                $out->_category = $ext;
            }
        }
        if (isset($this->measurementPeriod)) {
            $out->measurementPeriod = $this->measurementPeriod;
        }
        if (isset($this->calibration) && [] !== $this->calibration) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_CALIBRATION) && 1 === count($this->calibration)) {
                $out->calibration = $this->calibration[0];
            } else {
                $out->calibration = $this->calibration;
            }
        }
        $out->resourceType = $this->_getResourceType();
        return $out;
    }
}
