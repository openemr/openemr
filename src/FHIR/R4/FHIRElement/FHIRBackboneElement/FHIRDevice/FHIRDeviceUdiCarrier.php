<?php

namespace OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRDevice;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: September 10th, 2022 20:42+0000
 * 
 * PHPFHIR Copyright:
 * 
 * Copyright 2016-2022 Daniel Carbone (daniel.p.carbone@gmail.com)
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

use OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\R4\FHIRElement\FHIRBase64Binary;
use OpenEMR\FHIR\R4\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUDIEntryType;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUri;
use OpenEMR\FHIR\R4\FHIRStringPrimitive;
use OpenEMR\FHIR\R4\PHPFHIRConstants;
use OpenEMR\FHIR\R4\PHPFHIRTypeInterface;

/**
 * A type of a manufactured item that is used in the provision of healthcare
 * without being substantially changed through that activity. The device may be a
 * medical or non-medical device.
 *
 * Class FHIRDeviceUdiCarrier
 * @package \OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRDevice
 */
class FHIRDeviceUdiCarrier extends FHIRBackboneElement
{
    // name of FHIR type this class describes
    const FHIR_TYPE_NAME = PHPFHIRConstants::TYPE_NAME_DEVICE_DOT_UDI_CARRIER;
    const FIELD_DEVICE_IDENTIFIER = 'deviceIdentifier';
    const FIELD_DEVICE_IDENTIFIER_EXT = '_deviceIdentifier';
    const FIELD_ISSUER = 'issuer';
    const FIELD_ISSUER_EXT = '_issuer';
    const FIELD_JURISDICTION = 'jurisdiction';
    const FIELD_JURISDICTION_EXT = '_jurisdiction';
    const FIELD_CARRIER_AIDC = 'carrierAIDC';
    const FIELD_CARRIER_AIDC_EXT = '_carrierAIDC';
    const FIELD_CARRIER_HRF = 'carrierHRF';
    const FIELD_CARRIER_HRF_EXT = '_carrierHRF';
    const FIELD_ENTRY_TYPE = 'entryType';
    const FIELD_ENTRY_TYPE_EXT = '_entryType';

    /** @var string */
    private $_xmlns = '';

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The device identifier (DI) is a mandatory, fixed portion of a UDI that
     * identifies the labeler and the specific version or model of a device.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    protected $deviceIdentifier = null;

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Organization that is charged with issuing UDIs for devices. For example, the US
     * FDA issuers include : 1) GS1: http://hl7.org/fhir/NamingSystem/gs1-di, 2) HIBCC:
     * http://hl7.org/fhir/NamingSystem/hibcc-dI, 3) ICCBBA for blood containers:
     * http://hl7.org/fhir/NamingSystem/iccbba-blood-di, 4) ICCBA for other devices:
     * http://hl7.org/fhir/NamingSystem/iccbba-other-di.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    protected $issuer = null;

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The identity of the authoritative source for UDI generation within a
     * jurisdiction. All UDIs are globally unique within a single namespace with the
     * appropriate repository uri as the system. For example, UDIs of devices managed
     * in the U.S. by the FDA, the value is http://hl7.org/fhir/NamingSystem/fda-udi.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    protected $jurisdiction = null;

    /**
     * A stream of bytes
     * A stream of bytes, base64 encoded
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The full UDI carrier of the Automatic Identification and Data Capture (AIDC)
     * technology representation of the barcode string as printed on the packaging of
     * the device - e.g., a barcode or RFID. Because of limitations on character sets
     * in XML and the need to round-trip JSON data through XML, AIDC Formats *SHALL* be
     * base64 encoded.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBase64Binary
     */
    protected $carrierAIDC = null;

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The full UDI carrier as the human readable form (HRF) representation of the
     * barcode string as printed on the packaging of the device.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    protected $carrierHRF = null;

    /**
     * Codes to identify how UDI data was entered.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A coded entry to indicate how the data was entered.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRUDIEntryType
     */
    protected $entryType = null;

    /**
     * Validation map for fields in type Device.UdiCarrier
     * @var array
     */
    private static $_validationRules = [    ];

    /**
     * FHIRDeviceUdiCarrier Constructor
     * @param null|array $data
     */
    public function __construct($data = null)
    {
        if (null === $data || [] === $data) {
            return;
        }
        if (!is_array($data)) {
            throw new \InvalidArgumentException(sprintf(
                'FHIRDeviceUdiCarrier::_construct - $data expected to be null or array, %s seen',
                gettype($data)
            ));
        }
        parent::__construct($data);
        if (isset($data[self::FIELD_DEVICE_IDENTIFIER]) || isset($data[self::FIELD_DEVICE_IDENTIFIER_EXT])) {
            $value = isset($data[self::FIELD_DEVICE_IDENTIFIER]) ? $data[self::FIELD_DEVICE_IDENTIFIER] : null;
            $ext = (isset($data[self::FIELD_DEVICE_IDENTIFIER_EXT]) && is_array($data[self::FIELD_DEVICE_IDENTIFIER_EXT])) ? $ext = $data[self::FIELD_DEVICE_IDENTIFIER_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRString) {
                    $this->setDeviceIdentifier($value);
                } else if (is_array($value)) {
                    $this->setDeviceIdentifier(new FHIRString(array_merge($ext, $value)));
                } else {
                    $this->setDeviceIdentifier(new FHIRString([FHIRString::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setDeviceIdentifier(new FHIRString($ext));
            }
        }
        if (isset($data[self::FIELD_ISSUER]) || isset($data[self::FIELD_ISSUER_EXT])) {
            $value = isset($data[self::FIELD_ISSUER]) ? $data[self::FIELD_ISSUER] : null;
            $ext = (isset($data[self::FIELD_ISSUER_EXT]) && is_array($data[self::FIELD_ISSUER_EXT])) ? $ext = $data[self::FIELD_ISSUER_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRUri) {
                    $this->setIssuer($value);
                } else if (is_array($value)) {
                    $this->setIssuer(new FHIRUri(array_merge($ext, $value)));
                } else {
                    $this->setIssuer(new FHIRUri([FHIRUri::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setIssuer(new FHIRUri($ext));
            }
        }
        if (isset($data[self::FIELD_JURISDICTION]) || isset($data[self::FIELD_JURISDICTION_EXT])) {
            $value = isset($data[self::FIELD_JURISDICTION]) ? $data[self::FIELD_JURISDICTION] : null;
            $ext = (isset($data[self::FIELD_JURISDICTION_EXT]) && is_array($data[self::FIELD_JURISDICTION_EXT])) ? $ext = $data[self::FIELD_JURISDICTION_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRUri) {
                    $this->setJurisdiction($value);
                } else if (is_array($value)) {
                    $this->setJurisdiction(new FHIRUri(array_merge($ext, $value)));
                } else {
                    $this->setJurisdiction(new FHIRUri([FHIRUri::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setJurisdiction(new FHIRUri($ext));
            }
        }
        if (isset($data[self::FIELD_CARRIER_AIDC]) || isset($data[self::FIELD_CARRIER_AIDC_EXT])) {
            $value = isset($data[self::FIELD_CARRIER_AIDC]) ? $data[self::FIELD_CARRIER_AIDC] : null;
            $ext = (isset($data[self::FIELD_CARRIER_AIDC_EXT]) && is_array($data[self::FIELD_CARRIER_AIDC_EXT])) ? $ext = $data[self::FIELD_CARRIER_AIDC_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRBase64Binary) {
                    $this->setCarrierAIDC($value);
                } else if (is_array($value)) {
                    $this->setCarrierAIDC(new FHIRBase64Binary(array_merge($ext, $value)));
                } else {
                    $this->setCarrierAIDC(new FHIRBase64Binary([FHIRBase64Binary::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setCarrierAIDC(new FHIRBase64Binary($ext));
            }
        }
        if (isset($data[self::FIELD_CARRIER_HRF]) || isset($data[self::FIELD_CARRIER_HRF_EXT])) {
            $value = isset($data[self::FIELD_CARRIER_HRF]) ? $data[self::FIELD_CARRIER_HRF] : null;
            $ext = (isset($data[self::FIELD_CARRIER_HRF_EXT]) && is_array($data[self::FIELD_CARRIER_HRF_EXT])) ? $ext = $data[self::FIELD_CARRIER_HRF_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRString) {
                    $this->setCarrierHRF($value);
                } else if (is_array($value)) {
                    $this->setCarrierHRF(new FHIRString(array_merge($ext, $value)));
                } else {
                    $this->setCarrierHRF(new FHIRString([FHIRString::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setCarrierHRF(new FHIRString($ext));
            }
        }
        if (isset($data[self::FIELD_ENTRY_TYPE]) || isset($data[self::FIELD_ENTRY_TYPE_EXT])) {
            $value = isset($data[self::FIELD_ENTRY_TYPE]) ? $data[self::FIELD_ENTRY_TYPE] : null;
            $ext = (isset($data[self::FIELD_ENTRY_TYPE_EXT]) && is_array($data[self::FIELD_ENTRY_TYPE_EXT])) ? $ext = $data[self::FIELD_ENTRY_TYPE_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRUDIEntryType) {
                    $this->setEntryType($value);
                } else if (is_array($value)) {
                    $this->setEntryType(new FHIRUDIEntryType(array_merge($ext, $value)));
                } else {
                    $this->setEntryType(new FHIRUDIEntryType([FHIRUDIEntryType::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setEntryType(new FHIRUDIEntryType($ext));
            }
        }
    }

    /**
     * @return string
     */
    public function _getFHIRTypeName()
    {
        return self::FHIR_TYPE_NAME;
    }

    /**
     * @return string
     */
    public function _getFHIRXMLElementDefinition()
    {
        $xmlns = $this->_getFHIRXMLNamespace();
        if ('' !==  $xmlns) {
            $xmlns = " xmlns=\"{$xmlns}\"";
        }
        return "<DeviceUdiCarrier{$xmlns}></DeviceUdiCarrier>";
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The device identifier (DI) is a mandatory, fixed portion of a UDI that
     * identifies the labeler and the specific version or model of a device.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getDeviceIdentifier()
    {
        return $this->deviceIdentifier;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The device identifier (DI) is a mandatory, fixed portion of a UDI that
     * identifies the labeler and the specific version or model of a device.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString $deviceIdentifier
     * @return static
     */
    public function setDeviceIdentifier($deviceIdentifier = null)
    {
        if (null !== $deviceIdentifier && !($deviceIdentifier instanceof FHIRString)) {
            $deviceIdentifier = new FHIRString($deviceIdentifier);
        }
        $this->_trackValueSet($this->deviceIdentifier, $deviceIdentifier);
        $this->deviceIdentifier = $deviceIdentifier;
        return $this;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Organization that is charged with issuing UDIs for devices. For example, the US
     * FDA issuers include : 1) GS1: http://hl7.org/fhir/NamingSystem/gs1-di, 2) HIBCC:
     * http://hl7.org/fhir/NamingSystem/hibcc-dI, 3) ICCBBA for blood containers:
     * http://hl7.org/fhir/NamingSystem/iccbba-blood-di, 4) ICCBA for other devices:
     * http://hl7.org/fhir/NamingSystem/iccbba-other-di.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public function getIssuer()
    {
        return $this->issuer;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Organization that is charged with issuing UDIs for devices. For example, the US
     * FDA issuers include : 1) GS1: http://hl7.org/fhir/NamingSystem/gs1-di, 2) HIBCC:
     * http://hl7.org/fhir/NamingSystem/hibcc-dI, 3) ICCBBA for blood containers:
     * http://hl7.org/fhir/NamingSystem/iccbba-blood-di, 4) ICCBA for other devices:
     * http://hl7.org/fhir/NamingSystem/iccbba-other-di.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRUri $issuer
     * @return static
     */
    public function setIssuer($issuer = null)
    {
        if (null !== $issuer && !($issuer instanceof FHIRUri)) {
            $issuer = new FHIRUri($issuer);
        }
        $this->_trackValueSet($this->issuer, $issuer);
        $this->issuer = $issuer;
        return $this;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The identity of the authoritative source for UDI generation within a
     * jurisdiction. All UDIs are globally unique within a single namespace with the
     * appropriate repository uri as the system. For example, UDIs of devices managed
     * in the U.S. by the FDA, the value is http://hl7.org/fhir/NamingSystem/fda-udi.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRUri
     */
    public function getJurisdiction()
    {
        return $this->jurisdiction;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The identity of the authoritative source for UDI generation within a
     * jurisdiction. All UDIs are globally unique within a single namespace with the
     * appropriate repository uri as the system. For example, UDIs of devices managed
     * in the U.S. by the FDA, the value is http://hl7.org/fhir/NamingSystem/fda-udi.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRUri $jurisdiction
     * @return static
     */
    public function setJurisdiction($jurisdiction = null)
    {
        if (null !== $jurisdiction && !($jurisdiction instanceof FHIRUri)) {
            $jurisdiction = new FHIRUri($jurisdiction);
        }
        $this->_trackValueSet($this->jurisdiction, $jurisdiction);
        $this->jurisdiction = $jurisdiction;
        return $this;
    }

    /**
     * A stream of bytes
     * A stream of bytes, base64 encoded
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The full UDI carrier of the Automatic Identification and Data Capture (AIDC)
     * technology representation of the barcode string as printed on the packaging of
     * the device - e.g., a barcode or RFID. Because of limitations on character sets
     * in XML and the need to round-trip JSON data through XML, AIDC Formats *SHALL* be
     * base64 encoded.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBase64Binary
     */
    public function getCarrierAIDC()
    {
        return $this->carrierAIDC;
    }

    /**
     * A stream of bytes
     * A stream of bytes, base64 encoded
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The full UDI carrier of the Automatic Identification and Data Capture (AIDC)
     * technology representation of the barcode string as printed on the packaging of
     * the device - e.g., a barcode or RFID. Because of limitations on character sets
     * in XML and the need to round-trip JSON data through XML, AIDC Formats *SHALL* be
     * base64 encoded.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBase64Binary $carrierAIDC
     * @return static
     */
    public function setCarrierAIDC($carrierAIDC = null)
    {
        if (null !== $carrierAIDC && !($carrierAIDC instanceof FHIRBase64Binary)) {
            $carrierAIDC = new FHIRBase64Binary($carrierAIDC);
        }
        $this->_trackValueSet($this->carrierAIDC, $carrierAIDC);
        $this->carrierAIDC = $carrierAIDC;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The full UDI carrier as the human readable form (HRF) representation of the
     * barcode string as printed on the packaging of the device.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getCarrierHRF()
    {
        return $this->carrierHRF;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The full UDI carrier as the human readable form (HRF) representation of the
     * barcode string as printed on the packaging of the device.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString $carrierHRF
     * @return static
     */
    public function setCarrierHRF($carrierHRF = null)
    {
        if (null !== $carrierHRF && !($carrierHRF instanceof FHIRString)) {
            $carrierHRF = new FHIRString($carrierHRF);
        }
        $this->_trackValueSet($this->carrierHRF, $carrierHRF);
        $this->carrierHRF = $carrierHRF;
        return $this;
    }

    /**
     * Codes to identify how UDI data was entered.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A coded entry to indicate how the data was entered.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRUDIEntryType
     */
    public function getEntryType()
    {
        return $this->entryType;
    }

    /**
     * Codes to identify how UDI data was entered.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A coded entry to indicate how the data was entered.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRUDIEntryType $entryType
     * @return static
     */
    public function setEntryType(FHIRUDIEntryType $entryType = null)
    {
        $this->_trackValueSet($this->entryType, $entryType);
        $this->entryType = $entryType;
        return $this;
    }

    /**
     * Returns the validation rules that this type's fields must comply with to be considered "valid"
     * The returned array is in ["fieldname[.offset]" => ["rule" => {constraint}]]
     *
     * @return array
     */
    public function _getValidationRules()
    {
        return self::$_validationRules;
    }

    /**
     * Validates that this type conforms to the specifications set forth for it by FHIR.  An empty array must be seen as
     * passing.
     *
     * @return array
     */
    public function _getValidationErrors()
    {
        $errs = parent::_getValidationErrors();
        $validationRules = $this->_getValidationRules();
        if (null !== ($v = $this->getDeviceIdentifier())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_DEVICE_IDENTIFIER] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getIssuer())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_ISSUER] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getJurisdiction())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_JURISDICTION] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getCarrierAIDC())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_CARRIER_AIDC] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getCarrierHRF())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_CARRIER_HRF] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getEntryType())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_ENTRY_TYPE] = $fieldErrs;
            }
        }
        if (isset($validationRules[self::FIELD_DEVICE_IDENTIFIER])) {
            $v = $this->getDeviceIdentifier();
            foreach($validationRules[self::FIELD_DEVICE_IDENTIFIER] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_DEVICE_DOT_UDI_CARRIER, self::FIELD_DEVICE_IDENTIFIER, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_DEVICE_IDENTIFIER])) {
                        $errs[self::FIELD_DEVICE_IDENTIFIER] = [];
                    }
                    $errs[self::FIELD_DEVICE_IDENTIFIER][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_ISSUER])) {
            $v = $this->getIssuer();
            foreach($validationRules[self::FIELD_ISSUER] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_DEVICE_DOT_UDI_CARRIER, self::FIELD_ISSUER, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_ISSUER])) {
                        $errs[self::FIELD_ISSUER] = [];
                    }
                    $errs[self::FIELD_ISSUER][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_JURISDICTION])) {
            $v = $this->getJurisdiction();
            foreach($validationRules[self::FIELD_JURISDICTION] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_DEVICE_DOT_UDI_CARRIER, self::FIELD_JURISDICTION, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_JURISDICTION])) {
                        $errs[self::FIELD_JURISDICTION] = [];
                    }
                    $errs[self::FIELD_JURISDICTION][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_CARRIER_AIDC])) {
            $v = $this->getCarrierAIDC();
            foreach($validationRules[self::FIELD_CARRIER_AIDC] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_DEVICE_DOT_UDI_CARRIER, self::FIELD_CARRIER_AIDC, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_CARRIER_AIDC])) {
                        $errs[self::FIELD_CARRIER_AIDC] = [];
                    }
                    $errs[self::FIELD_CARRIER_AIDC][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_CARRIER_HRF])) {
            $v = $this->getCarrierHRF();
            foreach($validationRules[self::FIELD_CARRIER_HRF] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_DEVICE_DOT_UDI_CARRIER, self::FIELD_CARRIER_HRF, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_CARRIER_HRF])) {
                        $errs[self::FIELD_CARRIER_HRF] = [];
                    }
                    $errs[self::FIELD_CARRIER_HRF][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_ENTRY_TYPE])) {
            $v = $this->getEntryType();
            foreach($validationRules[self::FIELD_ENTRY_TYPE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_DEVICE_DOT_UDI_CARRIER, self::FIELD_ENTRY_TYPE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_ENTRY_TYPE])) {
                        $errs[self::FIELD_ENTRY_TYPE] = [];
                    }
                    $errs[self::FIELD_ENTRY_TYPE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_MODIFIER_EXTENSION])) {
            $v = $this->getModifierExtension();
            foreach($validationRules[self::FIELD_MODIFIER_EXTENSION] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_BACKBONE_ELEMENT, self::FIELD_MODIFIER_EXTENSION, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_MODIFIER_EXTENSION])) {
                        $errs[self::FIELD_MODIFIER_EXTENSION] = [];
                    }
                    $errs[self::FIELD_MODIFIER_EXTENSION][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_EXTENSION])) {
            $v = $this->getExtension();
            foreach($validationRules[self::FIELD_EXTENSION] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_ELEMENT, self::FIELD_EXTENSION, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_EXTENSION])) {
                        $errs[self::FIELD_EXTENSION] = [];
                    }
                    $errs[self::FIELD_EXTENSION][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_ID])) {
            $v = $this->getId();
            foreach($validationRules[self::FIELD_ID] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_ELEMENT, self::FIELD_ID, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_ID])) {
                        $errs[self::FIELD_ID] = [];
                    }
                    $errs[self::FIELD_ID][$rule] = $err;
                }
            }
        }
        return $errs;
    }

    /**
     * @param null|string|\DOMElement $element
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRDevice\FHIRDeviceUdiCarrier $type
     * @param null|int $libxmlOpts
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRDevice\FHIRDeviceUdiCarrier
     */
    public static function xmlUnserialize($element = null, PHPFHIRTypeInterface $type = null, $libxmlOpts = 591872)
    {
        if (null === $element) {
            return null;
        }
        if (is_string($element)) {
            libxml_use_internal_errors(true);
            $dom = new \DOMDocument();
            $dom->loadXML($element, $libxmlOpts);
            if (false === $dom) {
                throw new \DomainException(sprintf('FHIRDeviceUdiCarrier::xmlUnserialize - String provided is not parseable as XML: %s', implode(', ', array_map(function(\libXMLError $err) { return $err->message; }, libxml_get_errors()))));
            }
            libxml_use_internal_errors(false);
            $element = $dom->documentElement;
        }
        if (!($element instanceof \DOMElement)) {
            throw new \InvalidArgumentException(sprintf('FHIRDeviceUdiCarrier::xmlUnserialize - $node value must be null, \\DOMElement, or valid XML string, %s seen', is_object($element) ? get_class($element) : gettype($element)));
        }
        if (null === $type) {
            $type = new FHIRDeviceUdiCarrier(null);
        } elseif (!is_object($type) || !($type instanceof FHIRDeviceUdiCarrier)) {
            throw new \RuntimeException(sprintf(
                'FHIRDeviceUdiCarrier::xmlUnserialize - $type must be instance of \OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRDevice\FHIRDeviceUdiCarrier or null, %s seen.',
                is_object($type) ? get_class($type) : gettype($type)
            ));
        }
        if ('' === $type->_getFHIRXMLNamespace() && (null === $element->parentNode || $element->namespaceURI !== $element->parentNode->namespaceURI)) {
            $type->_setFHIRXMLNamespace($element->namespaceURI);
        }
        for($i = 0; $i < $element->childNodes->length; $i++) {
            $n = $element->childNodes->item($i);
            if (!($n instanceof \DOMElement)) {
                continue;
            }
            if (self::FIELD_DEVICE_IDENTIFIER === $n->nodeName) {
                $type->setDeviceIdentifier(FHIRString::xmlUnserialize($n));
            } elseif (self::FIELD_ISSUER === $n->nodeName) {
                $type->setIssuer(FHIRUri::xmlUnserialize($n));
            } elseif (self::FIELD_JURISDICTION === $n->nodeName) {
                $type->setJurisdiction(FHIRUri::xmlUnserialize($n));
            } elseif (self::FIELD_CARRIER_AIDC === $n->nodeName) {
                $type->setCarrierAIDC(FHIRBase64Binary::xmlUnserialize($n));
            } elseif (self::FIELD_CARRIER_HRF === $n->nodeName) {
                $type->setCarrierHRF(FHIRString::xmlUnserialize($n));
            } elseif (self::FIELD_ENTRY_TYPE === $n->nodeName) {
                $type->setEntryType(FHIRUDIEntryType::xmlUnserialize($n));
            } elseif (self::FIELD_MODIFIER_EXTENSION === $n->nodeName) {
                $type->addModifierExtension(FHIRExtension::xmlUnserialize($n));
            } elseif (self::FIELD_EXTENSION === $n->nodeName) {
                $type->addExtension(FHIRExtension::xmlUnserialize($n));
            } elseif (self::FIELD_ID === $n->nodeName) {
                $type->setId(FHIRStringPrimitive::xmlUnserialize($n));
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_DEVICE_IDENTIFIER);
        if (null !== $n) {
            $pt = $type->getDeviceIdentifier();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setDeviceIdentifier($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_ISSUER);
        if (null !== $n) {
            $pt = $type->getIssuer();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setIssuer($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_JURISDICTION);
        if (null !== $n) {
            $pt = $type->getJurisdiction();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setJurisdiction($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_CARRIER_AIDC);
        if (null !== $n) {
            $pt = $type->getCarrierAIDC();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setCarrierAIDC($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_CARRIER_HRF);
        if (null !== $n) {
            $pt = $type->getCarrierHRF();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setCarrierHRF($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_ID);
        if (null !== $n) {
            $pt = $type->getId();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setId($n->nodeValue);
            }
        }
        return $type;
    }

    /**
     * @param null|\DOMElement $element
     * @param null|int $libxmlOpts
     * @return \DOMElement
     */
    public function xmlSerialize(\DOMElement $element = null, $libxmlOpts = 591872)
    {
        if (null === $element) {
            $dom = new \DOMDocument();
            $dom->loadXML($this->_getFHIRXMLElementDefinition(), $libxmlOpts);
            $element = $dom->documentElement;
        } elseif (null === $element->namespaceURI && '' !== ($xmlns = $this->_getFHIRXMLNamespace())) {
            $element->setAttribute('xmlns', $xmlns);
        }
        parent::xmlSerialize($element);
        if (null !== ($v = $this->getDeviceIdentifier())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_DEVICE_IDENTIFIER);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getIssuer())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_ISSUER);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getJurisdiction())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_JURISDICTION);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getCarrierAIDC())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_CARRIER_AIDC);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getCarrierHRF())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_CARRIER_HRF);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getEntryType())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_ENTRY_TYPE);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        return $element;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $a = parent::jsonSerialize();
        if (null !== ($v = $this->getDeviceIdentifier())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_DEVICE_IDENTIFIER] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRString::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_DEVICE_IDENTIFIER_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getIssuer())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_ISSUER] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRUri::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_ISSUER_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getJurisdiction())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_JURISDICTION] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRUri::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_JURISDICTION_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getCarrierAIDC())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_CARRIER_AIDC] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRBase64Binary::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_CARRIER_AIDC_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getCarrierHRF())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_CARRIER_HRF] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRString::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_CARRIER_HRF_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getEntryType())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_ENTRY_TYPE] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRUDIEntryType::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_ENTRY_TYPE_EXT] = $ext;
            }
        }
        return $a;
    }


    /**
     * @return string
     */
    public function __toString()
    {
        return self::FHIR_TYPE_NAME;
    }
}