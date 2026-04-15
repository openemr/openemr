<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConsent;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * A record of a healthcare consumer’s choices, which permits or denies
 * identified recipient(s) or recipient role(s) to perform one or more actions
 * within a given policy context, for specific purposes and periods of time.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRConsentVerification extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_CONSENT_DOT_VERIFICATION;

    /* class_default.php:56 */
    public const FIELD_VERIFIED = 'verified';
    public const FIELD_VERIFIED_EXT = '_verified';
    public const FIELD_VERIFIED_WITH = 'verifiedWith';
    public const FIELD_VERIFICATION_DATE = 'verificationDate';
    public const FIELD_VERIFICATION_DATE_EXT = '_verificationDate';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_VERIFIED => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_VERIFIED => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_VERIFICATION_DATE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Has the instruction been verified.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    #[FHIRBoolean]
    protected FHIRBoolean $verified;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Who verified the instruction (Patient, Relative or other Authorized Person).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $verifiedWith;
    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Date verification was collected.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $verificationDate;

    /* constructor.php:61 */
    /**
     * FHIRConsentVerification Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $verified
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $verifiedWith
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $verificationDate
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $verified = null,
                                null|FHIRReference $verifiedWith = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $verificationDate = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $verified) {
            $this->setVerified($verified);
        }
        if (null !== $verifiedWith) {
            $this->setVerifiedWith($verifiedWith);
        }
        if (null !== $verificationDate) {
            $this->setVerificationDate($verificationDate);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Has the instruction been verified.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    public function getVerified(): null|FHIRBoolean
    {
        return $this->verified ?? null;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Has the instruction been verified.
     *
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $verified
     * @return static
     */
    public function setVerified(null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $verified): self
    {
        if (null === $verified) {
            unset($this->verified);
            return $this;
        }
        if (!($verified instanceof FHIRBoolean)) {
            $verified = new FHIRBoolean(value: $verified);
        }
        $this->verified = $verified;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Who verified the instruction (Patient, Relative or other Authorized Person).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getVerifiedWith(): null|FHIRReference
    {
        return $this->verifiedWith ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Who verified the instruction (Patient, Relative or other Authorized Person).
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $verifiedWith
     * @return static
     */
    public function setVerifiedWith(null|FHIRReference $verifiedWith): self
    {
        if (null === $verifiedWith) {
            unset($this->verifiedWith);
            return $this;
        }
        $this->verifiedWith = $verifiedWith;
        return $this;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Date verification was collected.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    public function getVerificationDate(): null|FHIRDateTime
    {
        return $this->verificationDate ?? null;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Date verification was collected.
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $verificationDate
     * @return static
     */
    public function setVerificationDate(null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $verificationDate): self
    {
        if (null === $verificationDate) {
            unset($this->verificationDate);
            return $this;
        }
        if (!($verificationDate instanceof FHIRDateTime)) {
            $verificationDate = new FHIRDateTime(value: $verificationDate);
        }
        $this->verificationDate = $verificationDate;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConsent\FHIRConsentVerification $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConsent\FHIRConsentVerification
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRConsentVerification)) {
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
            } else if (self::FIELD_VERIFIED === $cen) {
                $type->setVerified(FHIRBoolean::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VERIFIED_WITH === $cen) {
                $type->setVerifiedWith(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VERIFICATION_DATE === $cen) {
                $type->setVerificationDate(FHIRDateTime::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_VERIFIED])) {
            if (isset($type->verified)) {
                $type->verified->setValue((string)$attributes[self::FIELD_VERIFIED]);
            } else {
                $type->setVerified((string)$attributes[self::FIELD_VERIFIED]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_VERIFIED, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_VERIFICATION_DATE])) {
            if (isset($type->verificationDate)) {
                $type->verificationDate->setValue((string)$attributes[self::FIELD_VERIFICATION_DATE]);
            } else {
                $type->setVerificationDate((string)$attributes[self::FIELD_VERIFICATION_DATE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_VERIFICATION_DATE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->verified) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_VERIFIED]) {
            $xw->writeAttribute(self::FIELD_VERIFIED, $this->verified->_getValueAsString());
        }
        if (isset($this->verificationDate) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_VERIFICATION_DATE]) {
            $xw->writeAttribute(self::FIELD_VERIFICATION_DATE, $this->verificationDate->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->verified)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_VERIFIED]
                || $this->verified->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_VERIFIED);
            $this->verified->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_VERIFIED]);
            $xw->endElement();
        }
        if (isset($this->verifiedWith)) {
            $xw->startElement(self::FIELD_VERIFIED_WITH);
            $this->verifiedWith->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->verificationDate)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_VERIFICATION_DATE]
                || $this->verificationDate->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_VERIFICATION_DATE);
            $this->verificationDate->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_VERIFICATION_DATE]);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConsent\FHIRConsentVerification $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRConsent\FHIRConsentVerification
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
        } else if (!($type instanceof FHIRConsentVerification)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->verified)
            || isset($decoded->_verified)
            || property_exists($decoded, self::FIELD_VERIFIED)
            || property_exists($decoded, self::FIELD_VERIFIED_EXT)) {
            $v = $decoded->_verified ?? new \stdClass();
            $v->value = $decoded->verified ?? null;
            $type->setVerified(FHIRBoolean::jsonUnserialize($v, $config));
        }
        if (isset($decoded->verifiedWith) || property_exists($decoded, self::FIELD_VERIFIED_WITH)) {
            if (is_array($decoded->verifiedWith)) {
                $type->setVerifiedWith(FHIRReference::jsonUnserialize(reset($decoded->verifiedWith), $config));
            } else {
                $type->setVerifiedWith(FHIRReference::jsonUnserialize($decoded->verifiedWith, $config));
            }
        }
        if (isset($decoded->verificationDate)
            || isset($decoded->_verificationDate)
            || property_exists($decoded, self::FIELD_VERIFICATION_DATE)
            || property_exists($decoded, self::FIELD_VERIFICATION_DATE_EXT)) {
            $v = $decoded->_verificationDate ?? new \stdClass();
            $v->value = $decoded->verificationDate ?? null;
            $type->setVerificationDate(FHIRDateTime::jsonUnserialize($v, $config));
        }
        return $type;
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize(): mixed
    {
        $out = parent::jsonSerialize();
        if (isset($this->verified)) {
            if (null !== ($val = $this->verified->getValue())) {
                $out->verified = $val;
            }
            if ($this->verified->_nonValueFieldDefined()) {
                $ext = $this->verified->jsonSerialize();
                unset($ext->value);
                $out->_verified = $ext;
            }
        }
        if (isset($this->verifiedWith)) {
            $out->verifiedWith = $this->verifiedWith;
        }
        if (isset($this->verificationDate)) {
            if (null !== ($val = $this->verificationDate->getValue())) {
                $out->verificationDate = $val;
            }
            if ($this->verificationDate->_nonValueFieldDefined()) {
                $ext = $this->verificationDate->jsonSerialize();
                unset($ext->value);
                $out->_verificationDate = $ext;
            }
        }
        return $out;
    }
}
