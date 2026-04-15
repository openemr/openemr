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
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceDefinition\FHIRDeviceDefinitionCapability;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceDefinition\FHIRDeviceDefinitionDeviceName;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceDefinition\FHIRDeviceDefinitionMaterial;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceDefinition\FHIRDeviceDefinitionProperty;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceDefinition\FHIRDeviceDefinitionSpecialization;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceDefinition\FHIRDeviceDefinitionUdiDeviceIdentifier;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRProdCharacteristic;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRProductShelfLife;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactPoint;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive;
use OpenEMR\FHIR\Versions\R4\Version;
use OpenEMR\FHIR\Versions\R4\VersionConstants;
use OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface;
use OpenEMR\FHIR\Versions\R4\VersionTypeMap;

/**
 * The characteristics, operational status and capabilities of a medical-related
 * component of a medical device.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRDeviceDefinition extends FHIRDomainResource implements VersionContainedTypeInterface
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_DEVICE_DEFINITION;

    /* class_default.php:56 */
    public const FIELD_IDENTIFIER = 'identifier';
    public const FIELD_UDI_DEVICE_IDENTIFIER = 'udiDeviceIdentifier';
    public const FIELD_MANUFACTURER_STRING = 'manufacturerString';
    public const FIELD_MANUFACTURER_STRING_EXT = '_manufacturerString';
    public const FIELD_MANUFACTURER_REFERENCE = 'manufacturerReference';
    public const FIELD_DEVICE_NAME = 'deviceName';
    public const FIELD_MODEL_NUMBER = 'modelNumber';
    public const FIELD_MODEL_NUMBER_EXT = '_modelNumber';
    public const FIELD_TYPE = 'type';
    public const FIELD_SPECIALIZATION = 'specialization';
    public const FIELD_VERSION = 'version';
    public const FIELD_VERSION_EXT = '_version';
    public const FIELD_SAFETY = 'safety';
    public const FIELD_SHELF_LIFE_STORAGE = 'shelfLifeStorage';
    public const FIELD_PHYSICAL_CHARACTERISTICS = 'physicalCharacteristics';
    public const FIELD_LANGUAGE_CODE = 'languageCode';
    public const FIELD_CAPABILITY = 'capability';
    public const FIELD_PROPERTY = 'property';
    public const FIELD_OWNER = 'owner';
    public const FIELD_CONTACT = 'contact';
    public const FIELD_URL = 'url';
    public const FIELD_URL_EXT = '_url';
    public const FIELD_ONLINE_INFORMATION = 'onlineInformation';
    public const FIELD_ONLINE_INFORMATION_EXT = '_onlineInformation';
    public const FIELD_NOTE = 'note';
    public const FIELD_QUANTITY = 'quantity';
    public const FIELD_PARENT_DEVICE = 'parentDevice';
    public const FIELD_MATERIAL = 'material';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_MANUFACTURER_STRING => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_MODEL_NUMBER => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_URL => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_ONLINE_INFORMATION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Unique instance identifiers assigned to a device by the software, manufacturers,
     * other organizations or owners. For example: handle ID.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    #[FHIRIdentifier]
    protected array $identifier;
    /**
     * The characteristics, operational status and capabilities of a medical-related
     * component of a medical device.
     *
     * Unique device identifier (UDI) assigned to device label or package. Note that
     * the Device may include multiple udiCarriers as it either may include just the
     * udiCarrier for the jurisdiction it is sold, or for multiple jurisdictions it
     * could have been sold.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceDefinition\FHIRDeviceDefinitionUdiDeviceIdentifier>
     */
    #[FHIRDeviceDefinitionUdiDeviceIdentifier]
    protected array $udiDeviceIdentifier;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A name of the manufacturer. (choose any one of manufacturer*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $manufacturerString;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A name of the manufacturer. (choose any one of manufacturer*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $manufacturerReference;
    /**
     * The characteristics, operational status and capabilities of a medical-related
     * component of a medical device.
     *
     * A name given to the device to identify it.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceDefinition\FHIRDeviceDefinitionDeviceName>
     */
    #[FHIRDeviceDefinitionDeviceName]
    protected array $deviceName;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The model number for the device.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $modelNumber;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * What kind of device or device system this is.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $type;
    /**
     * The characteristics, operational status and capabilities of a medical-related
     * component of a medical device.
     *
     * The capabilities supported on a device, the standards to which the device
     * conforms for a particular purpose, and used for the communication.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceDefinition\FHIRDeviceDefinitionSpecialization>
     */
    #[FHIRDeviceDefinitionSpecialization]
    protected array $specialization;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The available versions of the device, e.g., software versions.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    #[FHIRString]
    protected array $version;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Safety characteristics of the device.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $safety;
    /**
     * The shelf-life and storage information for a medicinal product item or container
     * can be described using this class.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Shelf Life and storage information.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRProductShelfLife>
     */
    #[FHIRProductShelfLife]
    protected array $shelfLifeStorage;
    /**
     * The marketing status describes the date when a medicinal product is actually put
     * on the market or the date as of which it is no longer available.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Dimensions, color etc.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRProdCharacteristic
     */
    #[FHIRProdCharacteristic]
    protected FHIRProdCharacteristic $physicalCharacteristics;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Language code for the human-readable text strings produced by the device (all
     * supported).
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $languageCode;
    /**
     * The characteristics, operational status and capabilities of a medical-related
     * component of a medical device.
     *
     * Device capabilities.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceDefinition\FHIRDeviceDefinitionCapability>
     */
    #[FHIRDeviceDefinitionCapability]
    protected array $capability;
    /**
     * The characteristics, operational status and capabilities of a medical-related
     * component of a medical device.
     *
     * The actual configuration settings of a device as it actually operates, e.g.,
     * regulation status, time properties.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceDefinition\FHIRDeviceDefinitionProperty>
     */
    #[FHIRDeviceDefinitionProperty]
    protected array $property;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * An organization that is responsible for the provision and ongoing maintenance of
     * the device.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $owner;
    /**
     * Details for all kinds of technology mediated contact points for a person or
     * organization, including telephone, email, etc.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Contact details for an organization or a particular human that is responsible
     * for the device.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactPoint>
     */
    #[FHIRContactPoint]
    protected array $contact;
    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A network address on which the device may be contacted directly.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    #[FHIRUri]
    protected FHIRUri $url;
    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Access to on-line information about the device.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    #[FHIRUri]
    protected FHIRUri $onlineInformation;
    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Descriptive information, usage information or implantation information that is
     * not captured in an existing element.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation>
     */
    #[FHIRAnnotation]
    protected array $note;
    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The quantity of the device present in the packaging (e.g. the number of devices
     * present in a pack, or the number of devices in the same package of the medicinal
     * product).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    #[FHIRQuantity]
    protected FHIRQuantity $quantity;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The parent device it can be part of.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $parentDevice;
    /**
     * The characteristics, operational status and capabilities of a medical-related
     * component of a medical device.
     *
     * A substance used to create the material(s) of which the device is made.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceDefinition\FHIRDeviceDefinitionMaterial>
     */
    #[FHIRDeviceDefinitionMaterial]
    protected array $material;

    /* constructor.php:61 */
    /**
     * FHIRDeviceDefinition Constructor
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $id
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $meta
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $implicitRules
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative $text
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer>|iterable<\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface> $contained
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier> $identifier
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceDefinition\FHIRDeviceDefinitionUdiDeviceIdentifier> $udiDeviceIdentifier
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $manufacturerString
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $manufacturerReference
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceDefinition\FHIRDeviceDefinitionDeviceName> $deviceName
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $modelNumber
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $type
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceDefinition\FHIRDeviceDefinitionSpecialization> $specialization
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString> $version
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $safety
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRProductShelfLife> $shelfLifeStorage
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRProdCharacteristic $physicalCharacteristics
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $languageCode
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceDefinition\FHIRDeviceDefinitionCapability> $capability
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceDefinition\FHIRDeviceDefinitionProperty> $property
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $owner
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactPoint> $contact
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $url
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $onlineInformation
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation> $note
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $quantity
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $parentDevice
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceDefinition\FHIRDeviceDefinitionMaterial> $material
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
                                null|iterable $udiDeviceIdentifier = null,
                                null|string|FHIRStringPrimitive|FHIRString $manufacturerString = null,
                                null|FHIRReference $manufacturerReference = null,
                                null|iterable $deviceName = null,
                                null|string|FHIRStringPrimitive|FHIRString $modelNumber = null,
                                null|FHIRCodeableConcept $type = null,
                                null|iterable $specialization = null,
                                null|iterable $version = null,
                                null|iterable $safety = null,
                                null|iterable $shelfLifeStorage = null,
                                null|FHIRProdCharacteristic $physicalCharacteristics = null,
                                null|iterable $languageCode = null,
                                null|iterable $capability = null,
                                null|iterable $property = null,
                                null|FHIRReference $owner = null,
                                null|iterable $contact = null,
                                null|string|FHIRUriPrimitive|FHIRUri $url = null,
                                null|string|FHIRUriPrimitive|FHIRUri $onlineInformation = null,
                                null|iterable $note = null,
                                null|FHIRQuantity $quantity = null,
                                null|FHIRReference $parentDevice = null,
                                null|iterable $material = null,
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
        if (null !== $udiDeviceIdentifier) {
            $this->setUdiDeviceIdentifier(...$udiDeviceIdentifier);
        }
        if (null !== $manufacturerString) {
            $this->setManufacturerString($manufacturerString);
        }
        if (null !== $manufacturerReference) {
            $this->setManufacturerReference($manufacturerReference);
        }
        if (null !== $deviceName) {
            $this->setDeviceName(...$deviceName);
        }
        if (null !== $modelNumber) {
            $this->setModelNumber($modelNumber);
        }
        if (null !== $type) {
            $this->setType($type);
        }
        if (null !== $specialization) {
            $this->setSpecialization(...$specialization);
        }
        if (null !== $version) {
            $this->setVersion(...$version);
        }
        if (null !== $safety) {
            $this->setSafety(...$safety);
        }
        if (null !== $shelfLifeStorage) {
            $this->setShelfLifeStorage(...$shelfLifeStorage);
        }
        if (null !== $physicalCharacteristics) {
            $this->setPhysicalCharacteristics($physicalCharacteristics);
        }
        if (null !== $languageCode) {
            $this->setLanguageCode(...$languageCode);
        }
        if (null !== $capability) {
            $this->setCapability(...$capability);
        }
        if (null !== $property) {
            $this->setProperty(...$property);
        }
        if (null !== $owner) {
            $this->setOwner($owner);
        }
        if (null !== $contact) {
            $this->setContact(...$contact);
        }
        if (null !== $url) {
            $this->setUrl($url);
        }
        if (null !== $onlineInformation) {
            $this->setOnlineInformation($onlineInformation);
        }
        if (null !== $note) {
            $this->setNote(...$note);
        }
        if (null !== $quantity) {
            $this->setQuantity($quantity);
        }
        if (null !== $parentDevice) {
            $this->setParentDevice($parentDevice);
        }
        if (null !== $material) {
            $this->setMaterial(...$material);
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
     * Unique instance identifiers assigned to a device by the software, manufacturers,
     * other organizations or owners. For example: handle ID.
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
     * Unique instance identifiers assigned to a device by the software, manufacturers,
     * other organizations or owners. For example: handle ID.
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
     * Unique instance identifiers assigned to a device by the software, manufacturers,
     * other organizations or owners. For example: handle ID.
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
     * The characteristics, operational status and capabilities of a medical-related
     * component of a medical device.
     *
     * Unique device identifier (UDI) assigned to device label or package. Note that
     * the Device may include multiple udiCarriers as it either may include just the
     * udiCarrier for the jurisdiction it is sold, or for multiple jurisdictions it
     * could have been sold.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceDefinition\FHIRDeviceDefinitionUdiDeviceIdentifier>
     */
    public function getUdiDeviceIdentifier(): array
    {
        return $this->udiDeviceIdentifier ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceDefinition\FHIRDeviceDefinitionUdiDeviceIdentifier>
     */
    public function getUdiDeviceIdentifierIterator(): iterable
    {
        if (!isset($this->udiDeviceIdentifier)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->udiDeviceIdentifier);
    }

    /**
     * The characteristics, operational status and capabilities of a medical-related
     * component of a medical device.
     *
     * Unique device identifier (UDI) assigned to device label or package. Note that
     * the Device may include multiple udiCarriers as it either may include just the
     * udiCarrier for the jurisdiction it is sold, or for multiple jurisdictions it
     * could have been sold.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceDefinition\FHIRDeviceDefinitionUdiDeviceIdentifier $udiDeviceIdentifier
     * @return static
     */
    public function addUdiDeviceIdentifier(FHIRDeviceDefinitionUdiDeviceIdentifier $udiDeviceIdentifier): self
    {
        if (!isset($this->udiDeviceIdentifier)) {
            $this->udiDeviceIdentifier = [];
        }
        $this->udiDeviceIdentifier[] = $udiDeviceIdentifier;
        return $this;
    }

    /**
     * The characteristics, operational status and capabilities of a medical-related
     * component of a medical device.
     *
     * Unique device identifier (UDI) assigned to device label or package. Note that
     * the Device may include multiple udiCarriers as it either may include just the
     * udiCarrier for the jurisdiction it is sold, or for multiple jurisdictions it
     * could have been sold.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceDefinition\FHIRDeviceDefinitionUdiDeviceIdentifier ...$udiDeviceIdentifier
     * @return static
     */
    public function setUdiDeviceIdentifier(FHIRDeviceDefinitionUdiDeviceIdentifier ...$udiDeviceIdentifier): self
    {
        if ([] === $udiDeviceIdentifier) {
            unset($this->udiDeviceIdentifier);
            return $this;
        }
        $this->udiDeviceIdentifier = $udiDeviceIdentifier;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A name of the manufacturer. (choose any one of manufacturer*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getManufacturerString(): null|FHIRString
    {
        return $this->manufacturerString ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A name of the manufacturer. (choose any one of manufacturer*, but only one)
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $manufacturerString
     * @return static
     */
    public function setManufacturerString(null|string|FHIRStringPrimitive|FHIRString $manufacturerString): self
    {
        if (null === $manufacturerString) {
            unset($this->manufacturerString);
            return $this;
        }
        if (!($manufacturerString instanceof FHIRString)) {
            $manufacturerString = new FHIRString(value: $manufacturerString);
        }
        $this->manufacturerString = $manufacturerString;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A name of the manufacturer. (choose any one of manufacturer*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getManufacturerReference(): null|FHIRReference
    {
        return $this->manufacturerReference ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A name of the manufacturer. (choose any one of manufacturer*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $manufacturerReference
     * @return static
     */
    public function setManufacturerReference(null|FHIRReference $manufacturerReference): self
    {
        if (null === $manufacturerReference) {
            unset($this->manufacturerReference);
            return $this;
        }
        $this->manufacturerReference = $manufacturerReference;
        return $this;
    }

    /**
     * The characteristics, operational status and capabilities of a medical-related
     * component of a medical device.
     *
     * A name given to the device to identify it.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceDefinition\FHIRDeviceDefinitionDeviceName>
     */
    public function getDeviceName(): array
    {
        return $this->deviceName ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceDefinition\FHIRDeviceDefinitionDeviceName>
     */
    public function getDeviceNameIterator(): iterable
    {
        if (!isset($this->deviceName)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->deviceName);
    }

    /**
     * The characteristics, operational status and capabilities of a medical-related
     * component of a medical device.
     *
     * A name given to the device to identify it.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceDefinition\FHIRDeviceDefinitionDeviceName $deviceName
     * @return static
     */
    public function addDeviceName(FHIRDeviceDefinitionDeviceName $deviceName): self
    {
        if (!isset($this->deviceName)) {
            $this->deviceName = [];
        }
        $this->deviceName[] = $deviceName;
        return $this;
    }

    /**
     * The characteristics, operational status and capabilities of a medical-related
     * component of a medical device.
     *
     * A name given to the device to identify it.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceDefinition\FHIRDeviceDefinitionDeviceName ...$deviceName
     * @return static
     */
    public function setDeviceName(FHIRDeviceDefinitionDeviceName ...$deviceName): self
    {
        if ([] === $deviceName) {
            unset($this->deviceName);
            return $this;
        }
        $this->deviceName = $deviceName;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The model number for the device.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getModelNumber(): null|FHIRString
    {
        return $this->modelNumber ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The model number for the device.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $modelNumber
     * @return static
     */
    public function setModelNumber(null|string|FHIRStringPrimitive|FHIRString $modelNumber): self
    {
        if (null === $modelNumber) {
            unset($this->modelNumber);
            return $this;
        }
        if (!($modelNumber instanceof FHIRString)) {
            $modelNumber = new FHIRString(value: $modelNumber);
        }
        $this->modelNumber = $modelNumber;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * What kind of device or device system this is.
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
     * What kind of device or device system this is.
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
     * The characteristics, operational status and capabilities of a medical-related
     * component of a medical device.
     *
     * The capabilities supported on a device, the standards to which the device
     * conforms for a particular purpose, and used for the communication.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceDefinition\FHIRDeviceDefinitionSpecialization>
     */
    public function getSpecialization(): array
    {
        return $this->specialization ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceDefinition\FHIRDeviceDefinitionSpecialization>
     */
    public function getSpecializationIterator(): iterable
    {
        if (!isset($this->specialization)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->specialization);
    }

    /**
     * The characteristics, operational status and capabilities of a medical-related
     * component of a medical device.
     *
     * The capabilities supported on a device, the standards to which the device
     * conforms for a particular purpose, and used for the communication.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceDefinition\FHIRDeviceDefinitionSpecialization $specialization
     * @return static
     */
    public function addSpecialization(FHIRDeviceDefinitionSpecialization $specialization): self
    {
        if (!isset($this->specialization)) {
            $this->specialization = [];
        }
        $this->specialization[] = $specialization;
        return $this;
    }

    /**
     * The characteristics, operational status and capabilities of a medical-related
     * component of a medical device.
     *
     * The capabilities supported on a device, the standards to which the device
     * conforms for a particular purpose, and used for the communication.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceDefinition\FHIRDeviceDefinitionSpecialization ...$specialization
     * @return static
     */
    public function setSpecialization(FHIRDeviceDefinitionSpecialization ...$specialization): self
    {
        if ([] === $specialization) {
            unset($this->specialization);
            return $this;
        }
        $this->specialization = $specialization;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The available versions of the device, e.g., software versions.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    public function getVersion(): array
    {
        return $this->version ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    public function getVersionIterator(): iterable
    {
        if (!isset($this->version)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->version);
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The available versions of the device, e.g., software versions.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $version
     * @return static
     */
    public function addVersion(string|FHIRStringPrimitive|FHIRString $version): self
    {
        if (!($version instanceof FHIRString)) {
            $version = new FHIRString(value: $version);
        }
        if (!isset($this->version)) {
            $this->version = [];
        }
        $this->version[] = $version;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The available versions of the device, e.g., software versions.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString ...$version
     * @return static
     */
    public function setVersion(string|FHIRStringPrimitive|FHIRString ...$version): self
    {
        if ([] === $version) {
            unset($this->version);
            return $this;
        }
        $this->version = [];
        foreach($version as $v) {
            if ($v instanceof FHIRString) {
                $this->version[] = $v;
            } else {
                $this->version[] = new FHIRString(value: $v);
            }
        }
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Safety characteristics of the device.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getSafety(): array
    {
        return $this->safety ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getSafetyIterator(): iterable
    {
        if (!isset($this->safety)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->safety);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Safety characteristics of the device.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $safety
     * @return static
     */
    public function addSafety(FHIRCodeableConcept $safety): self
    {
        if (!isset($this->safety)) {
            $this->safety = [];
        }
        $this->safety[] = $safety;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Safety characteristics of the device.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$safety
     * @return static
     */
    public function setSafety(FHIRCodeableConcept ...$safety): self
    {
        if ([] === $safety) {
            unset($this->safety);
            return $this;
        }
        $this->safety = $safety;
        return $this;
    }

    /**
     * The shelf-life and storage information for a medicinal product item or container
     * can be described using this class.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Shelf Life and storage information.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRProductShelfLife>
     */
    public function getShelfLifeStorage(): array
    {
        return $this->shelfLifeStorage ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRProductShelfLife>
     */
    public function getShelfLifeStorageIterator(): iterable
    {
        if (!isset($this->shelfLifeStorage)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->shelfLifeStorage);
    }

    /**
     * The shelf-life and storage information for a medicinal product item or container
     * can be described using this class.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Shelf Life and storage information.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRProductShelfLife $shelfLifeStorage
     * @return static
     */
    public function addShelfLifeStorage(FHIRProductShelfLife $shelfLifeStorage): self
    {
        if (!isset($this->shelfLifeStorage)) {
            $this->shelfLifeStorage = [];
        }
        $this->shelfLifeStorage[] = $shelfLifeStorage;
        return $this;
    }

    /**
     * The shelf-life and storage information for a medicinal product item or container
     * can be described using this class.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Shelf Life and storage information.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRProductShelfLife ...$shelfLifeStorage
     * @return static
     */
    public function setShelfLifeStorage(FHIRProductShelfLife ...$shelfLifeStorage): self
    {
        if ([] === $shelfLifeStorage) {
            unset($this->shelfLifeStorage);
            return $this;
        }
        $this->shelfLifeStorage = $shelfLifeStorage;
        return $this;
    }

    /**
     * The marketing status describes the date when a medicinal product is actually put
     * on the market or the date as of which it is no longer available.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Dimensions, color etc.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRProdCharacteristic
     */
    public function getPhysicalCharacteristics(): null|FHIRProdCharacteristic
    {
        return $this->physicalCharacteristics ?? null;
    }

    /**
     * The marketing status describes the date when a medicinal product is actually put
     * on the market or the date as of which it is no longer available.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Dimensions, color etc.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRProdCharacteristic $physicalCharacteristics
     * @return static
     */
    public function setPhysicalCharacteristics(null|FHIRProdCharacteristic $physicalCharacteristics): self
    {
        if (null === $physicalCharacteristics) {
            unset($this->physicalCharacteristics);
            return $this;
        }
        $this->physicalCharacteristics = $physicalCharacteristics;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Language code for the human-readable text strings produced by the device (all
     * supported).
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getLanguageCode(): array
    {
        return $this->languageCode ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getLanguageCodeIterator(): iterable
    {
        if (!isset($this->languageCode)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->languageCode);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Language code for the human-readable text strings produced by the device (all
     * supported).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $languageCode
     * @return static
     */
    public function addLanguageCode(FHIRCodeableConcept $languageCode): self
    {
        if (!isset($this->languageCode)) {
            $this->languageCode = [];
        }
        $this->languageCode[] = $languageCode;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Language code for the human-readable text strings produced by the device (all
     * supported).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$languageCode
     * @return static
     */
    public function setLanguageCode(FHIRCodeableConcept ...$languageCode): self
    {
        if ([] === $languageCode) {
            unset($this->languageCode);
            return $this;
        }
        $this->languageCode = $languageCode;
        return $this;
    }

    /**
     * The characteristics, operational status and capabilities of a medical-related
     * component of a medical device.
     *
     * Device capabilities.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceDefinition\FHIRDeviceDefinitionCapability>
     */
    public function getCapability(): array
    {
        return $this->capability ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceDefinition\FHIRDeviceDefinitionCapability>
     */
    public function getCapabilityIterator(): iterable
    {
        if (!isset($this->capability)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->capability);
    }

    /**
     * The characteristics, operational status and capabilities of a medical-related
     * component of a medical device.
     *
     * Device capabilities.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceDefinition\FHIRDeviceDefinitionCapability $capability
     * @return static
     */
    public function addCapability(FHIRDeviceDefinitionCapability $capability): self
    {
        if (!isset($this->capability)) {
            $this->capability = [];
        }
        $this->capability[] = $capability;
        return $this;
    }

    /**
     * The characteristics, operational status and capabilities of a medical-related
     * component of a medical device.
     *
     * Device capabilities.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceDefinition\FHIRDeviceDefinitionCapability ...$capability
     * @return static
     */
    public function setCapability(FHIRDeviceDefinitionCapability ...$capability): self
    {
        if ([] === $capability) {
            unset($this->capability);
            return $this;
        }
        $this->capability = $capability;
        return $this;
    }

    /**
     * The characteristics, operational status and capabilities of a medical-related
     * component of a medical device.
     *
     * The actual configuration settings of a device as it actually operates, e.g.,
     * regulation status, time properties.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceDefinition\FHIRDeviceDefinitionProperty>
     */
    public function getProperty(): array
    {
        return $this->property ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceDefinition\FHIRDeviceDefinitionProperty>
     */
    public function getPropertyIterator(): iterable
    {
        if (!isset($this->property)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->property);
    }

    /**
     * The characteristics, operational status and capabilities of a medical-related
     * component of a medical device.
     *
     * The actual configuration settings of a device as it actually operates, e.g.,
     * regulation status, time properties.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceDefinition\FHIRDeviceDefinitionProperty $property
     * @return static
     */
    public function addProperty(FHIRDeviceDefinitionProperty $property): self
    {
        if (!isset($this->property)) {
            $this->property = [];
        }
        $this->property[] = $property;
        return $this;
    }

    /**
     * The characteristics, operational status and capabilities of a medical-related
     * component of a medical device.
     *
     * The actual configuration settings of a device as it actually operates, e.g.,
     * regulation status, time properties.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceDefinition\FHIRDeviceDefinitionProperty ...$property
     * @return static
     */
    public function setProperty(FHIRDeviceDefinitionProperty ...$property): self
    {
        if ([] === $property) {
            unset($this->property);
            return $this;
        }
        $this->property = $property;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * An organization that is responsible for the provision and ongoing maintenance of
     * the device.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getOwner(): null|FHIRReference
    {
        return $this->owner ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * An organization that is responsible for the provision and ongoing maintenance of
     * the device.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $owner
     * @return static
     */
    public function setOwner(null|FHIRReference $owner): self
    {
        if (null === $owner) {
            unset($this->owner);
            return $this;
        }
        $this->owner = $owner;
        return $this;
    }

    /**
     * Details for all kinds of technology mediated contact points for a person or
     * organization, including telephone, email, etc.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Contact details for an organization or a particular human that is responsible
     * for the device.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactPoint>
     */
    public function getContact(): array
    {
        return $this->contact ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactPoint>
     */
    public function getContactIterator(): iterable
    {
        if (!isset($this->contact)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->contact);
    }

    /**
     * Details for all kinds of technology mediated contact points for a person or
     * organization, including telephone, email, etc.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Contact details for an organization or a particular human that is responsible
     * for the device.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactPoint $contact
     * @return static
     */
    public function addContact(FHIRContactPoint $contact): self
    {
        if (!isset($this->contact)) {
            $this->contact = [];
        }
        $this->contact[] = $contact;
        return $this;
    }

    /**
     * Details for all kinds of technology mediated contact points for a person or
     * organization, including telephone, email, etc.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Contact details for an organization or a particular human that is responsible
     * for the device.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRContactPoint ...$contact
     * @return static
     */
    public function setContact(FHIRContactPoint ...$contact): self
    {
        if ([] === $contact) {
            unset($this->contact);
            return $this;
        }
        $this->contact = $contact;
        return $this;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A network address on which the device may be contacted directly.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    public function getUrl(): null|FHIRUri
    {
        return $this->url ?? null;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A network address on which the device may be contacted directly.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $url
     * @return static
     */
    public function setUrl(null|string|FHIRUriPrimitive|FHIRUri $url): self
    {
        if (null === $url) {
            unset($this->url);
            return $this;
        }
        if (!($url instanceof FHIRUri)) {
            $url = new FHIRUri(value: $url);
        }
        $this->url = $url;
        return $this;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Access to on-line information about the device.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    public function getOnlineInformation(): null|FHIRUri
    {
        return $this->onlineInformation ?? null;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Access to on-line information about the device.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $onlineInformation
     * @return static
     */
    public function setOnlineInformation(null|string|FHIRUriPrimitive|FHIRUri $onlineInformation): self
    {
        if (null === $onlineInformation) {
            unset($this->onlineInformation);
            return $this;
        }
        if (!($onlineInformation instanceof FHIRUri)) {
            $onlineInformation = new FHIRUri(value: $onlineInformation);
        }
        $this->onlineInformation = $onlineInformation;
        return $this;
    }

    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Descriptive information, usage information or implantation information that is
     * not captured in an existing element.
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
     * Descriptive information, usage information or implantation information that is
     * not captured in an existing element.
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
     * Descriptive information, usage information or implantation information that is
     * not captured in an existing element.
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

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The quantity of the device present in the packaging (e.g. the number of devices
     * present in a pack, or the number of devices in the same package of the medicinal
     * product).
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
     * The quantity of the device present in the packaging (e.g. the number of devices
     * present in a pack, or the number of devices in the same package of the medicinal
     * product).
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
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The parent device it can be part of.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getParentDevice(): null|FHIRReference
    {
        return $this->parentDevice ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The parent device it can be part of.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $parentDevice
     * @return static
     */
    public function setParentDevice(null|FHIRReference $parentDevice): self
    {
        if (null === $parentDevice) {
            unset($this->parentDevice);
            return $this;
        }
        $this->parentDevice = $parentDevice;
        return $this;
    }

    /**
     * The characteristics, operational status and capabilities of a medical-related
     * component of a medical device.
     *
     * A substance used to create the material(s) of which the device is made.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceDefinition\FHIRDeviceDefinitionMaterial>
     */
    public function getMaterial(): array
    {
        return $this->material ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceDefinition\FHIRDeviceDefinitionMaterial>
     */
    public function getMaterialIterator(): iterable
    {
        if (!isset($this->material)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->material);
    }

    /**
     * The characteristics, operational status and capabilities of a medical-related
     * component of a medical device.
     *
     * A substance used to create the material(s) of which the device is made.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceDefinition\FHIRDeviceDefinitionMaterial $material
     * @return static
     */
    public function addMaterial(FHIRDeviceDefinitionMaterial $material): self
    {
        if (!isset($this->material)) {
            $this->material = [];
        }
        $this->material[] = $material;
        return $this;
    }

    /**
     * The characteristics, operational status and capabilities of a medical-related
     * component of a medical device.
     *
     * A substance used to create the material(s) of which the device is made.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceDefinition\FHIRDeviceDefinitionMaterial ...$material
     * @return static
     */
    public function setMaterial(FHIRDeviceDefinitionMaterial ...$material): self
    {
        if ([] === $material) {
            unset($this->material);
            return $this;
        }
        $this->material = $material;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRDeviceDefinition $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRDeviceDefinition
     * @throws \Exception
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRDeviceDefinition)) {
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
            } else if (self::FIELD_UDI_DEVICE_IDENTIFIER === $cen) {
                $type->addUdiDeviceIdentifier(FHIRDeviceDefinitionUdiDeviceIdentifier::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MANUFACTURER_STRING === $cen) {
                $type->setManufacturerString(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MANUFACTURER_REFERENCE === $cen) {
                $type->setManufacturerReference(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DEVICE_NAME === $cen) {
                $type->addDeviceName(FHIRDeviceDefinitionDeviceName::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MODEL_NUMBER === $cen) {
                $type->setModelNumber(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TYPE === $cen) {
                $type->setType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SPECIALIZATION === $cen) {
                $type->addSpecialization(FHIRDeviceDefinitionSpecialization::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VERSION === $cen) {
                $type->addVersion(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SAFETY === $cen) {
                $type->addSafety(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SHELF_LIFE_STORAGE === $cen) {
                $type->addShelfLifeStorage(FHIRProductShelfLife::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PHYSICAL_CHARACTERISTICS === $cen) {
                $type->setPhysicalCharacteristics(FHIRProdCharacteristic::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LANGUAGE_CODE === $cen) {
                $type->addLanguageCode(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CAPABILITY === $cen) {
                $type->addCapability(FHIRDeviceDefinitionCapability::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PROPERTY === $cen) {
                $type->addProperty(FHIRDeviceDefinitionProperty::xmlUnserialize($ce, $config));
            } else if (self::FIELD_OWNER === $cen) {
                $type->setOwner(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CONTACT === $cen) {
                $type->addContact(FHIRContactPoint::xmlUnserialize($ce, $config));
            } else if (self::FIELD_URL === $cen) {
                $type->setUrl(FHIRUri::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ONLINE_INFORMATION === $cen) {
                $type->setOnlineInformation(FHIRUri::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NOTE === $cen) {
                $type->addNote(FHIRAnnotation::xmlUnserialize($ce, $config));
            } else if (self::FIELD_QUANTITY === $cen) {
                $type->setQuantity(FHIRQuantity::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PARENT_DEVICE === $cen) {
                $type->setParentDevice(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MATERIAL === $cen) {
                $type->addMaterial(FHIRDeviceDefinitionMaterial::xmlUnserialize($ce, $config));
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
        if (isset($attributes[self::FIELD_MANUFACTURER_STRING])) {
            if (isset($type->manufacturerString)) {
                $type->manufacturerString->setValue((string)$attributes[self::FIELD_MANUFACTURER_STRING]);
            } else {
                $type->setManufacturerString((string)$attributes[self::FIELD_MANUFACTURER_STRING]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_MANUFACTURER_STRING, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_MODEL_NUMBER])) {
            if (isset($type->modelNumber)) {
                $type->modelNumber->setValue((string)$attributes[self::FIELD_MODEL_NUMBER]);
            } else {
                $type->setModelNumber((string)$attributes[self::FIELD_MODEL_NUMBER]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_MODEL_NUMBER, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_URL])) {
            if (isset($type->url)) {
                $type->url->setValue((string)$attributes[self::FIELD_URL]);
            } else {
                $type->setUrl((string)$attributes[self::FIELD_URL]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_URL, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_ONLINE_INFORMATION])) {
            if (isset($type->onlineInformation)) {
                $type->onlineInformation->setValue((string)$attributes[self::FIELD_ONLINE_INFORMATION]);
            } else {
                $type->setOnlineInformation((string)$attributes[self::FIELD_ONLINE_INFORMATION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_ONLINE_INFORMATION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
            $xw->openRootNode('DeviceDefinition', $this->_getSourceXMLNS());
        }
        if (isset($this->manufacturerString) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_MANUFACTURER_STRING]) {
            $xw->writeAttribute(self::FIELD_MANUFACTURER_STRING, $this->manufacturerString->_getValueAsString());
        }
        if (isset($this->modelNumber) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_MODEL_NUMBER]) {
            $xw->writeAttribute(self::FIELD_MODEL_NUMBER, $this->modelNumber->_getValueAsString());
        }
        if (isset($this->url) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_URL]) {
            $xw->writeAttribute(self::FIELD_URL, $this->url->_getValueAsString());
        }
        if (isset($this->onlineInformation) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_ONLINE_INFORMATION]) {
            $xw->writeAttribute(self::FIELD_ONLINE_INFORMATION, $this->onlineInformation->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->identifier)) {
            foreach ($this->identifier as $v) {
                $xw->startElement(self::FIELD_IDENTIFIER);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->udiDeviceIdentifier)) {
            foreach ($this->udiDeviceIdentifier as $v) {
                $xw->startElement(self::FIELD_UDI_DEVICE_IDENTIFIER);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->manufacturerString)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_MANUFACTURER_STRING]
                || $this->manufacturerString->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_MANUFACTURER_STRING);
            $this->manufacturerString->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_MANUFACTURER_STRING]);
            $xw->endElement();
        }
        if (isset($this->manufacturerReference)) {
            $xw->startElement(self::FIELD_MANUFACTURER_REFERENCE);
            $this->manufacturerReference->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->deviceName)) {
            foreach ($this->deviceName as $v) {
                $xw->startElement(self::FIELD_DEVICE_NAME);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->modelNumber)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_MODEL_NUMBER]
                || $this->modelNumber->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_MODEL_NUMBER);
            $this->modelNumber->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_MODEL_NUMBER]);
            $xw->endElement();
        }
        if (isset($this->type)) {
            $xw->startElement(self::FIELD_TYPE);
            $this->type->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->specialization)) {
            foreach ($this->specialization as $v) {
                $xw->startElement(self::FIELD_SPECIALIZATION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->version) && [] !== $this->version) {
            foreach($this->version as $v) {
                $xw->startElement(self::FIELD_VERSION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->safety)) {
            foreach ($this->safety as $v) {
                $xw->startElement(self::FIELD_SAFETY);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->shelfLifeStorage)) {
            foreach ($this->shelfLifeStorage as $v) {
                $xw->startElement(self::FIELD_SHELF_LIFE_STORAGE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->physicalCharacteristics)) {
            $xw->startElement(self::FIELD_PHYSICAL_CHARACTERISTICS);
            $this->physicalCharacteristics->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->languageCode)) {
            foreach ($this->languageCode as $v) {
                $xw->startElement(self::FIELD_LANGUAGE_CODE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->capability)) {
            foreach ($this->capability as $v) {
                $xw->startElement(self::FIELD_CAPABILITY);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->property)) {
            foreach ($this->property as $v) {
                $xw->startElement(self::FIELD_PROPERTY);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->owner)) {
            $xw->startElement(self::FIELD_OWNER);
            $this->owner->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->contact)) {
            foreach ($this->contact as $v) {
                $xw->startElement(self::FIELD_CONTACT);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->url)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_URL]
                || $this->url->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_URL);
            $this->url->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_URL]);
            $xw->endElement();
        }
        if (isset($this->onlineInformation)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_ONLINE_INFORMATION]
                || $this->onlineInformation->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_ONLINE_INFORMATION);
            $this->onlineInformation->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_ONLINE_INFORMATION]);
            $xw->endElement();
        }
        if (isset($this->note)) {
            foreach ($this->note as $v) {
                $xw->startElement(self::FIELD_NOTE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->quantity)) {
            $xw->startElement(self::FIELD_QUANTITY);
            $this->quantity->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->parentDevice)) {
            $xw->startElement(self::FIELD_PARENT_DEVICE);
            $this->parentDevice->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->material)) {
            foreach ($this->material as $v) {
                $xw->startElement(self::FIELD_MATERIAL);
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
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRDeviceDefinition $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRDeviceDefinition
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
        } else if (!($type instanceof FHIRDeviceDefinition)) {
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
        if (isset($decoded->udiDeviceIdentifier) || property_exists($decoded, self::FIELD_UDI_DEVICE_IDENTIFIER)) {
            if (is_object($decoded->udiDeviceIdentifier)) {
                $vals = [$decoded->udiDeviceIdentifier];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_UDI_DEVICE_IDENTIFIER, true);
            } else {
                $vals = $decoded->udiDeviceIdentifier;
            }
            foreach($vals as $v) {
                $type->addUdiDeviceIdentifier(FHIRDeviceDefinitionUdiDeviceIdentifier::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->manufacturerString)
            || isset($decoded->_manufacturerString)
            || property_exists($decoded, self::FIELD_MANUFACTURER_STRING)
            || property_exists($decoded, self::FIELD_MANUFACTURER_STRING_EXT)) {
            $v = $decoded->_manufacturerString ?? new \stdClass();
            $v->value = $decoded->manufacturerString ?? null;
            $type->setManufacturerString(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->manufacturerReference) || property_exists($decoded, self::FIELD_MANUFACTURER_REFERENCE)) {
            if (is_array($decoded->manufacturerReference)) {
                $type->setManufacturerReference(FHIRReference::jsonUnserialize(reset($decoded->manufacturerReference), $config));
            } else {
                $type->setManufacturerReference(FHIRReference::jsonUnserialize($decoded->manufacturerReference, $config));
            }
        }
        if (isset($decoded->deviceName) || property_exists($decoded, self::FIELD_DEVICE_NAME)) {
            if (is_object($decoded->deviceName)) {
                $vals = [$decoded->deviceName];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_DEVICE_NAME, true);
            } else {
                $vals = $decoded->deviceName;
            }
            foreach($vals as $v) {
                $type->addDeviceName(FHIRDeviceDefinitionDeviceName::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->modelNumber)
            || isset($decoded->_modelNumber)
            || property_exists($decoded, self::FIELD_MODEL_NUMBER)
            || property_exists($decoded, self::FIELD_MODEL_NUMBER_EXT)) {
            $v = $decoded->_modelNumber ?? new \stdClass();
            $v->value = $decoded->modelNumber ?? null;
            $type->setModelNumber(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->type) || property_exists($decoded, self::FIELD_TYPE)) {
            if (is_array($decoded->type)) {
                $type->setType(FHIRCodeableConcept::jsonUnserialize(reset($decoded->type), $config));
            } else {
                $type->setType(FHIRCodeableConcept::jsonUnserialize($decoded->type, $config));
            }
        }
        if (isset($decoded->specialization) || property_exists($decoded, self::FIELD_SPECIALIZATION)) {
            if (is_object($decoded->specialization)) {
                $vals = [$decoded->specialization];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_SPECIALIZATION, true);
            } else {
                $vals = $decoded->specialization;
            }
            foreach($vals as $v) {
                $type->addSpecialization(FHIRDeviceDefinitionSpecialization::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->version)
            || isset($decoded->_version)
            || property_exists($decoded, self::FIELD_VERSION)
            || property_exists($decoded, self::FIELD_VERSION_EXT)) {
            $vals = (array)($decoded->version ?? []);
            $exts = (array)($decoded->_version ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addVersion(FHIRString::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->safety) || property_exists($decoded, self::FIELD_SAFETY)) {
            if (is_object($decoded->safety)) {
                $vals = [$decoded->safety];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_SAFETY, true);
            } else {
                $vals = $decoded->safety;
            }
            foreach($vals as $v) {
                $type->addSafety(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->shelfLifeStorage) || property_exists($decoded, self::FIELD_SHELF_LIFE_STORAGE)) {
            if (is_object($decoded->shelfLifeStorage)) {
                $vals = [$decoded->shelfLifeStorage];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_SHELF_LIFE_STORAGE, true);
            } else {
                $vals = $decoded->shelfLifeStorage;
            }
            foreach($vals as $v) {
                $type->addShelfLifeStorage(FHIRProductShelfLife::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->physicalCharacteristics) || property_exists($decoded, self::FIELD_PHYSICAL_CHARACTERISTICS)) {
            if (is_array($decoded->physicalCharacteristics)) {
                $type->setPhysicalCharacteristics(FHIRProdCharacteristic::jsonUnserialize(reset($decoded->physicalCharacteristics), $config));
            } else {
                $type->setPhysicalCharacteristics(FHIRProdCharacteristic::jsonUnserialize($decoded->physicalCharacteristics, $config));
            }
        }
        if (isset($decoded->languageCode) || property_exists($decoded, self::FIELD_LANGUAGE_CODE)) {
            if (is_object($decoded->languageCode)) {
                $vals = [$decoded->languageCode];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_LANGUAGE_CODE, true);
            } else {
                $vals = $decoded->languageCode;
            }
            foreach($vals as $v) {
                $type->addLanguageCode(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->capability) || property_exists($decoded, self::FIELD_CAPABILITY)) {
            if (is_object($decoded->capability)) {
                $vals = [$decoded->capability];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_CAPABILITY, true);
            } else {
                $vals = $decoded->capability;
            }
            foreach($vals as $v) {
                $type->addCapability(FHIRDeviceDefinitionCapability::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->property) || property_exists($decoded, self::FIELD_PROPERTY)) {
            if (is_object($decoded->property)) {
                $vals = [$decoded->property];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PROPERTY, true);
            } else {
                $vals = $decoded->property;
            }
            foreach($vals as $v) {
                $type->addProperty(FHIRDeviceDefinitionProperty::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->owner) || property_exists($decoded, self::FIELD_OWNER)) {
            if (is_array($decoded->owner)) {
                $type->setOwner(FHIRReference::jsonUnserialize(reset($decoded->owner), $config));
            } else {
                $type->setOwner(FHIRReference::jsonUnserialize($decoded->owner, $config));
            }
        }
        if (isset($decoded->contact) || property_exists($decoded, self::FIELD_CONTACT)) {
            if (is_object($decoded->contact)) {
                $vals = [$decoded->contact];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_CONTACT, true);
            } else {
                $vals = $decoded->contact;
            }
            foreach($vals as $v) {
                $type->addContact(FHIRContactPoint::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->url)
            || isset($decoded->_url)
            || property_exists($decoded, self::FIELD_URL)
            || property_exists($decoded, self::FIELD_URL_EXT)) {
            $v = $decoded->_url ?? new \stdClass();
            $v->value = $decoded->url ?? null;
            $type->setUrl(FHIRUri::jsonUnserialize($v, $config));
        }
        if (isset($decoded->onlineInformation)
            || isset($decoded->_onlineInformation)
            || property_exists($decoded, self::FIELD_ONLINE_INFORMATION)
            || property_exists($decoded, self::FIELD_ONLINE_INFORMATION_EXT)) {
            $v = $decoded->_onlineInformation ?? new \stdClass();
            $v->value = $decoded->onlineInformation ?? null;
            $type->setOnlineInformation(FHIRUri::jsonUnserialize($v, $config));
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
        if (isset($decoded->quantity) || property_exists($decoded, self::FIELD_QUANTITY)) {
            if (is_array($decoded->quantity)) {
                $type->setQuantity(FHIRQuantity::jsonUnserialize(reset($decoded->quantity), $config));
            } else {
                $type->setQuantity(FHIRQuantity::jsonUnserialize($decoded->quantity, $config));
            }
        }
        if (isset($decoded->parentDevice) || property_exists($decoded, self::FIELD_PARENT_DEVICE)) {
            if (is_array($decoded->parentDevice)) {
                $type->setParentDevice(FHIRReference::jsonUnserialize(reset($decoded->parentDevice), $config));
            } else {
                $type->setParentDevice(FHIRReference::jsonUnserialize($decoded->parentDevice, $config));
            }
        }
        if (isset($decoded->material) || property_exists($decoded, self::FIELD_MATERIAL)) {
            if (is_object($decoded->material)) {
                $vals = [$decoded->material];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_MATERIAL, true);
            } else {
                $vals = $decoded->material;
            }
            foreach($vals as $v) {
                $type->addMaterial(FHIRDeviceDefinitionMaterial::jsonUnserialize($v, $config));
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
        if (isset($this->udiDeviceIdentifier) && [] !== $this->udiDeviceIdentifier) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_UDI_DEVICE_IDENTIFIER) && 1 === count($this->udiDeviceIdentifier)) {
                $out->udiDeviceIdentifier = $this->udiDeviceIdentifier[0];
            } else {
                $out->udiDeviceIdentifier = $this->udiDeviceIdentifier;
            }
        }
        if (isset($this->manufacturerString)) {
            if (null !== ($val = $this->manufacturerString->getValue())) {
                $out->manufacturerString = $val;
            }
            if ($this->manufacturerString->_nonValueFieldDefined()) {
                $ext = $this->manufacturerString->jsonSerialize();
                unset($ext->value);
                $out->_manufacturerString = $ext;
            }
        }
        if (isset($this->manufacturerReference)) {
            $out->manufacturerReference = $this->manufacturerReference;
        }
        if (isset($this->deviceName) && [] !== $this->deviceName) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_DEVICE_NAME) && 1 === count($this->deviceName)) {
                $out->deviceName = $this->deviceName[0];
            } else {
                $out->deviceName = $this->deviceName;
            }
        }
        if (isset($this->modelNumber)) {
            if (null !== ($val = $this->modelNumber->getValue())) {
                $out->modelNumber = $val;
            }
            if ($this->modelNumber->_nonValueFieldDefined()) {
                $ext = $this->modelNumber->jsonSerialize();
                unset($ext->value);
                $out->_modelNumber = $ext;
            }
        }
        if (isset($this->type)) {
            $out->type = $this->type;
        }
        if (isset($this->specialization) && [] !== $this->specialization) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_SPECIALIZATION) && 1 === count($this->specialization)) {
                $out->specialization = $this->specialization[0];
            } else {
                $out->specialization = $this->specialization;
            }
        }
        if (isset($this->version) && [] !== $this->version) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->version as $v) {
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
                $out->version = $vals;
            }
            if ($hasExts) {
                $out->_version = $exts;
            }
        }
        if (isset($this->safety) && [] !== $this->safety) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_SAFETY) && 1 === count($this->safety)) {
                $out->safety = $this->safety[0];
            } else {
                $out->safety = $this->safety;
            }
        }
        if (isset($this->shelfLifeStorage) && [] !== $this->shelfLifeStorage) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_SHELF_LIFE_STORAGE) && 1 === count($this->shelfLifeStorage)) {
                $out->shelfLifeStorage = $this->shelfLifeStorage[0];
            } else {
                $out->shelfLifeStorage = $this->shelfLifeStorage;
            }
        }
        if (isset($this->physicalCharacteristics)) {
            $out->physicalCharacteristics = $this->physicalCharacteristics;
        }
        if (isset($this->languageCode) && [] !== $this->languageCode) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_LANGUAGE_CODE) && 1 === count($this->languageCode)) {
                $out->languageCode = $this->languageCode[0];
            } else {
                $out->languageCode = $this->languageCode;
            }
        }
        if (isset($this->capability) && [] !== $this->capability) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_CAPABILITY) && 1 === count($this->capability)) {
                $out->capability = $this->capability[0];
            } else {
                $out->capability = $this->capability;
            }
        }
        if (isset($this->property) && [] !== $this->property) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PROPERTY) && 1 === count($this->property)) {
                $out->property = $this->property[0];
            } else {
                $out->property = $this->property;
            }
        }
        if (isset($this->owner)) {
            $out->owner = $this->owner;
        }
        if (isset($this->contact) && [] !== $this->contact) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_CONTACT) && 1 === count($this->contact)) {
                $out->contact = $this->contact[0];
            } else {
                $out->contact = $this->contact;
            }
        }
        if (isset($this->url)) {
            if (null !== ($val = $this->url->getValue())) {
                $out->url = $val;
            }
            if ($this->url->_nonValueFieldDefined()) {
                $ext = $this->url->jsonSerialize();
                unset($ext->value);
                $out->_url = $ext;
            }
        }
        if (isset($this->onlineInformation)) {
            if (null !== ($val = $this->onlineInformation->getValue())) {
                $out->onlineInformation = $val;
            }
            if ($this->onlineInformation->_nonValueFieldDefined()) {
                $ext = $this->onlineInformation->jsonSerialize();
                unset($ext->value);
                $out->_onlineInformation = $ext;
            }
        }
        if (isset($this->note) && [] !== $this->note) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_NOTE) && 1 === count($this->note)) {
                $out->note = $this->note[0];
            } else {
                $out->note = $this->note;
            }
        }
        if (isset($this->quantity)) {
            $out->quantity = $this->quantity;
        }
        if (isset($this->parentDevice)) {
            $out->parentDevice = $this->parentDevice;
        }
        if (isset($this->material) && [] !== $this->material) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_MATERIAL) && 1 === count($this->material)) {
                $out->material = $this->material[0];
            } else {
                $out->material = $this->material;
            }
        }
        $out->resourceType = $this->_getResourceType();
        return $out;
    }
}
