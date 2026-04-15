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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMarketingStatus;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct\FHIRMedicinalProductManufacturingBusinessOperation;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct\FHIRMedicinalProductName;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct\FHIRMedicinalProductSpecialDesignation;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative;
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
 * Detailed definition of a medicinal product, typically for uses other than direct
 * patient care (e.g. regulatory use).
 * If the element is present, it must have either a \@value, an \@id, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRMedicinalProduct extends FHIRDomainResource implements VersionContainedTypeInterface
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_MEDICINAL_PRODUCT;

    /* class_default.php:56 */
    public const FIELD_IDENTIFIER = 'identifier';
    public const FIELD_TYPE = 'type';
    public const FIELD_DOMAIN = 'domain';
    public const FIELD_COMBINED_PHARMACEUTICAL_DOSE_FORM = 'combinedPharmaceuticalDoseForm';
    public const FIELD_LEGAL_STATUS_OF_SUPPLY = 'legalStatusOfSupply';
    public const FIELD_ADDITIONAL_MONITORING_INDICATOR = 'additionalMonitoringIndicator';
    public const FIELD_SPECIAL_MEASURES = 'specialMeasures';
    public const FIELD_SPECIAL_MEASURES_EXT = '_specialMeasures';
    public const FIELD_PAEDIATRIC_USE_INDICATOR = 'paediatricUseIndicator';
    public const FIELD_PRODUCT_CLASSIFICATION = 'productClassification';
    public const FIELD_MARKETING_STATUS = 'marketingStatus';
    public const FIELD_PHARMACEUTICAL_PRODUCT = 'pharmaceuticalProduct';
    public const FIELD_PACKAGED_MEDICINAL_PRODUCT = 'packagedMedicinalProduct';
    public const FIELD_ATTACHED_DOCUMENT = 'attachedDocument';
    public const FIELD_MASTER_FILE = 'masterFile';
    public const FIELD_CONTACT = 'contact';
    public const FIELD_CLINICAL_TRIAL = 'clinicalTrial';
    public const FIELD_NAME = 'name';
    public const FIELD_CROSS_REFERENCE = 'crossReference';
    public const FIELD_MANUFACTURING_BUSINESS_OPERATION = 'manufacturingBusinessOperation';
    public const FIELD_SPECIAL_DESIGNATION = 'specialDesignation';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_NAME => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
    ];

    /* class_default.php:112 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Business identifier for this product. Could be an MPID.
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
     * Regulatory type, e.g. Investigational or Authorized.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $type;
    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If this medicine applies to human or veterinary uses.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding
     */
    #[FHIRCoding]
    protected FHIRCoding $domain;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The dose form for a single part product, or combined form of a multiple part
     * product.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $combinedPharmaceuticalDoseForm;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The legal status of supply of the medicinal product as classified by the
     * regulator.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $legalStatusOfSupply;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Whether the Medicinal Product is subject to additional monitoring for regulatory
     * reasons.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $additionalMonitoringIndicator;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether the Medicinal Product is subject to special measures for regulatory
     * reasons.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    #[FHIRString]
    protected array $specialMeasures;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If authorised for use in children.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $paediatricUseIndicator;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Allows the product to be classified by various systems.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $productClassification;
    /**
     * The marketing status describes the date when a medicinal product is actually put
     * on the market or the date as of which it is no longer available.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Marketing status of the medicinal product, in contrast to marketing
     * authorizaton.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMarketingStatus>
     */
    #[FHIRMarketingStatus]
    protected array $marketingStatus;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Pharmaceutical aspects of product.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $pharmaceuticalProduct;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Package representation for the product.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $packagedMedicinalProduct;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Supporting documentation, typically for regulatory submission.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $attachedDocument;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A master file for to the medicinal product (e.g. Pharmacovigilance System Master
     * File).
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $masterFile;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A product specific contact, person (in a role), or an organization.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $contact;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Clinical trials or studies that this product is involved in.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $clinicalTrial;
    /**
     * Detailed definition of a medicinal product, typically for uses other than direct
     * patient care (e.g. regulatory use).
     *
     * The product's name, including full name and possibly coded parts.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct\FHIRMedicinalProductName>
     */
    #[FHIRMedicinalProductName]
    protected array $name;
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reference to another product, e.g. for linking authorised to investigational
     * product.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    #[FHIRIdentifier]
    protected array $crossReference;
    /**
     * Detailed definition of a medicinal product, typically for uses other than direct
     * patient care (e.g. regulatory use).
     *
     * An operation applied to the product, for manufacturing or adminsitrative
     * purpose.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct\FHIRMedicinalProductManufacturingBusinessOperation>
     */
    #[FHIRMedicinalProductManufacturingBusinessOperation]
    protected array $manufacturingBusinessOperation;
    /**
     * Detailed definition of a medicinal product, typically for uses other than direct
     * patient care (e.g. regulatory use).
     *
     * Indicates if the medicinal product has an orphan designation for the treatment
     * of a rare disease.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct\FHIRMedicinalProductSpecialDesignation>
     */
    #[FHIRMedicinalProductSpecialDesignation]
    protected array $specialDesignation;

    /* constructor.php:61 */
    /**
     * FHIRMedicinalProduct Constructor
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
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding $domain
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $combinedPharmaceuticalDoseForm
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $legalStatusOfSupply
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $additionalMonitoringIndicator
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString> $specialMeasures
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $paediatricUseIndicator
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $productClassification
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMarketingStatus> $marketingStatus
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $pharmaceuticalProduct
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $packagedMedicinalProduct
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $attachedDocument
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $masterFile
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $contact
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $clinicalTrial
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct\FHIRMedicinalProductName> $name
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier> $crossReference
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct\FHIRMedicinalProductManufacturingBusinessOperation> $manufacturingBusinessOperation
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct\FHIRMedicinalProductSpecialDesignation> $specialDesignation
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
                                null|FHIRCoding $domain = null,
                                null|FHIRCodeableConcept $combinedPharmaceuticalDoseForm = null,
                                null|FHIRCodeableConcept $legalStatusOfSupply = null,
                                null|FHIRCodeableConcept $additionalMonitoringIndicator = null,
                                null|iterable $specialMeasures = null,
                                null|FHIRCodeableConcept $paediatricUseIndicator = null,
                                null|iterable $productClassification = null,
                                null|iterable $marketingStatus = null,
                                null|iterable $pharmaceuticalProduct = null,
                                null|iterable $packagedMedicinalProduct = null,
                                null|iterable $attachedDocument = null,
                                null|iterable $masterFile = null,
                                null|iterable $contact = null,
                                null|iterable $clinicalTrial = null,
                                null|iterable $name = null,
                                null|iterable $crossReference = null,
                                null|iterable $manufacturingBusinessOperation = null,
                                null|iterable $specialDesignation = null,
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
        if (null !== $domain) {
            $this->setDomain($domain);
        }
        if (null !== $combinedPharmaceuticalDoseForm) {
            $this->setCombinedPharmaceuticalDoseForm($combinedPharmaceuticalDoseForm);
        }
        if (null !== $legalStatusOfSupply) {
            $this->setLegalStatusOfSupply($legalStatusOfSupply);
        }
        if (null !== $additionalMonitoringIndicator) {
            $this->setAdditionalMonitoringIndicator($additionalMonitoringIndicator);
        }
        if (null !== $specialMeasures) {
            $this->setSpecialMeasures(...$specialMeasures);
        }
        if (null !== $paediatricUseIndicator) {
            $this->setPaediatricUseIndicator($paediatricUseIndicator);
        }
        if (null !== $productClassification) {
            $this->setProductClassification(...$productClassification);
        }
        if (null !== $marketingStatus) {
            $this->setMarketingStatus(...$marketingStatus);
        }
        if (null !== $pharmaceuticalProduct) {
            $this->setPharmaceuticalProduct(...$pharmaceuticalProduct);
        }
        if (null !== $packagedMedicinalProduct) {
            $this->setPackagedMedicinalProduct(...$packagedMedicinalProduct);
        }
        if (null !== $attachedDocument) {
            $this->setAttachedDocument(...$attachedDocument);
        }
        if (null !== $masterFile) {
            $this->setMasterFile(...$masterFile);
        }
        if (null !== $contact) {
            $this->setContact(...$contact);
        }
        if (null !== $clinicalTrial) {
            $this->setClinicalTrial(...$clinicalTrial);
        }
        if (null !== $name) {
            $this->setName(...$name);
        }
        if (null !== $crossReference) {
            $this->setCrossReference(...$crossReference);
        }
        if (null !== $manufacturingBusinessOperation) {
            $this->setManufacturingBusinessOperation(...$manufacturingBusinessOperation);
        }
        if (null !== $specialDesignation) {
            $this->setSpecialDesignation(...$specialDesignation);
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
     * Business identifier for this product. Could be an MPID.
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
     * Business identifier for this product. Could be an MPID.
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
     * Business identifier for this product. Could be an MPID.
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
     * Regulatory type, e.g. Investigational or Authorized.
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
     * Regulatory type, e.g. Investigational or Authorized.
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
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If this medicine applies to human or veterinary uses.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding
     */
    public function getDomain(): null|FHIRCoding
    {
        return $this->domain ?? null;
    }

    /**
     * A reference to a code defined by a terminology system.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If this medicine applies to human or veterinary uses.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCoding $domain
     * @return static
     */
    public function setDomain(null|FHIRCoding $domain): self
    {
        if (null === $domain) {
            unset($this->domain);
            return $this;
        }
        $this->domain = $domain;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The dose form for a single part product, or combined form of a multiple part
     * product.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getCombinedPharmaceuticalDoseForm(): null|FHIRCodeableConcept
    {
        return $this->combinedPharmaceuticalDoseForm ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The dose form for a single part product, or combined form of a multiple part
     * product.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $combinedPharmaceuticalDoseForm
     * @return static
     */
    public function setCombinedPharmaceuticalDoseForm(null|FHIRCodeableConcept $combinedPharmaceuticalDoseForm): self
    {
        if (null === $combinedPharmaceuticalDoseForm) {
            unset($this->combinedPharmaceuticalDoseForm);
            return $this;
        }
        $this->combinedPharmaceuticalDoseForm = $combinedPharmaceuticalDoseForm;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The legal status of supply of the medicinal product as classified by the
     * regulator.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getLegalStatusOfSupply(): null|FHIRCodeableConcept
    {
        return $this->legalStatusOfSupply ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The legal status of supply of the medicinal product as classified by the
     * regulator.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $legalStatusOfSupply
     * @return static
     */
    public function setLegalStatusOfSupply(null|FHIRCodeableConcept $legalStatusOfSupply): self
    {
        if (null === $legalStatusOfSupply) {
            unset($this->legalStatusOfSupply);
            return $this;
        }
        $this->legalStatusOfSupply = $legalStatusOfSupply;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Whether the Medicinal Product is subject to additional monitoring for regulatory
     * reasons.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getAdditionalMonitoringIndicator(): null|FHIRCodeableConcept
    {
        return $this->additionalMonitoringIndicator ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Whether the Medicinal Product is subject to additional monitoring for regulatory
     * reasons.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $additionalMonitoringIndicator
     * @return static
     */
    public function setAdditionalMonitoringIndicator(null|FHIRCodeableConcept $additionalMonitoringIndicator): self
    {
        if (null === $additionalMonitoringIndicator) {
            unset($this->additionalMonitoringIndicator);
            return $this;
        }
        $this->additionalMonitoringIndicator = $additionalMonitoringIndicator;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether the Medicinal Product is subject to special measures for regulatory
     * reasons.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    public function getSpecialMeasures(): array
    {
        return $this->specialMeasures ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    public function getSpecialMeasuresIterator(): iterable
    {
        if (!isset($this->specialMeasures)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->specialMeasures);
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether the Medicinal Product is subject to special measures for regulatory
     * reasons.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $specialMeasures
     * @return static
     */
    public function addSpecialMeasures(string|FHIRStringPrimitive|FHIRString $specialMeasures): self
    {
        if (!($specialMeasures instanceof FHIRString)) {
            $specialMeasures = new FHIRString(value: $specialMeasures);
        }
        if (!isset($this->specialMeasures)) {
            $this->specialMeasures = [];
        }
        $this->specialMeasures[] = $specialMeasures;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether the Medicinal Product is subject to special measures for regulatory
     * reasons.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString ...$specialMeasures
     * @return static
     */
    public function setSpecialMeasures(string|FHIRStringPrimitive|FHIRString ...$specialMeasures): self
    {
        if ([] === $specialMeasures) {
            unset($this->specialMeasures);
            return $this;
        }
        $this->specialMeasures = [];
        foreach($specialMeasures as $v) {
            if ($v instanceof FHIRString) {
                $this->specialMeasures[] = $v;
            } else {
                $this->specialMeasures[] = new FHIRString(value: $v);
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
     * If authorised for use in children.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getPaediatricUseIndicator(): null|FHIRCodeableConcept
    {
        return $this->paediatricUseIndicator ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * If authorised for use in children.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $paediatricUseIndicator
     * @return static
     */
    public function setPaediatricUseIndicator(null|FHIRCodeableConcept $paediatricUseIndicator): self
    {
        if (null === $paediatricUseIndicator) {
            unset($this->paediatricUseIndicator);
            return $this;
        }
        $this->paediatricUseIndicator = $paediatricUseIndicator;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Allows the product to be classified by various systems.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getProductClassification(): array
    {
        return $this->productClassification ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getProductClassificationIterator(): iterable
    {
        if (!isset($this->productClassification)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->productClassification);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Allows the product to be classified by various systems.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $productClassification
     * @return static
     */
    public function addProductClassification(FHIRCodeableConcept $productClassification): self
    {
        if (!isset($this->productClassification)) {
            $this->productClassification = [];
        }
        $this->productClassification[] = $productClassification;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Allows the product to be classified by various systems.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$productClassification
     * @return static
     */
    public function setProductClassification(FHIRCodeableConcept ...$productClassification): self
    {
        if ([] === $productClassification) {
            unset($this->productClassification);
            return $this;
        }
        $this->productClassification = $productClassification;
        return $this;
    }

    /**
     * The marketing status describes the date when a medicinal product is actually put
     * on the market or the date as of which it is no longer available.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Marketing status of the medicinal product, in contrast to marketing
     * authorizaton.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMarketingStatus>
     */
    public function getMarketingStatus(): array
    {
        return $this->marketingStatus ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMarketingStatus>
     */
    public function getMarketingStatusIterator(): iterable
    {
        if (!isset($this->marketingStatus)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->marketingStatus);
    }

    /**
     * The marketing status describes the date when a medicinal product is actually put
     * on the market or the date as of which it is no longer available.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Marketing status of the medicinal product, in contrast to marketing
     * authorizaton.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMarketingStatus $marketingStatus
     * @return static
     */
    public function addMarketingStatus(FHIRMarketingStatus $marketingStatus): self
    {
        if (!isset($this->marketingStatus)) {
            $this->marketingStatus = [];
        }
        $this->marketingStatus[] = $marketingStatus;
        return $this;
    }

    /**
     * The marketing status describes the date when a medicinal product is actually put
     * on the market or the date as of which it is no longer available.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Marketing status of the medicinal product, in contrast to marketing
     * authorizaton.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMarketingStatus ...$marketingStatus
     * @return static
     */
    public function setMarketingStatus(FHIRMarketingStatus ...$marketingStatus): self
    {
        if ([] === $marketingStatus) {
            unset($this->marketingStatus);
            return $this;
        }
        $this->marketingStatus = $marketingStatus;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Pharmaceutical aspects of product.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getPharmaceuticalProduct(): array
    {
        return $this->pharmaceuticalProduct ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getPharmaceuticalProductIterator(): iterable
    {
        if (!isset($this->pharmaceuticalProduct)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->pharmaceuticalProduct);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Pharmaceutical aspects of product.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $pharmaceuticalProduct
     * @return static
     */
    public function addPharmaceuticalProduct(FHIRReference $pharmaceuticalProduct): self
    {
        if (!isset($this->pharmaceuticalProduct)) {
            $this->pharmaceuticalProduct = [];
        }
        $this->pharmaceuticalProduct[] = $pharmaceuticalProduct;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Pharmaceutical aspects of product.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$pharmaceuticalProduct
     * @return static
     */
    public function setPharmaceuticalProduct(FHIRReference ...$pharmaceuticalProduct): self
    {
        if ([] === $pharmaceuticalProduct) {
            unset($this->pharmaceuticalProduct);
            return $this;
        }
        $this->pharmaceuticalProduct = $pharmaceuticalProduct;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Package representation for the product.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getPackagedMedicinalProduct(): array
    {
        return $this->packagedMedicinalProduct ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getPackagedMedicinalProductIterator(): iterable
    {
        if (!isset($this->packagedMedicinalProduct)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->packagedMedicinalProduct);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Package representation for the product.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $packagedMedicinalProduct
     * @return static
     */
    public function addPackagedMedicinalProduct(FHIRReference $packagedMedicinalProduct): self
    {
        if (!isset($this->packagedMedicinalProduct)) {
            $this->packagedMedicinalProduct = [];
        }
        $this->packagedMedicinalProduct[] = $packagedMedicinalProduct;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Package representation for the product.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$packagedMedicinalProduct
     * @return static
     */
    public function setPackagedMedicinalProduct(FHIRReference ...$packagedMedicinalProduct): self
    {
        if ([] === $packagedMedicinalProduct) {
            unset($this->packagedMedicinalProduct);
            return $this;
        }
        $this->packagedMedicinalProduct = $packagedMedicinalProduct;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Supporting documentation, typically for regulatory submission.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getAttachedDocument(): array
    {
        return $this->attachedDocument ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getAttachedDocumentIterator(): iterable
    {
        if (!isset($this->attachedDocument)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->attachedDocument);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Supporting documentation, typically for regulatory submission.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $attachedDocument
     * @return static
     */
    public function addAttachedDocument(FHIRReference $attachedDocument): self
    {
        if (!isset($this->attachedDocument)) {
            $this->attachedDocument = [];
        }
        $this->attachedDocument[] = $attachedDocument;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Supporting documentation, typically for regulatory submission.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$attachedDocument
     * @return static
     */
    public function setAttachedDocument(FHIRReference ...$attachedDocument): self
    {
        if ([] === $attachedDocument) {
            unset($this->attachedDocument);
            return $this;
        }
        $this->attachedDocument = $attachedDocument;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A master file for to the medicinal product (e.g. Pharmacovigilance System Master
     * File).
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getMasterFile(): array
    {
        return $this->masterFile ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getMasterFileIterator(): iterable
    {
        if (!isset($this->masterFile)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->masterFile);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A master file for to the medicinal product (e.g. Pharmacovigilance System Master
     * File).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $masterFile
     * @return static
     */
    public function addMasterFile(FHIRReference $masterFile): self
    {
        if (!isset($this->masterFile)) {
            $this->masterFile = [];
        }
        $this->masterFile[] = $masterFile;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A master file for to the medicinal product (e.g. Pharmacovigilance System Master
     * File).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$masterFile
     * @return static
     */
    public function setMasterFile(FHIRReference ...$masterFile): self
    {
        if ([] === $masterFile) {
            unset($this->masterFile);
            return $this;
        }
        $this->masterFile = $masterFile;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A product specific contact, person (in a role), or an organization.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getContact(): array
    {
        return $this->contact ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getContactIterator(): iterable
    {
        if (!isset($this->contact)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->contact);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A product specific contact, person (in a role), or an organization.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $contact
     * @return static
     */
    public function addContact(FHIRReference $contact): self
    {
        if (!isset($this->contact)) {
            $this->contact = [];
        }
        $this->contact[] = $contact;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A product specific contact, person (in a role), or an organization.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$contact
     * @return static
     */
    public function setContact(FHIRReference ...$contact): self
    {
        if ([] === $contact) {
            unset($this->contact);
            return $this;
        }
        $this->contact = $contact;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Clinical trials or studies that this product is involved in.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getClinicalTrial(): array
    {
        return $this->clinicalTrial ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getClinicalTrialIterator(): iterable
    {
        if (!isset($this->clinicalTrial)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->clinicalTrial);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Clinical trials or studies that this product is involved in.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $clinicalTrial
     * @return static
     */
    public function addClinicalTrial(FHIRReference $clinicalTrial): self
    {
        if (!isset($this->clinicalTrial)) {
            $this->clinicalTrial = [];
        }
        $this->clinicalTrial[] = $clinicalTrial;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Clinical trials or studies that this product is involved in.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$clinicalTrial
     * @return static
     */
    public function setClinicalTrial(FHIRReference ...$clinicalTrial): self
    {
        if ([] === $clinicalTrial) {
            unset($this->clinicalTrial);
            return $this;
        }
        $this->clinicalTrial = $clinicalTrial;
        return $this;
    }

    /**
     * Detailed definition of a medicinal product, typically for uses other than direct
     * patient care (e.g. regulatory use).
     *
     * The product's name, including full name and possibly coded parts.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct\FHIRMedicinalProductName>
     */
    public function getName(): array
    {
        return $this->name ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct\FHIRMedicinalProductName>
     */
    public function getNameIterator(): iterable
    {
        if (!isset($this->name)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->name);
    }

    /**
     * Detailed definition of a medicinal product, typically for uses other than direct
     * patient care (e.g. regulatory use).
     *
     * The product's name, including full name and possibly coded parts.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct\FHIRMedicinalProductName $name
     * @return static
     */
    public function addName(FHIRMedicinalProductName $name): self
    {
        if (!isset($this->name)) {
            $this->name = [];
        }
        $this->name[] = $name;
        return $this;
    }

    /**
     * Detailed definition of a medicinal product, typically for uses other than direct
     * patient care (e.g. regulatory use).
     *
     * The product's name, including full name and possibly coded parts.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct\FHIRMedicinalProductName ...$name
     * @return static
     */
    public function setName(FHIRMedicinalProductName ...$name): self
    {
        if ([] === $name) {
            unset($this->name);
            return $this;
        }
        $this->name = $name;
        return $this;
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reference to another product, e.g. for linking authorised to investigational
     * product.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    public function getCrossReference(): array
    {
        return $this->crossReference ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    public function getCrossReferenceIterator(): iterable
    {
        if (!isset($this->crossReference)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->crossReference);
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reference to another product, e.g. for linking authorised to investigational
     * product.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $crossReference
     * @return static
     */
    public function addCrossReference(FHIRIdentifier $crossReference): self
    {
        if (!isset($this->crossReference)) {
            $this->crossReference = [];
        }
        $this->crossReference[] = $crossReference;
        return $this;
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reference to another product, e.g. for linking authorised to investigational
     * product.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier ...$crossReference
     * @return static
     */
    public function setCrossReference(FHIRIdentifier ...$crossReference): self
    {
        if ([] === $crossReference) {
            unset($this->crossReference);
            return $this;
        }
        $this->crossReference = $crossReference;
        return $this;
    }

    /**
     * Detailed definition of a medicinal product, typically for uses other than direct
     * patient care (e.g. regulatory use).
     *
     * An operation applied to the product, for manufacturing or adminsitrative
     * purpose.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct\FHIRMedicinalProductManufacturingBusinessOperation>
     */
    public function getManufacturingBusinessOperation(): array
    {
        return $this->manufacturingBusinessOperation ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct\FHIRMedicinalProductManufacturingBusinessOperation>
     */
    public function getManufacturingBusinessOperationIterator(): iterable
    {
        if (!isset($this->manufacturingBusinessOperation)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->manufacturingBusinessOperation);
    }

    /**
     * Detailed definition of a medicinal product, typically for uses other than direct
     * patient care (e.g. regulatory use).
     *
     * An operation applied to the product, for manufacturing or adminsitrative
     * purpose.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct\FHIRMedicinalProductManufacturingBusinessOperation $manufacturingBusinessOperation
     * @return static
     */
    public function addManufacturingBusinessOperation(FHIRMedicinalProductManufacturingBusinessOperation $manufacturingBusinessOperation): self
    {
        if (!isset($this->manufacturingBusinessOperation)) {
            $this->manufacturingBusinessOperation = [];
        }
        $this->manufacturingBusinessOperation[] = $manufacturingBusinessOperation;
        return $this;
    }

    /**
     * Detailed definition of a medicinal product, typically for uses other than direct
     * patient care (e.g. regulatory use).
     *
     * An operation applied to the product, for manufacturing or adminsitrative
     * purpose.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct\FHIRMedicinalProductManufacturingBusinessOperation ...$manufacturingBusinessOperation
     * @return static
     */
    public function setManufacturingBusinessOperation(FHIRMedicinalProductManufacturingBusinessOperation ...$manufacturingBusinessOperation): self
    {
        if ([] === $manufacturingBusinessOperation) {
            unset($this->manufacturingBusinessOperation);
            return $this;
        }
        $this->manufacturingBusinessOperation = $manufacturingBusinessOperation;
        return $this;
    }

    /**
     * Detailed definition of a medicinal product, typically for uses other than direct
     * patient care (e.g. regulatory use).
     *
     * Indicates if the medicinal product has an orphan designation for the treatment
     * of a rare disease.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct\FHIRMedicinalProductSpecialDesignation>
     */
    public function getSpecialDesignation(): array
    {
        return $this->specialDesignation ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct\FHIRMedicinalProductSpecialDesignation>
     */
    public function getSpecialDesignationIterator(): iterable
    {
        if (!isset($this->specialDesignation)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->specialDesignation);
    }

    /**
     * Detailed definition of a medicinal product, typically for uses other than direct
     * patient care (e.g. regulatory use).
     *
     * Indicates if the medicinal product has an orphan designation for the treatment
     * of a rare disease.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct\FHIRMedicinalProductSpecialDesignation $specialDesignation
     * @return static
     */
    public function addSpecialDesignation(FHIRMedicinalProductSpecialDesignation $specialDesignation): self
    {
        if (!isset($this->specialDesignation)) {
            $this->specialDesignation = [];
        }
        $this->specialDesignation[] = $specialDesignation;
        return $this;
    }

    /**
     * Detailed definition of a medicinal product, typically for uses other than direct
     * patient care (e.g. regulatory use).
     *
     * Indicates if the medicinal product has an orphan designation for the treatment
     * of a rare disease.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProduct\FHIRMedicinalProductSpecialDesignation ...$specialDesignation
     * @return static
     */
    public function setSpecialDesignation(FHIRMedicinalProductSpecialDesignation ...$specialDesignation): self
    {
        if ([] === $specialDesignation) {
            unset($this->specialDesignation);
            return $this;
        }
        $this->specialDesignation = $specialDesignation;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicinalProduct $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicinalProduct
     * @throws \Exception
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRMedicinalProduct)) {
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
            } else if (self::FIELD_DOMAIN === $cen) {
                $type->setDomain(FHIRCoding::xmlUnserialize($ce, $config));
            } else if (self::FIELD_COMBINED_PHARMACEUTICAL_DOSE_FORM === $cen) {
                $type->setCombinedPharmaceuticalDoseForm(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LEGAL_STATUS_OF_SUPPLY === $cen) {
                $type->setLegalStatusOfSupply(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ADDITIONAL_MONITORING_INDICATOR === $cen) {
                $type->setAdditionalMonitoringIndicator(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SPECIAL_MEASURES === $cen) {
                $type->addSpecialMeasures(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PAEDIATRIC_USE_INDICATOR === $cen) {
                $type->setPaediatricUseIndicator(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PRODUCT_CLASSIFICATION === $cen) {
                $type->addProductClassification(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MARKETING_STATUS === $cen) {
                $type->addMarketingStatus(FHIRMarketingStatus::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PHARMACEUTICAL_PRODUCT === $cen) {
                $type->addPharmaceuticalProduct(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PACKAGED_MEDICINAL_PRODUCT === $cen) {
                $type->addPackagedMedicinalProduct(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ATTACHED_DOCUMENT === $cen) {
                $type->addAttachedDocument(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MASTER_FILE === $cen) {
                $type->addMasterFile(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CONTACT === $cen) {
                $type->addContact(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CLINICAL_TRIAL === $cen) {
                $type->addClinicalTrial(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NAME === $cen) {
                $type->addName(FHIRMedicinalProductName::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CROSS_REFERENCE === $cen) {
                $type->addCrossReference(FHIRIdentifier::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MANUFACTURING_BUSINESS_OPERATION === $cen) {
                $type->addManufacturingBusinessOperation(FHIRMedicinalProductManufacturingBusinessOperation::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SPECIAL_DESIGNATION === $cen) {
                $type->addSpecialDesignation(FHIRMedicinalProductSpecialDesignation::xmlUnserialize($ce, $config));
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
            $xw->openRootNode('MedicinalProduct', $this->_getSourceXMLNS());
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
        if (isset($this->domain)) {
            $xw->startElement(self::FIELD_DOMAIN);
            $this->domain->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->combinedPharmaceuticalDoseForm)) {
            $xw->startElement(self::FIELD_COMBINED_PHARMACEUTICAL_DOSE_FORM);
            $this->combinedPharmaceuticalDoseForm->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->legalStatusOfSupply)) {
            $xw->startElement(self::FIELD_LEGAL_STATUS_OF_SUPPLY);
            $this->legalStatusOfSupply->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->additionalMonitoringIndicator)) {
            $xw->startElement(self::FIELD_ADDITIONAL_MONITORING_INDICATOR);
            $this->additionalMonitoringIndicator->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->specialMeasures) && [] !== $this->specialMeasures) {
            foreach($this->specialMeasures as $v) {
                $xw->startElement(self::FIELD_SPECIAL_MEASURES);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->paediatricUseIndicator)) {
            $xw->startElement(self::FIELD_PAEDIATRIC_USE_INDICATOR);
            $this->paediatricUseIndicator->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->productClassification)) {
            foreach ($this->productClassification as $v) {
                $xw->startElement(self::FIELD_PRODUCT_CLASSIFICATION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->marketingStatus)) {
            foreach ($this->marketingStatus as $v) {
                $xw->startElement(self::FIELD_MARKETING_STATUS);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->pharmaceuticalProduct)) {
            foreach ($this->pharmaceuticalProduct as $v) {
                $xw->startElement(self::FIELD_PHARMACEUTICAL_PRODUCT);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->packagedMedicinalProduct)) {
            foreach ($this->packagedMedicinalProduct as $v) {
                $xw->startElement(self::FIELD_PACKAGED_MEDICINAL_PRODUCT);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->attachedDocument)) {
            foreach ($this->attachedDocument as $v) {
                $xw->startElement(self::FIELD_ATTACHED_DOCUMENT);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->masterFile)) {
            foreach ($this->masterFile as $v) {
                $xw->startElement(self::FIELD_MASTER_FILE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->contact)) {
            foreach ($this->contact as $v) {
                $xw->startElement(self::FIELD_CONTACT);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->clinicalTrial)) {
            foreach ($this->clinicalTrial as $v) {
                $xw->startElement(self::FIELD_CLINICAL_TRIAL);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->name)) {
            foreach ($this->name as $v) {
                $xw->startElement(self::FIELD_NAME);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->crossReference)) {
            foreach ($this->crossReference as $v) {
                $xw->startElement(self::FIELD_CROSS_REFERENCE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->manufacturingBusinessOperation)) {
            foreach ($this->manufacturingBusinessOperation as $v) {
                $xw->startElement(self::FIELD_MANUFACTURING_BUSINESS_OPERATION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->specialDesignation)) {
            foreach ($this->specialDesignation as $v) {
                $xw->startElement(self::FIELD_SPECIAL_DESIGNATION);
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
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicinalProduct $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicinalProduct
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
        } else if (!($type instanceof FHIRMedicinalProduct)) {
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
        if (isset($decoded->domain) || property_exists($decoded, self::FIELD_DOMAIN)) {
            if (is_array($decoded->domain)) {
                $type->setDomain(FHIRCoding::jsonUnserialize(reset($decoded->domain), $config));
            } else {
                $type->setDomain(FHIRCoding::jsonUnserialize($decoded->domain, $config));
            }
        }
        if (isset($decoded->combinedPharmaceuticalDoseForm) || property_exists($decoded, self::FIELD_COMBINED_PHARMACEUTICAL_DOSE_FORM)) {
            if (is_array($decoded->combinedPharmaceuticalDoseForm)) {
                $type->setCombinedPharmaceuticalDoseForm(FHIRCodeableConcept::jsonUnserialize(reset($decoded->combinedPharmaceuticalDoseForm), $config));
            } else {
                $type->setCombinedPharmaceuticalDoseForm(FHIRCodeableConcept::jsonUnserialize($decoded->combinedPharmaceuticalDoseForm, $config));
            }
        }
        if (isset($decoded->legalStatusOfSupply) || property_exists($decoded, self::FIELD_LEGAL_STATUS_OF_SUPPLY)) {
            if (is_array($decoded->legalStatusOfSupply)) {
                $type->setLegalStatusOfSupply(FHIRCodeableConcept::jsonUnserialize(reset($decoded->legalStatusOfSupply), $config));
            } else {
                $type->setLegalStatusOfSupply(FHIRCodeableConcept::jsonUnserialize($decoded->legalStatusOfSupply, $config));
            }
        }
        if (isset($decoded->additionalMonitoringIndicator) || property_exists($decoded, self::FIELD_ADDITIONAL_MONITORING_INDICATOR)) {
            if (is_array($decoded->additionalMonitoringIndicator)) {
                $type->setAdditionalMonitoringIndicator(FHIRCodeableConcept::jsonUnserialize(reset($decoded->additionalMonitoringIndicator), $config));
            } else {
                $type->setAdditionalMonitoringIndicator(FHIRCodeableConcept::jsonUnserialize($decoded->additionalMonitoringIndicator, $config));
            }
        }
        if (isset($decoded->specialMeasures)
            || isset($decoded->_specialMeasures)
            || property_exists($decoded, self::FIELD_SPECIAL_MEASURES)
            || property_exists($decoded, self::FIELD_SPECIAL_MEASURES_EXT)) {
            $vals = (array)($decoded->specialMeasures ?? []);
            $exts = (array)($decoded->_specialMeasures ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addSpecialMeasures(FHIRString::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->paediatricUseIndicator) || property_exists($decoded, self::FIELD_PAEDIATRIC_USE_INDICATOR)) {
            if (is_array($decoded->paediatricUseIndicator)) {
                $type->setPaediatricUseIndicator(FHIRCodeableConcept::jsonUnserialize(reset($decoded->paediatricUseIndicator), $config));
            } else {
                $type->setPaediatricUseIndicator(FHIRCodeableConcept::jsonUnserialize($decoded->paediatricUseIndicator, $config));
            }
        }
        if (isset($decoded->productClassification) || property_exists($decoded, self::FIELD_PRODUCT_CLASSIFICATION)) {
            if (is_object($decoded->productClassification)) {
                $vals = [$decoded->productClassification];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PRODUCT_CLASSIFICATION, true);
            } else {
                $vals = $decoded->productClassification;
            }
            foreach($vals as $v) {
                $type->addProductClassification(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->marketingStatus) || property_exists($decoded, self::FIELD_MARKETING_STATUS)) {
            if (is_object($decoded->marketingStatus)) {
                $vals = [$decoded->marketingStatus];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_MARKETING_STATUS, true);
            } else {
                $vals = $decoded->marketingStatus;
            }
            foreach($vals as $v) {
                $type->addMarketingStatus(FHIRMarketingStatus::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->pharmaceuticalProduct) || property_exists($decoded, self::FIELD_PHARMACEUTICAL_PRODUCT)) {
            if (is_object($decoded->pharmaceuticalProduct)) {
                $vals = [$decoded->pharmaceuticalProduct];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PHARMACEUTICAL_PRODUCT, true);
            } else {
                $vals = $decoded->pharmaceuticalProduct;
            }
            foreach($vals as $v) {
                $type->addPharmaceuticalProduct(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->packagedMedicinalProduct) || property_exists($decoded, self::FIELD_PACKAGED_MEDICINAL_PRODUCT)) {
            if (is_object($decoded->packagedMedicinalProduct)) {
                $vals = [$decoded->packagedMedicinalProduct];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PACKAGED_MEDICINAL_PRODUCT, true);
            } else {
                $vals = $decoded->packagedMedicinalProduct;
            }
            foreach($vals as $v) {
                $type->addPackagedMedicinalProduct(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->attachedDocument) || property_exists($decoded, self::FIELD_ATTACHED_DOCUMENT)) {
            if (is_object($decoded->attachedDocument)) {
                $vals = [$decoded->attachedDocument];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_ATTACHED_DOCUMENT, true);
            } else {
                $vals = $decoded->attachedDocument;
            }
            foreach($vals as $v) {
                $type->addAttachedDocument(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->masterFile) || property_exists($decoded, self::FIELD_MASTER_FILE)) {
            if (is_object($decoded->masterFile)) {
                $vals = [$decoded->masterFile];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_MASTER_FILE, true);
            } else {
                $vals = $decoded->masterFile;
            }
            foreach($vals as $v) {
                $type->addMasterFile(FHIRReference::jsonUnserialize($v, $config));
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
                $type->addContact(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->clinicalTrial) || property_exists($decoded, self::FIELD_CLINICAL_TRIAL)) {
            if (is_object($decoded->clinicalTrial)) {
                $vals = [$decoded->clinicalTrial];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_CLINICAL_TRIAL, true);
            } else {
                $vals = $decoded->clinicalTrial;
            }
            foreach($vals as $v) {
                $type->addClinicalTrial(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->name) || property_exists($decoded, self::FIELD_NAME)) {
            if (is_object($decoded->name)) {
                $vals = [$decoded->name];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_NAME, true);
            } else {
                $vals = $decoded->name;
            }
            foreach($vals as $v) {
                $type->addName(FHIRMedicinalProductName::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->crossReference) || property_exists($decoded, self::FIELD_CROSS_REFERENCE)) {
            if (is_object($decoded->crossReference)) {
                $vals = [$decoded->crossReference];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_CROSS_REFERENCE, true);
            } else {
                $vals = $decoded->crossReference;
            }
            foreach($vals as $v) {
                $type->addCrossReference(FHIRIdentifier::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->manufacturingBusinessOperation) || property_exists($decoded, self::FIELD_MANUFACTURING_BUSINESS_OPERATION)) {
            if (is_object($decoded->manufacturingBusinessOperation)) {
                $vals = [$decoded->manufacturingBusinessOperation];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_MANUFACTURING_BUSINESS_OPERATION, true);
            } else {
                $vals = $decoded->manufacturingBusinessOperation;
            }
            foreach($vals as $v) {
                $type->addManufacturingBusinessOperation(FHIRMedicinalProductManufacturingBusinessOperation::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->specialDesignation) || property_exists($decoded, self::FIELD_SPECIAL_DESIGNATION)) {
            if (is_object($decoded->specialDesignation)) {
                $vals = [$decoded->specialDesignation];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_SPECIAL_DESIGNATION, true);
            } else {
                $vals = $decoded->specialDesignation;
            }
            foreach($vals as $v) {
                $type->addSpecialDesignation(FHIRMedicinalProductSpecialDesignation::jsonUnserialize($v, $config));
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
        if (isset($this->domain)) {
            $out->domain = $this->domain;
        }
        if (isset($this->combinedPharmaceuticalDoseForm)) {
            $out->combinedPharmaceuticalDoseForm = $this->combinedPharmaceuticalDoseForm;
        }
        if (isset($this->legalStatusOfSupply)) {
            $out->legalStatusOfSupply = $this->legalStatusOfSupply;
        }
        if (isset($this->additionalMonitoringIndicator)) {
            $out->additionalMonitoringIndicator = $this->additionalMonitoringIndicator;
        }
        if (isset($this->specialMeasures) && [] !== $this->specialMeasures) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->specialMeasures as $v) {
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
                $out->specialMeasures = $vals;
            }
            if ($hasExts) {
                $out->_specialMeasures = $exts;
            }
        }
        if (isset($this->paediatricUseIndicator)) {
            $out->paediatricUseIndicator = $this->paediatricUseIndicator;
        }
        if (isset($this->productClassification) && [] !== $this->productClassification) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PRODUCT_CLASSIFICATION) && 1 === count($this->productClassification)) {
                $out->productClassification = $this->productClassification[0];
            } else {
                $out->productClassification = $this->productClassification;
            }
        }
        if (isset($this->marketingStatus) && [] !== $this->marketingStatus) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_MARKETING_STATUS) && 1 === count($this->marketingStatus)) {
                $out->marketingStatus = $this->marketingStatus[0];
            } else {
                $out->marketingStatus = $this->marketingStatus;
            }
        }
        if (isset($this->pharmaceuticalProduct) && [] !== $this->pharmaceuticalProduct) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PHARMACEUTICAL_PRODUCT) && 1 === count($this->pharmaceuticalProduct)) {
                $out->pharmaceuticalProduct = $this->pharmaceuticalProduct[0];
            } else {
                $out->pharmaceuticalProduct = $this->pharmaceuticalProduct;
            }
        }
        if (isset($this->packagedMedicinalProduct) && [] !== $this->packagedMedicinalProduct) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PACKAGED_MEDICINAL_PRODUCT) && 1 === count($this->packagedMedicinalProduct)) {
                $out->packagedMedicinalProduct = $this->packagedMedicinalProduct[0];
            } else {
                $out->packagedMedicinalProduct = $this->packagedMedicinalProduct;
            }
        }
        if (isset($this->attachedDocument) && [] !== $this->attachedDocument) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_ATTACHED_DOCUMENT) && 1 === count($this->attachedDocument)) {
                $out->attachedDocument = $this->attachedDocument[0];
            } else {
                $out->attachedDocument = $this->attachedDocument;
            }
        }
        if (isset($this->masterFile) && [] !== $this->masterFile) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_MASTER_FILE) && 1 === count($this->masterFile)) {
                $out->masterFile = $this->masterFile[0];
            } else {
                $out->masterFile = $this->masterFile;
            }
        }
        if (isset($this->contact) && [] !== $this->contact) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_CONTACT) && 1 === count($this->contact)) {
                $out->contact = $this->contact[0];
            } else {
                $out->contact = $this->contact;
            }
        }
        if (isset($this->clinicalTrial) && [] !== $this->clinicalTrial) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_CLINICAL_TRIAL) && 1 === count($this->clinicalTrial)) {
                $out->clinicalTrial = $this->clinicalTrial[0];
            } else {
                $out->clinicalTrial = $this->clinicalTrial;
            }
        }
        if (isset($this->name) && [] !== $this->name) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_NAME) && 1 === count($this->name)) {
                $out->name = $this->name[0];
            } else {
                $out->name = $this->name;
            }
        }
        if (isset($this->crossReference) && [] !== $this->crossReference) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_CROSS_REFERENCE) && 1 === count($this->crossReference)) {
                $out->crossReference = $this->crossReference[0];
            } else {
                $out->crossReference = $this->crossReference;
            }
        }
        if (isset($this->manufacturingBusinessOperation) && [] !== $this->manufacturingBusinessOperation) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_MANUFACTURING_BUSINESS_OPERATION) && 1 === count($this->manufacturingBusinessOperation)) {
                $out->manufacturingBusinessOperation = $this->manufacturingBusinessOperation[0];
            } else {
                $out->manufacturingBusinessOperation = $this->manufacturingBusinessOperation;
            }
        }
        if (isset($this->specialDesignation) && [] !== $this->specialDesignation) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_SPECIAL_DESIGNATION) && 1 === count($this->specialDesignation)) {
                $out->specialDesignation = $this->specialDesignation[0];
            } else {
                $out->specialDesignation = $this->specialDesignation;
            }
        }
        $out->resourceType = $this->_getResourceType();
        return $out;
    }
}
