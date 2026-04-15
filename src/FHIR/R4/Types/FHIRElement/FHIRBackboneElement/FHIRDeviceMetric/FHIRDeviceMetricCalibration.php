<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceMetric;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRDeviceMetricCalibrationStateList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRDeviceMetricCalibrationTypeList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDeviceMetricCalibrationState;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDeviceMetricCalibrationType;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInstant;
use OpenEMR\FHIR\Versions\R4\Types\FHIRInstantPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * Describes a measurement, calculation or setting capability of a medical device.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRDeviceMetricCalibration extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_DEVICE_METRIC_DOT_CALIBRATION;

    /* class_default.php:56 */
    public const FIELD_TYPE = 'type';
    public const FIELD_TYPE_EXT = '_type';
    public const FIELD_STATE = 'state';
    public const FIELD_STATE_EXT = '_state';
    public const FIELD_TIME = 'time';
    public const FIELD_TIME_EXT = '_time';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_TYPE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_STATE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_TIME => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * Describes the type of a metric calibration.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Describes the type of the calibration method.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDeviceMetricCalibrationType
     */
    #[FHIRDeviceMetricCalibrationType]
    protected FHIRDeviceMetricCalibrationType $type;
    /**
     * Describes the state of a metric calibration.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Describes the state of the calibration.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDeviceMetricCalibrationState
     */
    #[FHIRDeviceMetricCalibrationState]
    protected FHIRDeviceMetricCalibrationState $state;
    /**
     * An instant in time - known at least to the second
     * Note: This is intended for where precisely observed times are required,
     * typically system logs etc., and not human-reported times - for them, see date
     * and dateTime (which can be as precise as instant, but is not required to be)
     * below. Time zone is always required
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Describes the time last calibration has been performed.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInstant
     */
    #[FHIRInstant]
    protected FHIRInstant $time;

    /* constructor.php:61 */
    /**
     * FHIRDeviceMetricCalibration Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRDeviceMetricCalibrationTypeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDeviceMetricCalibrationType $type
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRDeviceMetricCalibrationStateList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDeviceMetricCalibrationState $state
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRInstantPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInstant $time
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|string|FHIRDeviceMetricCalibrationTypeList|FHIRDeviceMetricCalibrationType $type = null,
                                null|string|FHIRDeviceMetricCalibrationStateList|FHIRDeviceMetricCalibrationState $state = null,
                                null|string|\DateTimeInterface|FHIRInstantPrimitive|FHIRInstant $time = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $type) {
            $this->setType($type);
        }
        if (null !== $state) {
            $this->setState($state);
        }
        if (null !== $time) {
            $this->setTime($time);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * Describes the type of a metric calibration.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Describes the type of the calibration method.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDeviceMetricCalibrationType
     */
    public function getType(): null|FHIRDeviceMetricCalibrationType
    {
        return $this->type ?? null;
    }

    /**
     * Describes the type of a metric calibration.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Describes the type of the calibration method.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRDeviceMetricCalibrationTypeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDeviceMetricCalibrationType $type
     * @return static
     */
    public function setType(null|string|FHIRDeviceMetricCalibrationTypeList|FHIRDeviceMetricCalibrationType $type): self
    {
        if (null === $type) {
            unset($this->type);
            return $this;
        }
        if (!($type instanceof FHIRDeviceMetricCalibrationType)) {
            $type = new FHIRDeviceMetricCalibrationType(value: $type);
        }
        $this->type = $type;
        return $this;
    }

    /**
     * Describes the state of a metric calibration.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Describes the state of the calibration.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDeviceMetricCalibrationState
     */
    public function getState(): null|FHIRDeviceMetricCalibrationState
    {
        return $this->state ?? null;
    }

    /**
     * Describes the state of a metric calibration.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Describes the state of the calibration.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRDeviceMetricCalibrationStateList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDeviceMetricCalibrationState $state
     * @return static
     */
    public function setState(null|string|FHIRDeviceMetricCalibrationStateList|FHIRDeviceMetricCalibrationState $state): self
    {
        if (null === $state) {
            unset($this->state);
            return $this;
        }
        if (!($state instanceof FHIRDeviceMetricCalibrationState)) {
            $state = new FHIRDeviceMetricCalibrationState(value: $state);
        }
        $this->state = $state;
        return $this;
    }

    /**
     * An instant in time - known at least to the second
     * Note: This is intended for where precisely observed times are required,
     * typically system logs etc., and not human-reported times - for them, see date
     * and dateTime (which can be as precise as instant, but is not required to be)
     * below. Time zone is always required
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Describes the time last calibration has been performed.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInstant
     */
    public function getTime(): null|FHIRInstant
    {
        return $this->time ?? null;
    }

    /**
     * An instant in time - known at least to the second
     * Note: This is intended for where precisely observed times are required,
     * typically system logs etc., and not human-reported times - for them, see date
     * and dateTime (which can be as precise as instant, but is not required to be)
     * below. Time zone is always required
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Describes the time last calibration has been performed.
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRInstantPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInstant $time
     * @return static
     */
    public function setTime(null|string|\DateTimeInterface|FHIRInstantPrimitive|FHIRInstant $time): self
    {
        if (null === $time) {
            unset($this->time);
            return $this;
        }
        if (!($time instanceof FHIRInstant)) {
            $time = new FHIRInstant(value: $time);
        }
        $this->time = $time;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceMetric\FHIRDeviceMetricCalibration $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceMetric\FHIRDeviceMetricCalibration
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRDeviceMetricCalibration)) {
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
                $type->setType(FHIRDeviceMetricCalibrationType::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STATE === $cen) {
                $type->setState(FHIRDeviceMetricCalibrationState::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TIME === $cen) {
                $type->setTime(FHIRInstant::xmlUnserialize($ce, $config));
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
        if (isset($attributes[self::FIELD_STATE])) {
            if (isset($type->state)) {
                $type->state->setValue((string)$attributes[self::FIELD_STATE]);
            } else {
                $type->setState((string)$attributes[self::FIELD_STATE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_STATE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_TIME])) {
            if (isset($type->time)) {
                $type->time->setValue((string)$attributes[self::FIELD_TIME]);
            } else {
                $type->setTime((string)$attributes[self::FIELD_TIME]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_TIME, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->state) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_STATE]) {
            $xw->writeAttribute(self::FIELD_STATE, $this->state->_getValueAsString());
        }
        if (isset($this->time) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_TIME]) {
            $xw->writeAttribute(self::FIELD_TIME, $this->time->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->type)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_TYPE]
                || $this->type->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_TYPE);
            $this->type->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_TYPE]);
            $xw->endElement();
        }
        if (isset($this->state)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_STATE]
                || $this->state->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_STATE);
            $this->state->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_STATE]);
            $xw->endElement();
        }
        if (isset($this->time)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_TIME]
                || $this->time->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_TIME);
            $this->time->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_TIME]);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceMetric\FHIRDeviceMetricCalibration $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceMetric\FHIRDeviceMetricCalibration
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
        } else if (!($type instanceof FHIRDeviceMetricCalibration)) {
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
            $type->setType(FHIRDeviceMetricCalibrationType::jsonUnserialize($v, $config));
        }
        if (isset($decoded->state)
            || isset($decoded->_state)
            || property_exists($decoded, self::FIELD_STATE)
            || property_exists($decoded, self::FIELD_STATE_EXT)) {
            $v = $decoded->_state ?? new \stdClass();
            $v->value = $decoded->state ?? null;
            $type->setState(FHIRDeviceMetricCalibrationState::jsonUnserialize($v, $config));
        }
        if (isset($decoded->time)
            || isset($decoded->_time)
            || property_exists($decoded, self::FIELD_TIME)
            || property_exists($decoded, self::FIELD_TIME_EXT)) {
            $v = $decoded->_time ?? new \stdClass();
            $v->value = $decoded->time ?? null;
            $type->setTime(FHIRInstant::jsonUnserialize($v, $config));
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
        if (isset($this->state)) {
            if (null !== ($val = $this->state->getValue())) {
                $out->state = $val;
            }
            if ($this->state->_nonValueFieldDefined()) {
                $ext = $this->state->jsonSerialize();
                unset($ext->value);
                $out->_state = $ext;
            }
        }
        if (isset($this->time)) {
            if (null !== ($val = $this->time->getValue())) {
                $out->time = $val;
            }
            if ($this->time->_nonValueFieldDefined()) {
                $ext = $this->time->jsonSerialize();
                unset($ext->value);
                $out->_time = $ext;
            }
        }
        return $out;
    }
}
