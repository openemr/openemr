<?php

namespace OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;

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

use OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRSubstanceProtein\FHIRSubstanceProteinSubunit;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCode;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRInteger;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUri;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\R4\PHPFHIRConstants;
use OpenEMR\FHIR\R4\PHPFHIRContainedTypeInterface;
use OpenEMR\FHIR\R4\PHPFHIRTypeInterface;
use OpenEMR\FHIR\R4\PHPFHIRTypeMap;

/**
 * A SubstanceProtein is defined as a single unit of a linear amino acid sequence,
 * or a combination of subunits that are either covalently linked or have a defined
 * invariant stoichiometric relationship. This includes all synthetic, recombinant
 * and purified SubstanceProteins of defined sequence, whether the use is
 * therapeutic or prophylactic. This set of elements will be used to describe
 * albumins, coagulation factors, cytokines, growth factors,
 * peptide/SubstanceProtein hormones, enzymes, toxins, toxoids, recombinant
 * vaccines, and immunomodulators.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 *
 * Class FHIRSubstanceProtein
 * @package \OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource
 */
class FHIRSubstanceProtein extends FHIRDomainResource implements PHPFHIRContainedTypeInterface
{
    // name of FHIR type this class describes
    const FHIR_TYPE_NAME = PHPFHIRConstants::TYPE_NAME_SUBSTANCE_PROTEIN;
    const FIELD_SEQUENCE_TYPE = 'sequenceType';
    const FIELD_NUMBER_OF_SUBUNITS = 'numberOfSubunits';
    const FIELD_NUMBER_OF_SUBUNITS_EXT = '_numberOfSubunits';
    const FIELD_DISULFIDE_LINKAGE = 'disulfideLinkage';
    const FIELD_DISULFIDE_LINKAGE_EXT = '_disulfideLinkage';
    const FIELD_SUBUNIT = 'subunit';

    /** @var string */
    private $_xmlns = '';

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The SubstanceProtein descriptive elements will only be used when a complete or
     * partial amino acid sequence is available or derivable from a nucleic acid
     * sequence.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    protected $sequenceType = null;

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Number of linear sequences of amino acids linked through peptide bonds. The
     * number of subunits constituting the SubstanceProtein shall be described. It is
     * possible that the number of subunits can be variable.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    protected $numberOfSubunits = null;

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The disulphide bond between two cysteine residues either on the same subunit or
     * on two different subunits shall be described. The position of the disulfide
     * bonds in the SubstanceProtein shall be listed in increasing order of subunit
     * number and position within subunit followed by the abbreviation of the amino
     * acids involved. The disulfide linkage positions shall actually contain the amino
     * acid Cysteine at the respective positions.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    protected $disulfideLinkage = [];

    /**
     * A SubstanceProtein is defined as a single unit of a linear amino acid sequence,
     * or a combination of subunits that are either covalently linked or have a defined
     * invariant stoichiometric relationship. This includes all synthetic, recombinant
     * and purified SubstanceProteins of defined sequence, whether the use is
     * therapeutic or prophylactic. This set of elements will be used to describe
     * albumins, coagulation factors, cytokines, growth factors,
     * peptide/SubstanceProtein hormones, enzymes, toxins, toxoids, recombinant
     * vaccines, and immunomodulators.
     *
     * This subclause refers to the description of each subunit constituting the
     * SubstanceProtein. A subunit is a linear sequence of amino acids linked through
     * peptide bonds. The Subunit information shall be provided when the finished
     * SubstanceProtein is a complex of multiple sequences; subunits are not used to
     * delineate domains within a single sequence. Subunits are listed in order of
     * decreasing length; sequences of the same length will be ordered by decreasing
     * molecular weight; subunits that have identical sequences will be repeated
     * multiple times.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRSubstanceProtein\FHIRSubstanceProteinSubunit[]
     */
    protected $subunit = [];

    /**
     * Validation map for fields in type SubstanceProtein
     * @var array
     */
    private static $_validationRules = [    ];

    /**
     * FHIRSubstanceProtein Constructor
     * @param null|array $data
     */
    public function __construct($data = null)
    {
        if (null === $data || [] === $data) {
            return;
        }
        if (!is_array($data)) {
            throw new \InvalidArgumentException(sprintf(
                'FHIRSubstanceProtein::_construct - $data expected to be null or array, %s seen',
                gettype($data)
            ));
        }
        parent::__construct($data);
        if (isset($data[self::FIELD_SEQUENCE_TYPE])) {
            if ($data[self::FIELD_SEQUENCE_TYPE] instanceof FHIRCodeableConcept) {
                $this->setSequenceType($data[self::FIELD_SEQUENCE_TYPE]);
            } else {
                $this->setSequenceType(new FHIRCodeableConcept($data[self::FIELD_SEQUENCE_TYPE]));
            }
        }
        if (isset($data[self::FIELD_NUMBER_OF_SUBUNITS]) || isset($data[self::FIELD_NUMBER_OF_SUBUNITS_EXT])) {
            $value = isset($data[self::FIELD_NUMBER_OF_SUBUNITS]) ? $data[self::FIELD_NUMBER_OF_SUBUNITS] : null;
            $ext = (isset($data[self::FIELD_NUMBER_OF_SUBUNITS_EXT]) && is_array($data[self::FIELD_NUMBER_OF_SUBUNITS_EXT])) ? $ext = $data[self::FIELD_NUMBER_OF_SUBUNITS_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRInteger) {
                    $this->setNumberOfSubunits($value);
                } else if (is_array($value)) {
                    $this->setNumberOfSubunits(new FHIRInteger(array_merge($ext, $value)));
                } else {
                    $this->setNumberOfSubunits(new FHIRInteger([FHIRInteger::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setNumberOfSubunits(new FHIRInteger($ext));
            }
        }
        if (isset($data[self::FIELD_DISULFIDE_LINKAGE]) || isset($data[self::FIELD_DISULFIDE_LINKAGE_EXT])) {
            $value = isset($data[self::FIELD_DISULFIDE_LINKAGE]) ? $data[self::FIELD_DISULFIDE_LINKAGE] : null;
            $ext = (isset($data[self::FIELD_DISULFIDE_LINKAGE_EXT]) && is_array($data[self::FIELD_DISULFIDE_LINKAGE_EXT])) ? $ext = $data[self::FIELD_DISULFIDE_LINKAGE_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRString) {
                    $this->addDisulfideLinkage($value);
                } else if (is_array($value)) {
                    foreach ($value as $i => $v) {
                        if ($v instanceof FHIRString) {
                            $this->addDisulfideLinkage($v);
                        } else {
                            $iext = (isset($ext[$i]) && is_array($ext[$i])) ? $ext[$i] : [];
                            if (is_array($v)) {
                                $this->addDisulfideLinkage(new FHIRString(array_merge($v, $iext)));
                            } else {
                                $this->addDisulfideLinkage(new FHIRString([FHIRString::FIELD_VALUE => $v] + $iext));
                            }
                        }
                    }
                } elseif (is_array($value)) {
                    $this->addDisulfideLinkage(new FHIRString(array_merge($ext, $value)));
                } else {
                    $this->addDisulfideLinkage(new FHIRString([FHIRString::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                foreach ($ext as $iext) {
                    $this->addDisulfideLinkage(new FHIRString($iext));
                }
            }
        }
        if (isset($data[self::FIELD_SUBUNIT])) {
            if (is_array($data[self::FIELD_SUBUNIT])) {
                foreach ($data[self::FIELD_SUBUNIT] as $v) {
                    if (null === $v) {
                        continue;
                    }
                    if ($v instanceof FHIRSubstanceProteinSubunit) {
                        $this->addSubunit($v);
                    } else {
                        $this->addSubunit(new FHIRSubstanceProteinSubunit($v));
                    }
                }
            } elseif ($data[self::FIELD_SUBUNIT] instanceof FHIRSubstanceProteinSubunit) {
                $this->addSubunit($data[self::FIELD_SUBUNIT]);
            } else {
                $this->addSubunit(new FHIRSubstanceProteinSubunit($data[self::FIELD_SUBUNIT]));
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
        return "<SubstanceProtein{$xmlns}></SubstanceProtein>";
    }
    /**
     * @return string
     */
    public function _getResourceType()
    {
        return static::FHIR_TYPE_NAME;
    }


    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The SubstanceProtein descriptive elements will only be used when a complete or
     * partial amino acid sequence is available or derivable from a nucleic acid
     * sequence.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept
     */
    public function getSequenceType()
    {
        return $this->sequenceType;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The SubstanceProtein descriptive elements will only be used when a complete or
     * partial amino acid sequence is available or derivable from a nucleic acid
     * sequence.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept $sequenceType
     * @return static
     */
    public function setSequenceType(FHIRCodeableConcept $sequenceType = null)
    {
        $this->_trackValueSet($this->sequenceType, $sequenceType);
        $this->sequenceType = $sequenceType;
        return $this;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Number of linear sequences of amino acids linked through peptide bonds. The
     * number of subunits constituting the SubstanceProtein shall be described. It is
     * possible that the number of subunits can be variable.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public function getNumberOfSubunits()
    {
        return $this->numberOfSubunits;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Number of linear sequences of amino acids linked through peptide bonds. The
     * number of subunits constituting the SubstanceProtein shall be described. It is
     * possible that the number of subunits can be variable.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRInteger $numberOfSubunits
     * @return static
     */
    public function setNumberOfSubunits($numberOfSubunits = null)
    {
        if (null !== $numberOfSubunits && !($numberOfSubunits instanceof FHIRInteger)) {
            $numberOfSubunits = new FHIRInteger($numberOfSubunits);
        }
        $this->_trackValueSet($this->numberOfSubunits, $numberOfSubunits);
        $this->numberOfSubunits = $numberOfSubunits;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The disulphide bond between two cysteine residues either on the same subunit or
     * on two different subunits shall be described. The position of the disulfide
     * bonds in the SubstanceProtein shall be listed in increasing order of subunit
     * number and position within subunit followed by the abbreviation of the amino
     * acids involved. The disulfide linkage positions shall actually contain the amino
     * acid Cysteine at the respective positions.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString[]
     */
    public function getDisulfideLinkage()
    {
        return $this->disulfideLinkage;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The disulphide bond between two cysteine residues either on the same subunit or
     * on two different subunits shall be described. The position of the disulfide
     * bonds in the SubstanceProtein shall be listed in increasing order of subunit
     * number and position within subunit followed by the abbreviation of the amino
     * acids involved. The disulfide linkage positions shall actually contain the amino
     * acid Cysteine at the respective positions.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString $disulfideLinkage
     * @return static
     */
    public function addDisulfideLinkage($disulfideLinkage = null)
    {
        if (null !== $disulfideLinkage && !($disulfideLinkage instanceof FHIRString)) {
            $disulfideLinkage = new FHIRString($disulfideLinkage);
        }
        $this->_trackValueAdded();
        $this->disulfideLinkage[] = $disulfideLinkage;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The disulphide bond between two cysteine residues either on the same subunit or
     * on two different subunits shall be described. The position of the disulfide
     * bonds in the SubstanceProtein shall be listed in increasing order of subunit
     * number and position within subunit followed by the abbreviation of the amino
     * acids involved. The disulfide linkage positions shall actually contain the amino
     * acid Cysteine at the respective positions.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRString[] $disulfideLinkage
     * @return static
     */
    public function setDisulfideLinkage(array $disulfideLinkage = [])
    {
        if ([] !== $this->disulfideLinkage) {
            $this->_trackValuesRemoved(count($this->disulfideLinkage));
            $this->disulfideLinkage = [];
        }
        if ([] === $disulfideLinkage) {
            return $this;
        }
        foreach ($disulfideLinkage as $v) {
            if ($v instanceof FHIRString) {
                $this->addDisulfideLinkage($v);
            } else {
                $this->addDisulfideLinkage(new FHIRString($v));
            }
        }
        return $this;
    }

    /**
     * A SubstanceProtein is defined as a single unit of a linear amino acid sequence,
     * or a combination of subunits that are either covalently linked or have a defined
     * invariant stoichiometric relationship. This includes all synthetic, recombinant
     * and purified SubstanceProteins of defined sequence, whether the use is
     * therapeutic or prophylactic. This set of elements will be used to describe
     * albumins, coagulation factors, cytokines, growth factors,
     * peptide/SubstanceProtein hormones, enzymes, toxins, toxoids, recombinant
     * vaccines, and immunomodulators.
     *
     * This subclause refers to the description of each subunit constituting the
     * SubstanceProtein. A subunit is a linear sequence of amino acids linked through
     * peptide bonds. The Subunit information shall be provided when the finished
     * SubstanceProtein is a complex of multiple sequences; subunits are not used to
     * delineate domains within a single sequence. Subunits are listed in order of
     * decreasing length; sequences of the same length will be ordered by decreasing
     * molecular weight; subunits that have identical sequences will be repeated
     * multiple times.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRSubstanceProtein\FHIRSubstanceProteinSubunit[]
     */
    public function getSubunit()
    {
        return $this->subunit;
    }

    /**
     * A SubstanceProtein is defined as a single unit of a linear amino acid sequence,
     * or a combination of subunits that are either covalently linked or have a defined
     * invariant stoichiometric relationship. This includes all synthetic, recombinant
     * and purified SubstanceProteins of defined sequence, whether the use is
     * therapeutic or prophylactic. This set of elements will be used to describe
     * albumins, coagulation factors, cytokines, growth factors,
     * peptide/SubstanceProtein hormones, enzymes, toxins, toxoids, recombinant
     * vaccines, and immunomodulators.
     *
     * This subclause refers to the description of each subunit constituting the
     * SubstanceProtein. A subunit is a linear sequence of amino acids linked through
     * peptide bonds. The Subunit information shall be provided when the finished
     * SubstanceProtein is a complex of multiple sequences; subunits are not used to
     * delineate domains within a single sequence. Subunits are listed in order of
     * decreasing length; sequences of the same length will be ordered by decreasing
     * molecular weight; subunits that have identical sequences will be repeated
     * multiple times.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRSubstanceProtein\FHIRSubstanceProteinSubunit $subunit
     * @return static
     */
    public function addSubunit(FHIRSubstanceProteinSubunit $subunit = null)
    {
        $this->_trackValueAdded();
        $this->subunit[] = $subunit;
        return $this;
    }

    /**
     * A SubstanceProtein is defined as a single unit of a linear amino acid sequence,
     * or a combination of subunits that are either covalently linked or have a defined
     * invariant stoichiometric relationship. This includes all synthetic, recombinant
     * and purified SubstanceProteins of defined sequence, whether the use is
     * therapeutic or prophylactic. This set of elements will be used to describe
     * albumins, coagulation factors, cytokines, growth factors,
     * peptide/SubstanceProtein hormones, enzymes, toxins, toxoids, recombinant
     * vaccines, and immunomodulators.
     *
     * This subclause refers to the description of each subunit constituting the
     * SubstanceProtein. A subunit is a linear sequence of amino acids linked through
     * peptide bonds. The Subunit information shall be provided when the finished
     * SubstanceProtein is a complex of multiple sequences; subunits are not used to
     * delineate domains within a single sequence. Subunits are listed in order of
     * decreasing length; sequences of the same length will be ordered by decreasing
     * molecular weight; subunits that have identical sequences will be repeated
     * multiple times.
     *
     * @param \OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRSubstanceProtein\FHIRSubstanceProteinSubunit[] $subunit
     * @return static
     */
    public function setSubunit(array $subunit = [])
    {
        if ([] !== $this->subunit) {
            $this->_trackValuesRemoved(count($this->subunit));
            $this->subunit = [];
        }
        if ([] === $subunit) {
            return $this;
        }
        foreach ($subunit as $v) {
            if ($v instanceof FHIRSubstanceProteinSubunit) {
                $this->addSubunit($v);
            } else {
                $this->addSubunit(new FHIRSubstanceProteinSubunit($v));
            }
        }
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
        if (null !== ($v = $this->getSequenceType())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_SEQUENCE_TYPE] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getNumberOfSubunits())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_NUMBER_OF_SUBUNITS] = $fieldErrs;
            }
        }
        if ([] !== ($vs = $this->getDisulfideLinkage())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_DISULFIDE_LINKAGE, $i)] = $fieldErrs;
                }
            }
        }
        if ([] !== ($vs = $this->getSubunit())) {
            foreach ($vs as $i => $v) {
                if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                    $errs[sprintf('%s.%d', self::FIELD_SUBUNIT, $i)] = $fieldErrs;
                }
            }
        }
        if (isset($validationRules[self::FIELD_SEQUENCE_TYPE])) {
            $v = $this->getSequenceType();
            foreach ($validationRules[self::FIELD_SEQUENCE_TYPE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_SUBSTANCE_PROTEIN, self::FIELD_SEQUENCE_TYPE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_SEQUENCE_TYPE])) {
                        $errs[self::FIELD_SEQUENCE_TYPE] = [];
                    }
                    $errs[self::FIELD_SEQUENCE_TYPE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_NUMBER_OF_SUBUNITS])) {
            $v = $this->getNumberOfSubunits();
            foreach ($validationRules[self::FIELD_NUMBER_OF_SUBUNITS] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_SUBSTANCE_PROTEIN, self::FIELD_NUMBER_OF_SUBUNITS, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_NUMBER_OF_SUBUNITS])) {
                        $errs[self::FIELD_NUMBER_OF_SUBUNITS] = [];
                    }
                    $errs[self::FIELD_NUMBER_OF_SUBUNITS][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_DISULFIDE_LINKAGE])) {
            $v = $this->getDisulfideLinkage();
            foreach ($validationRules[self::FIELD_DISULFIDE_LINKAGE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_SUBSTANCE_PROTEIN, self::FIELD_DISULFIDE_LINKAGE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_DISULFIDE_LINKAGE])) {
                        $errs[self::FIELD_DISULFIDE_LINKAGE] = [];
                    }
                    $errs[self::FIELD_DISULFIDE_LINKAGE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_SUBUNIT])) {
            $v = $this->getSubunit();
            foreach ($validationRules[self::FIELD_SUBUNIT] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_SUBSTANCE_PROTEIN, self::FIELD_SUBUNIT, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_SUBUNIT])) {
                        $errs[self::FIELD_SUBUNIT] = [];
                    }
                    $errs[self::FIELD_SUBUNIT][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_TEXT])) {
            $v = $this->getText();
            foreach ($validationRules[self::FIELD_TEXT] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_DOMAIN_RESOURCE, self::FIELD_TEXT, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_TEXT])) {
                        $errs[self::FIELD_TEXT] = [];
                    }
                    $errs[self::FIELD_TEXT][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_CONTAINED])) {
            $v = $this->getContained();
            foreach ($validationRules[self::FIELD_CONTAINED] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_DOMAIN_RESOURCE, self::FIELD_CONTAINED, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_CONTAINED])) {
                        $errs[self::FIELD_CONTAINED] = [];
                    }
                    $errs[self::FIELD_CONTAINED][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_EXTENSION])) {
            $v = $this->getExtension();
            foreach ($validationRules[self::FIELD_EXTENSION] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_DOMAIN_RESOURCE, self::FIELD_EXTENSION, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_EXTENSION])) {
                        $errs[self::FIELD_EXTENSION] = [];
                    }
                    $errs[self::FIELD_EXTENSION][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_MODIFIER_EXTENSION])) {
            $v = $this->getModifierExtension();
            foreach ($validationRules[self::FIELD_MODIFIER_EXTENSION] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_DOMAIN_RESOURCE, self::FIELD_MODIFIER_EXTENSION, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_MODIFIER_EXTENSION])) {
                        $errs[self::FIELD_MODIFIER_EXTENSION] = [];
                    }
                    $errs[self::FIELD_MODIFIER_EXTENSION][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_ID])) {
            $v = $this->getId();
            foreach ($validationRules[self::FIELD_ID] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RESOURCE, self::FIELD_ID, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_ID])) {
                        $errs[self::FIELD_ID] = [];
                    }
                    $errs[self::FIELD_ID][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_META])) {
            $v = $this->getMeta();
            foreach ($validationRules[self::FIELD_META] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RESOURCE, self::FIELD_META, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_META])) {
                        $errs[self::FIELD_META] = [];
                    }
                    $errs[self::FIELD_META][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_IMPLICIT_RULES])) {
            $v = $this->getImplicitRules();
            foreach ($validationRules[self::FIELD_IMPLICIT_RULES] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RESOURCE, self::FIELD_IMPLICIT_RULES, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_IMPLICIT_RULES])) {
                        $errs[self::FIELD_IMPLICIT_RULES] = [];
                    }
                    $errs[self::FIELD_IMPLICIT_RULES][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_LANGUAGE])) {
            $v = $this->getLanguage();
            foreach ($validationRules[self::FIELD_LANGUAGE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RESOURCE, self::FIELD_LANGUAGE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_LANGUAGE])) {
                        $errs[self::FIELD_LANGUAGE] = [];
                    }
                    $errs[self::FIELD_LANGUAGE][$rule] = $err;
                }
            }
        }
        return $errs;
    }

    /**
     * @param null|string|\DOMElement $element
     * @param null|\OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource\FHIRSubstanceProtein $type
     * @param null|int $libxmlOpts
     * @return null|\OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource\FHIRSubstanceProtein
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
                throw new \DomainException(sprintf('FHIRSubstanceProtein::xmlUnserialize - String provided is not parseable as XML: %s', implode(', ', array_map(function (\libXMLError $err) {
                    return $err->message;
                }, libxml_get_errors()))));
            }
            libxml_use_internal_errors(false);
            $element = $dom->documentElement;
        }
        if (!($element instanceof \DOMElement)) {
            throw new \InvalidArgumentException(sprintf('FHIRSubstanceProtein::xmlUnserialize - $node value must be null, \\DOMElement, or valid XML string, %s seen', is_object($element) ? get_class($element) : gettype($element)));
        }
        if (null === $type) {
            $type = new FHIRSubstanceProtein(null);
        } elseif (!is_object($type) || !($type instanceof FHIRSubstanceProtein)) {
            throw new \RuntimeException(sprintf(
                'FHIRSubstanceProtein::xmlUnserialize - $type must be instance of \OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource\FHIRSubstanceProtein or null, %s seen.',
                is_object($type) ? get_class($type) : gettype($type)
            ));
        }
        if ('' === $type->_getFHIRXMLNamespace() && (null === $element->parentNode || $element->namespaceURI !== $element->parentNode->namespaceURI)) {
            $type->_setFHIRXMLNamespace($element->namespaceURI);
        }
        for ($i = 0; $i < $element->childNodes->length; $i++) {
            $n = $element->childNodes->item($i);
            if (!($n instanceof \DOMElement)) {
                continue;
            }
            if (self::FIELD_SEQUENCE_TYPE === $n->nodeName) {
                $type->setSequenceType(FHIRCodeableConcept::xmlUnserialize($n));
            } elseif (self::FIELD_NUMBER_OF_SUBUNITS === $n->nodeName) {
                $type->setNumberOfSubunits(FHIRInteger::xmlUnserialize($n));
            } elseif (self::FIELD_DISULFIDE_LINKAGE === $n->nodeName) {
                $type->addDisulfideLinkage(FHIRString::xmlUnserialize($n));
            } elseif (self::FIELD_SUBUNIT === $n->nodeName) {
                $type->addSubunit(FHIRSubstanceProteinSubunit::xmlUnserialize($n));
            } elseif (self::FIELD_TEXT === $n->nodeName) {
                $type->setText(FHIRNarrative::xmlUnserialize($n));
            } elseif (self::FIELD_CONTAINED === $n->nodeName) {
                for ($ni = 0; $ni < $n->childNodes->length; $ni++) {
                    $nn = $n->childNodes->item($ni);
                    if ($nn instanceof \DOMElement) {
                        $type->addContained(PHPFHIRTypeMap::getContainedTypeFromXML($nn));
                    }
                }
            } elseif (self::FIELD_EXTENSION === $n->nodeName) {
                $type->addExtension(FHIRExtension::xmlUnserialize($n));
            } elseif (self::FIELD_MODIFIER_EXTENSION === $n->nodeName) {
                $type->addModifierExtension(FHIRExtension::xmlUnserialize($n));
            } elseif (self::FIELD_ID === $n->nodeName) {
                $type->setId(FHIRId::xmlUnserialize($n));
            } elseif (self::FIELD_META === $n->nodeName) {
                $type->setMeta(FHIRMeta::xmlUnserialize($n));
            } elseif (self::FIELD_IMPLICIT_RULES === $n->nodeName) {
                $type->setImplicitRules(FHIRUri::xmlUnserialize($n));
            } elseif (self::FIELD_LANGUAGE === $n->nodeName) {
                $type->setLanguage(FHIRCode::xmlUnserialize($n));
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_NUMBER_OF_SUBUNITS);
        if (null !== $n) {
            $pt = $type->getNumberOfSubunits();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setNumberOfSubunits($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_DISULFIDE_LINKAGE);
        if (null !== $n) {
            $pt = $type->getDisulfideLinkage();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->addDisulfideLinkage($n->nodeValue);
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
        $n = $element->attributes->getNamedItem(self::FIELD_IMPLICIT_RULES);
        if (null !== $n) {
            $pt = $type->getImplicitRules();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setImplicitRules($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_LANGUAGE);
        if (null !== $n) {
            $pt = $type->getLanguage();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setLanguage($n->nodeValue);
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
        if (null !== ($v = $this->getSequenceType())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_SEQUENCE_TYPE);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getNumberOfSubunits())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_NUMBER_OF_SUBUNITS);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if ([] !== ($vs = $this->getDisulfideLinkage())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_DISULFIDE_LINKAGE);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        if ([] !== ($vs = $this->getSubunit())) {
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $telement = $element->ownerDocument->createElement(self::FIELD_SUBUNIT);
                $element->appendChild($telement);
                $v->xmlSerialize($telement);
            }
        }
        return $element;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $a = parent::jsonSerialize();
        if (null !== ($v = $this->getSequenceType())) {
            $a[self::FIELD_SEQUENCE_TYPE] = $v;
        }
        if (null !== ($v = $this->getNumberOfSubunits())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_NUMBER_OF_SUBUNITS] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRInteger::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_NUMBER_OF_SUBUNITS_EXT] = $ext;
            }
        }
        if ([] !== ($vs = $this->getDisulfideLinkage())) {
            $vals = [];
            $exts = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $val = $v->getValue();
                $ext = $v->jsonSerialize();
                unset($ext[FHIRString::FIELD_VALUE]);
                if (null !== $val) {
                    $vals[] = $val;
                }
                if ([] !== $ext) {
                    $exts[] = $ext;
                }
            }
            if ([] !== $vals) {
                $a[self::FIELD_DISULFIDE_LINKAGE] = $vals;
            }
            if ([] !== $exts) {
                $a[self::FIELD_DISULFIDE_LINKAGE_EXT] = $exts;
            }
        }
        if ([] !== ($vs = $this->getSubunit())) {
            $a[self::FIELD_SUBUNIT] = [];
            foreach ($vs as $v) {
                if (null === $v) {
                    continue;
                }
                $a[self::FIELD_SUBUNIT][] = $v;
            }
        }
        return [PHPFHIRConstants::JSON_FIELD_RESOURCE_TYPE => $this->_getResourceType()] + $a;
    }


    /**
     * @return string
     */
    public function __toString()
    {
        return self::FHIR_TYPE_NAME;
    }
}
