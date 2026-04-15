<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRLocation;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * Details and position information for a physical place where services are
 * provided and resources and participants may be stored, found, contained, or
 * accommodated.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRLocationPosition extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_LOCATION_DOT_POSITION;

    /* class_default.php:56 */
    public const FIELD_LONGITUDE = 'longitude';
    public const FIELD_LONGITUDE_EXT = '_longitude';
    public const FIELD_LATITUDE = 'latitude';
    public const FIELD_LATITUDE_EXT = '_latitude';
    public const FIELD_ALTITUDE = 'altitude';
    public const FIELD_ALTITUDE_EXT = '_altitude';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_LONGITUDE => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_LATITUDE => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_LONGITUDE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_LATITUDE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_ALTITUDE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Longitude. The value domain and the interpretation are the same as for the text
     * of the longitude element in KML (see notes below).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    #[FHIRDecimal]
    protected FHIRDecimal $longitude;
    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Latitude. The value domain and the interpretation are the same as for the text
     * of the latitude element in KML (see notes below).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    #[FHIRDecimal]
    protected FHIRDecimal $latitude;
    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Altitude. The value domain and the interpretation are the same as for the text
     * of the altitude element in KML (see notes below).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    #[FHIRDecimal]
    protected FHIRDecimal $altitude;

    /* constructor.php:61 */
    /**
     * FHIRLocationPosition Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $longitude
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $latitude
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $altitude
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $longitude = null,
                                null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $latitude = null,
                                null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $altitude = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $longitude) {
            $this->setLongitude($longitude);
        }
        if (null !== $latitude) {
            $this->setLatitude($latitude);
        }
        if (null !== $altitude) {
            $this->setAltitude($altitude);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Longitude. The value domain and the interpretation are the same as for the text
     * of the longitude element in KML (see notes below).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    public function getLongitude(): null|FHIRDecimal
    {
        return $this->longitude ?? null;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Longitude. The value domain and the interpretation are the same as for the text
     * of the longitude element in KML (see notes below).
     *
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $longitude
     * @return static
     */
    public function setLongitude(null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $longitude): self
    {
        if (null === $longitude) {
            unset($this->longitude);
            return $this;
        }
        if (!($longitude instanceof FHIRDecimal)) {
            $longitude = new FHIRDecimal(value: $longitude);
        }
        $this->longitude = $longitude;
        return $this;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Latitude. The value domain and the interpretation are the same as for the text
     * of the latitude element in KML (see notes below).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    public function getLatitude(): null|FHIRDecimal
    {
        return $this->latitude ?? null;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Latitude. The value domain and the interpretation are the same as for the text
     * of the latitude element in KML (see notes below).
     *
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $latitude
     * @return static
     */
    public function setLatitude(null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $latitude): self
    {
        if (null === $latitude) {
            unset($this->latitude);
            return $this;
        }
        if (!($latitude instanceof FHIRDecimal)) {
            $latitude = new FHIRDecimal(value: $latitude);
        }
        $this->latitude = $latitude;
        return $this;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Altitude. The value domain and the interpretation are the same as for the text
     * of the altitude element in KML (see notes below).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    public function getAltitude(): null|FHIRDecimal
    {
        return $this->altitude ?? null;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Altitude. The value domain and the interpretation are the same as for the text
     * of the altitude element in KML (see notes below).
     *
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $altitude
     * @return static
     */
    public function setAltitude(null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $altitude): self
    {
        if (null === $altitude) {
            unset($this->altitude);
            return $this;
        }
        if (!($altitude instanceof FHIRDecimal)) {
            $altitude = new FHIRDecimal(value: $altitude);
        }
        $this->altitude = $altitude;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRLocation\FHIRLocationPosition $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRLocation\FHIRLocationPosition
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRLocationPosition)) {
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
            } else if (self::FIELD_LONGITUDE === $cen) {
                $type->setLongitude(FHIRDecimal::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LATITUDE === $cen) {
                $type->setLatitude(FHIRDecimal::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ALTITUDE === $cen) {
                $type->setAltitude(FHIRDecimal::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_LONGITUDE])) {
            if (isset($type->longitude)) {
                $type->longitude->setValue((string)$attributes[self::FIELD_LONGITUDE]);
            } else {
                $type->setLongitude((string)$attributes[self::FIELD_LONGITUDE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_LONGITUDE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_LATITUDE])) {
            if (isset($type->latitude)) {
                $type->latitude->setValue((string)$attributes[self::FIELD_LATITUDE]);
            } else {
                $type->setLatitude((string)$attributes[self::FIELD_LATITUDE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_LATITUDE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_ALTITUDE])) {
            if (isset($type->altitude)) {
                $type->altitude->setValue((string)$attributes[self::FIELD_ALTITUDE]);
            } else {
                $type->setAltitude((string)$attributes[self::FIELD_ALTITUDE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_ALTITUDE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->longitude) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_LONGITUDE]) {
            $xw->writeAttribute(self::FIELD_LONGITUDE, $this->longitude->_getValueAsString());
        }
        if (isset($this->latitude) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_LATITUDE]) {
            $xw->writeAttribute(self::FIELD_LATITUDE, $this->latitude->_getValueAsString());
        }
        if (isset($this->altitude) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_ALTITUDE]) {
            $xw->writeAttribute(self::FIELD_ALTITUDE, $this->altitude->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->longitude)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_LONGITUDE]
                || $this->longitude->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_LONGITUDE);
            $this->longitude->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_LONGITUDE]);
            $xw->endElement();
        }
        if (isset($this->latitude)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_LATITUDE]
                || $this->latitude->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_LATITUDE);
            $this->latitude->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_LATITUDE]);
            $xw->endElement();
        }
        if (isset($this->altitude)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_ALTITUDE]
                || $this->altitude->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_ALTITUDE);
            $this->altitude->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_ALTITUDE]);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRLocation\FHIRLocationPosition $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRLocation\FHIRLocationPosition
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
        } else if (!($type instanceof FHIRLocationPosition)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->longitude)
            || isset($decoded->_longitude)
            || property_exists($decoded, self::FIELD_LONGITUDE)
            || property_exists($decoded, self::FIELD_LONGITUDE_EXT)) {
            $v = $decoded->_longitude ?? new \stdClass();
            $v->value = $decoded->longitude ?? null;
            $type->setLongitude(FHIRDecimal::jsonUnserialize($v, $config));
        }
        if (isset($decoded->latitude)
            || isset($decoded->_latitude)
            || property_exists($decoded, self::FIELD_LATITUDE)
            || property_exists($decoded, self::FIELD_LATITUDE_EXT)) {
            $v = $decoded->_latitude ?? new \stdClass();
            $v->value = $decoded->latitude ?? null;
            $type->setLatitude(FHIRDecimal::jsonUnserialize($v, $config));
        }
        if (isset($decoded->altitude)
            || isset($decoded->_altitude)
            || property_exists($decoded, self::FIELD_ALTITUDE)
            || property_exists($decoded, self::FIELD_ALTITUDE_EXT)) {
            $v = $decoded->_altitude ?? new \stdClass();
            $v->value = $decoded->altitude ?? null;
            $type->setAltitude(FHIRDecimal::jsonUnserialize($v, $config));
        }
        return $type;
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize(): mixed
    {
        $out = parent::jsonSerialize();
        if (isset($this->longitude)) {
            if (null !== ($val = $this->longitude->getValue())) {
                $out->longitude = $val;
            }
            if ($this->longitude->_nonValueFieldDefined()) {
                $ext = $this->longitude->jsonSerialize();
                unset($ext->value);
                $out->_longitude = $ext;
            }
        }
        if (isset($this->latitude)) {
            if (null !== ($val = $this->latitude->getValue())) {
                $out->latitude = $val;
            }
            if ($this->latitude->_nonValueFieldDefined()) {
                $ext = $this->latitude->jsonSerialize();
                unset($ext->value);
                $out->_latitude = $ext;
            }
        }
        if (isset($this->altitude)) {
            if (null !== ($val = $this->altitude->getValue())) {
                $out->altitude = $val;
            }
            if ($this->altitude->_nonValueFieldDefined()) {
                $ext = $this->altitude->jsonSerialize();
                unset($ext->value);
                $out->_altitude = $ext;
            }
        }
        return $out;
    }
}
