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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductContraindication\FHIRMedicinalProductContraindicationOtherTherapy;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRPopulation;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
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
 * The clinical particulars - indications, contraindications etc. of a medicinal
 * product, including for regulatory purposes.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRMedicinalProductContraindication extends FHIRDomainResource implements VersionContainedTypeInterface
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_MEDICINAL_PRODUCT_CONTRAINDICATION;

    /* class_default.php:56 */
    public const FIELD_SUBJECT = 'subject';
    public const FIELD_DISEASE = 'disease';
    public const FIELD_DISEASE_STATUS = 'diseaseStatus';
    public const FIELD_COMORBIDITY = 'comorbidity';
    public const FIELD_THERAPEUTIC_INDICATION = 'therapeuticIndication';
    public const FIELD_OTHER_THERAPY = 'otherTherapy';
    public const FIELD_POPULATION = 'population';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
    ];

    /* class_default.php:112 */
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The medication for which this is an indication.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $subject;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The disease, symptom or procedure for the contraindication.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $disease;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The status of the disease or symptom for the contraindication.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $diseaseStatus;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A comorbidity (concurrent condition) or coinfection.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $comorbidity;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Information about the use of the medicinal product in relation to other
     * therapies as part of the indication.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $therapeuticIndication;
    /**
     * The clinical particulars - indications, contraindications etc. of a medicinal
     * product, including for regulatory purposes.
     *
     * Information about the use of the medicinal product in relation to other
     * therapies described as part of the indication.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductContraindication\FHIRMedicinalProductContraindicationOtherTherapy>
     */
    #[FHIRMedicinalProductContraindicationOtherTherapy]
    protected array $otherTherapy;
    /**
     * A populatioof people with some set of grouping criteria.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The population group to which this applies.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRPopulation>
     */
    #[FHIRPopulation]
    protected array $population;

    /* constructor.php:61 */
    /**
     * FHIRMedicinalProductContraindication Constructor
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $id
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $meta
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $implicitRules
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative $text
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer>|iterable<\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface> $contained
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $subject
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $disease
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $diseaseStatus
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $comorbidity
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $therapeuticIndication
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductContraindication\FHIRMedicinalProductContraindicationOtherTherapy> $otherTherapy
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRPopulation> $population
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
                                null|iterable $subject = null,
                                null|FHIRCodeableConcept $disease = null,
                                null|FHIRCodeableConcept $diseaseStatus = null,
                                null|iterable $comorbidity = null,
                                null|iterable $therapeuticIndication = null,
                                null|iterable $otherTherapy = null,
                                null|iterable $population = null,
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
        if (null !== $subject) {
            $this->setSubject(...$subject);
        }
        if (null !== $disease) {
            $this->setDisease($disease);
        }
        if (null !== $diseaseStatus) {
            $this->setDiseaseStatus($diseaseStatus);
        }
        if (null !== $comorbidity) {
            $this->setComorbidity(...$comorbidity);
        }
        if (null !== $therapeuticIndication) {
            $this->setTherapeuticIndication(...$therapeuticIndication);
        }
        if (null !== $otherTherapy) {
            $this->setOtherTherapy(...$otherTherapy);
        }
        if (null !== $population) {
            $this->setPopulation(...$population);
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
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The medication for which this is an indication.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getSubject(): array
    {
        return $this->subject ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getSubjectIterator(): iterable
    {
        if (!isset($this->subject)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->subject);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The medication for which this is an indication.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $subject
     * @return static
     */
    public function addSubject(FHIRReference $subject): self
    {
        if (!isset($this->subject)) {
            $this->subject = [];
        }
        $this->subject[] = $subject;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The medication for which this is an indication.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$subject
     * @return static
     */
    public function setSubject(FHIRReference ...$subject): self
    {
        if ([] === $subject) {
            unset($this->subject);
            return $this;
        }
        $this->subject = $subject;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The disease, symptom or procedure for the contraindication.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getDisease(): null|FHIRCodeableConcept
    {
        return $this->disease ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The disease, symptom or procedure for the contraindication.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $disease
     * @return static
     */
    public function setDisease(null|FHIRCodeableConcept $disease): self
    {
        if (null === $disease) {
            unset($this->disease);
            return $this;
        }
        $this->disease = $disease;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The status of the disease or symptom for the contraindication.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getDiseaseStatus(): null|FHIRCodeableConcept
    {
        return $this->diseaseStatus ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The status of the disease or symptom for the contraindication.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $diseaseStatus
     * @return static
     */
    public function setDiseaseStatus(null|FHIRCodeableConcept $diseaseStatus): self
    {
        if (null === $diseaseStatus) {
            unset($this->diseaseStatus);
            return $this;
        }
        $this->diseaseStatus = $diseaseStatus;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A comorbidity (concurrent condition) or coinfection.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getComorbidity(): array
    {
        return $this->comorbidity ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getComorbidityIterator(): iterable
    {
        if (!isset($this->comorbidity)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->comorbidity);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A comorbidity (concurrent condition) or coinfection.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $comorbidity
     * @return static
     */
    public function addComorbidity(FHIRCodeableConcept $comorbidity): self
    {
        if (!isset($this->comorbidity)) {
            $this->comorbidity = [];
        }
        $this->comorbidity[] = $comorbidity;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A comorbidity (concurrent condition) or coinfection.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$comorbidity
     * @return static
     */
    public function setComorbidity(FHIRCodeableConcept ...$comorbidity): self
    {
        if ([] === $comorbidity) {
            unset($this->comorbidity);
            return $this;
        }
        $this->comorbidity = $comorbidity;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Information about the use of the medicinal product in relation to other
     * therapies as part of the indication.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getTherapeuticIndication(): array
    {
        return $this->therapeuticIndication ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getTherapeuticIndicationIterator(): iterable
    {
        if (!isset($this->therapeuticIndication)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->therapeuticIndication);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Information about the use of the medicinal product in relation to other
     * therapies as part of the indication.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $therapeuticIndication
     * @return static
     */
    public function addTherapeuticIndication(FHIRReference $therapeuticIndication): self
    {
        if (!isset($this->therapeuticIndication)) {
            $this->therapeuticIndication = [];
        }
        $this->therapeuticIndication[] = $therapeuticIndication;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Information about the use of the medicinal product in relation to other
     * therapies as part of the indication.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$therapeuticIndication
     * @return static
     */
    public function setTherapeuticIndication(FHIRReference ...$therapeuticIndication): self
    {
        if ([] === $therapeuticIndication) {
            unset($this->therapeuticIndication);
            return $this;
        }
        $this->therapeuticIndication = $therapeuticIndication;
        return $this;
    }

    /**
     * The clinical particulars - indications, contraindications etc. of a medicinal
     * product, including for regulatory purposes.
     *
     * Information about the use of the medicinal product in relation to other
     * therapies described as part of the indication.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductContraindication\FHIRMedicinalProductContraindicationOtherTherapy>
     */
    public function getOtherTherapy(): array
    {
        return $this->otherTherapy ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductContraindication\FHIRMedicinalProductContraindicationOtherTherapy>
     */
    public function getOtherTherapyIterator(): iterable
    {
        if (!isset($this->otherTherapy)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->otherTherapy);
    }

    /**
     * The clinical particulars - indications, contraindications etc. of a medicinal
     * product, including for regulatory purposes.
     *
     * Information about the use of the medicinal product in relation to other
     * therapies described as part of the indication.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductContraindication\FHIRMedicinalProductContraindicationOtherTherapy $otherTherapy
     * @return static
     */
    public function addOtherTherapy(FHIRMedicinalProductContraindicationOtherTherapy $otherTherapy): self
    {
        if (!isset($this->otherTherapy)) {
            $this->otherTherapy = [];
        }
        $this->otherTherapy[] = $otherTherapy;
        return $this;
    }

    /**
     * The clinical particulars - indications, contraindications etc. of a medicinal
     * product, including for regulatory purposes.
     *
     * Information about the use of the medicinal product in relation to other
     * therapies described as part of the indication.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductContraindication\FHIRMedicinalProductContraindicationOtherTherapy ...$otherTherapy
     * @return static
     */
    public function setOtherTherapy(FHIRMedicinalProductContraindicationOtherTherapy ...$otherTherapy): self
    {
        if ([] === $otherTherapy) {
            unset($this->otherTherapy);
            return $this;
        }
        $this->otherTherapy = $otherTherapy;
        return $this;
    }

    /**
     * A populatioof people with some set of grouping criteria.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The population group to which this applies.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRPopulation>
     */
    public function getPopulation(): array
    {
        return $this->population ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRPopulation>
     */
    public function getPopulationIterator(): iterable
    {
        if (!isset($this->population)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->population);
    }

    /**
     * A populatioof people with some set of grouping criteria.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The population group to which this applies.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRPopulation $population
     * @return static
     */
    public function addPopulation(FHIRPopulation $population): self
    {
        if (!isset($this->population)) {
            $this->population = [];
        }
        $this->population[] = $population;
        return $this;
    }

    /**
     * A populatioof people with some set of grouping criteria.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The population group to which this applies.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRPopulation ...$population
     * @return static
     */
    public function setPopulation(FHIRPopulation ...$population): self
    {
        if ([] === $population) {
            unset($this->population);
            return $this;
        }
        $this->population = $population;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicinalProductContraindication $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicinalProductContraindication
     * @throws \Exception
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRMedicinalProductContraindication)) {
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
            } else if (self::FIELD_SUBJECT === $cen) {
                $type->addSubject(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DISEASE === $cen) {
                $type->setDisease(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DISEASE_STATUS === $cen) {
                $type->setDiseaseStatus(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_COMORBIDITY === $cen) {
                $type->addComorbidity(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_THERAPEUTIC_INDICATION === $cen) {
                $type->addTherapeuticIndication(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_OTHER_THERAPY === $cen) {
                $type->addOtherTherapy(FHIRMedicinalProductContraindicationOtherTherapy::xmlUnserialize($ce, $config));
            } else if (self::FIELD_POPULATION === $cen) {
                $type->addPopulation(FHIRPopulation::xmlUnserialize($ce, $config));
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
            $xw->openRootNode('MedicinalProductContraindication', $this->_getSourceXMLNS());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->subject)) {
            foreach ($this->subject as $v) {
                $xw->startElement(self::FIELD_SUBJECT);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->disease)) {
            $xw->startElement(self::FIELD_DISEASE);
            $this->disease->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->diseaseStatus)) {
            $xw->startElement(self::FIELD_DISEASE_STATUS);
            $this->diseaseStatus->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->comorbidity)) {
            foreach ($this->comorbidity as $v) {
                $xw->startElement(self::FIELD_COMORBIDITY);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->therapeuticIndication)) {
            foreach ($this->therapeuticIndication as $v) {
                $xw->startElement(self::FIELD_THERAPEUTIC_INDICATION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->otherTherapy)) {
            foreach ($this->otherTherapy as $v) {
                $xw->startElement(self::FIELD_OTHER_THERAPY);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->population)) {
            foreach ($this->population as $v) {
                $xw->startElement(self::FIELD_POPULATION);
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
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicinalProductContraindication $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicinalProductContraindication
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
        } else if (!($type instanceof FHIRMedicinalProductContraindication)) {
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
        if (isset($decoded->subject) || property_exists($decoded, self::FIELD_SUBJECT)) {
            if (is_object($decoded->subject)) {
                $vals = [$decoded->subject];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_SUBJECT, true);
            } else {
                $vals = $decoded->subject;
            }
            foreach($vals as $v) {
                $type->addSubject(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->disease) || property_exists($decoded, self::FIELD_DISEASE)) {
            if (is_array($decoded->disease)) {
                $type->setDisease(FHIRCodeableConcept::jsonUnserialize(reset($decoded->disease), $config));
            } else {
                $type->setDisease(FHIRCodeableConcept::jsonUnserialize($decoded->disease, $config));
            }
        }
        if (isset($decoded->diseaseStatus) || property_exists($decoded, self::FIELD_DISEASE_STATUS)) {
            if (is_array($decoded->diseaseStatus)) {
                $type->setDiseaseStatus(FHIRCodeableConcept::jsonUnserialize(reset($decoded->diseaseStatus), $config));
            } else {
                $type->setDiseaseStatus(FHIRCodeableConcept::jsonUnserialize($decoded->diseaseStatus, $config));
            }
        }
        if (isset($decoded->comorbidity) || property_exists($decoded, self::FIELD_COMORBIDITY)) {
            if (is_object($decoded->comorbidity)) {
                $vals = [$decoded->comorbidity];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_COMORBIDITY, true);
            } else {
                $vals = $decoded->comorbidity;
            }
            foreach($vals as $v) {
                $type->addComorbidity(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->therapeuticIndication) || property_exists($decoded, self::FIELD_THERAPEUTIC_INDICATION)) {
            if (is_object($decoded->therapeuticIndication)) {
                $vals = [$decoded->therapeuticIndication];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_THERAPEUTIC_INDICATION, true);
            } else {
                $vals = $decoded->therapeuticIndication;
            }
            foreach($vals as $v) {
                $type->addTherapeuticIndication(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->otherTherapy) || property_exists($decoded, self::FIELD_OTHER_THERAPY)) {
            if (is_object($decoded->otherTherapy)) {
                $vals = [$decoded->otherTherapy];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_OTHER_THERAPY, true);
            } else {
                $vals = $decoded->otherTherapy;
            }
            foreach($vals as $v) {
                $type->addOtherTherapy(FHIRMedicinalProductContraindicationOtherTherapy::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->population) || property_exists($decoded, self::FIELD_POPULATION)) {
            if (is_object($decoded->population)) {
                $vals = [$decoded->population];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_POPULATION, true);
            } else {
                $vals = $decoded->population;
            }
            foreach($vals as $v) {
                $type->addPopulation(FHIRPopulation::jsonUnserialize($v, $config));
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
        if (isset($this->subject) && [] !== $this->subject) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_SUBJECT) && 1 === count($this->subject)) {
                $out->subject = $this->subject[0];
            } else {
                $out->subject = $this->subject;
            }
        }
        if (isset($this->disease)) {
            $out->disease = $this->disease;
        }
        if (isset($this->diseaseStatus)) {
            $out->diseaseStatus = $this->diseaseStatus;
        }
        if (isset($this->comorbidity) && [] !== $this->comorbidity) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_COMORBIDITY) && 1 === count($this->comorbidity)) {
                $out->comorbidity = $this->comorbidity[0];
            } else {
                $out->comorbidity = $this->comorbidity;
            }
        }
        if (isset($this->therapeuticIndication) && [] !== $this->therapeuticIndication) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_THERAPEUTIC_INDICATION) && 1 === count($this->therapeuticIndication)) {
                $out->therapeuticIndication = $this->therapeuticIndication[0];
            } else {
                $out->therapeuticIndication = $this->therapeuticIndication;
            }
        }
        if (isset($this->otherTherapy) && [] !== $this->otherTherapy) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_OTHER_THERAPY) && 1 === count($this->otherTherapy)) {
                $out->otherTherapy = $this->otherTherapy[0];
            } else {
                $out->otherTherapy = $this->otherTherapy;
            }
        }
        if (isset($this->population) && [] !== $this->population) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_POPULATION) && 1 === count($this->population)) {
                $out->population = $this->population[0];
            } else {
                $out->population = $this->population;
            }
        }
        $out->resourceType = $this->_getResourceType();
        return $out;
    }
}
