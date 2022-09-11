<?php

namespace OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMolecularSequence;

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
use OpenEMR\FHIR\R4\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\R4\FHIRElement\FHIRInteger;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\FHIRStringPrimitive;
use OpenEMR\FHIR\R4\PHPFHIRConstants;
use OpenEMR\FHIR\R4\PHPFHIRTypeInterface;

/**
 * Raw data describing a biological sequence.
 *
 * Class FHIRMolecularSequenceVariant
 * @package \OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMolecularSequence
 */
class FHIRMolecularSequenceVariant extends FHIRBackboneElement
{
    // name of FHIR type this class describes
    const FHIR_TYPE_NAME = PHPFHIRConstants::TYPE_NAME_MOLECULAR_SEQUENCE_DOT_VARIANT;
    const FIELD_START = 'start';
    const FIELD_START_EXT = '_start';
    const FIELD_END = 'end';
    const FIELD_END_EXT = '_end';
    const FIELD_OBSERVED_ALLELE = 'observedAllele';
    const FIELD_OBSERVED_ALLELE_EXT = '_observedAllele';
    const FIELD_REFERENCE_ALLELE = 'referenceAllele';
    const FIELD_REFERENCE_ALLELE_EXT = '_referenceAllele';
    const FIELD_CIGAR = 'cigar';
    const FIELD_CIGAR_EXT = '_cigar';
    const FIELD_VARIANT_POINTER = 'variantPointer';

    /** @var string */
    private $_xmlns = '';

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Start position of the variant on the reference sequence. If the coordinate
     * system is either 0-based or 1-based, then start position is inclusive.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    protected $start = null;

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * End position of the variant on the reference sequence. If the coordinate system
     * is 0-based then end is exclusive and does not include the last position. If the
     * coordinate system is 1-base, then end is inclusive and includes the last
     * position.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    protected $end = null;

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An allele is one of a set of coexisting sequence variants of a gene
     * ([SO:0001023](http://www.sequenceontology.org/browser/current_svn/term/SO:0001023)).
     * Nucleotide(s)/amino acids from start position of sequence to stop position of
     * sequence on the positive (+) strand of the observed sequence. When the sequence
     * type is DNA, it should be the sequence on the positive (+) strand. This will lay
     * in the range between variant.start and variant.end.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    protected $observedAllele = null;

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An allele is one of a set of coexisting sequence variants of a gene
     * ([SO:0001023](http://www.sequenceontology.org/browser/current_svn/term/SO:0001023)).
     * Nucleotide(s)/amino acids from start position of sequence to stop position of
     * sequence on the positive (+) strand of the reference sequence. When the sequence
     * type is DNA, it should be the sequence on the positive (+) strand. This will lay
     * in the range between variant.start and variant.end.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    protected $referenceAllele = null;

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Extended CIGAR string for aligning the sequence with reference bases. See
     * detailed documentation
     * [here](http://support.illumina.com/help/SequencingAnalysisWorkflow/Content/Vault/Informatics/Sequencing_Analysis/CASAVA/swSEQ_mCA_ExtendedCIGARFormat.htm).
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    protected $cigar = null;

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A pointer to an Observation containing variant information.
     *
     * @var null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    protected $variantPointer = null;

    /**
     * Validation map for fields in type MolecularSequence.Variant
     * @var array
     */
    private static $_validationRules = [    ];

    /**
     * FHIRMolecularSequenceVariant Constructor
     * @param null|array $data
     */
    public function __construct($data = null)
    {
        if (null === $data || [] === $data) {
            return;
        }
        if (!is_array($data)) {
            throw new \InvalidArgumentException(sprintf(
                'FHIRMolecularSequenceVariant::_construct - $data expected to be null or array, %s seen',
                gettype($data)
            ));
        }
        parent::__construct($data);
        if (isset($data[self::FIELD_START]) || isset($data[self::FIELD_START_EXT])) {
            $value = isset($data[self::FIELD_START]) ? $data[self::FIELD_START] : null;
            $ext = (isset($data[self::FIELD_START_EXT]) && is_array($data[self::FIELD_START_EXT])) ? $ext = $data[self::FIELD_START_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRInteger) {
                    $this->setStart($value);
                } else if (is_array($value)) {
                    $this->setStart(new FHIRInteger(array_merge($ext, $value)));
                } else {
                    $this->setStart(new FHIRInteger([FHIRInteger::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setStart(new FHIRInteger($ext));
            }
        }
        if (isset($data[self::FIELD_END]) || isset($data[self::FIELD_END_EXT])) {
            $value = isset($data[self::FIELD_END]) ? $data[self::FIELD_END] : null;
            $ext = (isset($data[self::FIELD_END_EXT]) && is_array($data[self::FIELD_END_EXT])) ? $ext = $data[self::FIELD_END_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRInteger) {
                    $this->setEnd($value);
                } else if (is_array($value)) {
                    $this->setEnd(new FHIRInteger(array_merge($ext, $value)));
                } else {
                    $this->setEnd(new FHIRInteger([FHIRInteger::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setEnd(new FHIRInteger($ext));
            }
        }
        if (isset($data[self::FIELD_OBSERVED_ALLELE]) || isset($data[self::FIELD_OBSERVED_ALLELE_EXT])) {
            $value = isset($data[self::FIELD_OBSERVED_ALLELE]) ? $data[self::FIELD_OBSERVED_ALLELE] : null;
            $ext = (isset($data[self::FIELD_OBSERVED_ALLELE_EXT]) && is_array($data[self::FIELD_OBSERVED_ALLELE_EXT])) ? $ext = $data[self::FIELD_OBSERVED_ALLELE_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRString) {
                    $this->setObservedAllele($value);
                } else if (is_array($value)) {
                    $this->setObservedAllele(new FHIRString(array_merge($ext, $value)));
                } else {
                    $this->setObservedAllele(new FHIRString([FHIRString::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setObservedAllele(new FHIRString($ext));
            }
        }
        if (isset($data[self::FIELD_REFERENCE_ALLELE]) || isset($data[self::FIELD_REFERENCE_ALLELE_EXT])) {
            $value = isset($data[self::FIELD_REFERENCE_ALLELE]) ? $data[self::FIELD_REFERENCE_ALLELE] : null;
            $ext = (isset($data[self::FIELD_REFERENCE_ALLELE_EXT]) && is_array($data[self::FIELD_REFERENCE_ALLELE_EXT])) ? $ext = $data[self::FIELD_REFERENCE_ALLELE_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRString) {
                    $this->setReferenceAllele($value);
                } else if (is_array($value)) {
                    $this->setReferenceAllele(new FHIRString(array_merge($ext, $value)));
                } else {
                    $this->setReferenceAllele(new FHIRString([FHIRString::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setReferenceAllele(new FHIRString($ext));
            }
        }
        if (isset($data[self::FIELD_CIGAR]) || isset($data[self::FIELD_CIGAR_EXT])) {
            $value = isset($data[self::FIELD_CIGAR]) ? $data[self::FIELD_CIGAR] : null;
            $ext = (isset($data[self::FIELD_CIGAR_EXT]) && is_array($data[self::FIELD_CIGAR_EXT])) ? $ext = $data[self::FIELD_CIGAR_EXT] : $ext = [];
            if (null !== $value) {
                if ($value instanceof FHIRString) {
                    $this->setCigar($value);
                } else if (is_array($value)) {
                    $this->setCigar(new FHIRString(array_merge($ext, $value)));
                } else {
                    $this->setCigar(new FHIRString([FHIRString::FIELD_VALUE => $value] + $ext));
                }
            } elseif ([] !== $ext) {
                $this->setCigar(new FHIRString($ext));
            }
        }
        if (isset($data[self::FIELD_VARIANT_POINTER])) {
            if ($data[self::FIELD_VARIANT_POINTER] instanceof FHIRReference) {
                $this->setVariantPointer($data[self::FIELD_VARIANT_POINTER]);
            } else {
                $this->setVariantPointer(new FHIRReference($data[self::FIELD_VARIANT_POINTER]));
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
        return "<MolecularSequenceVariant{$xmlns}></MolecularSequenceVariant>";
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Start position of the variant on the reference sequence. If the coordinate
     * system is either 0-based or 1-based, then start position is inclusive.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Start position of the variant on the reference sequence. If the coordinate
     * system is either 0-based or 1-based, then start position is inclusive.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRInteger $start
     * @return static
     */
    public function setStart($start = null)
    {
        if (null !== $start && !($start instanceof FHIRInteger)) {
            $start = new FHIRInteger($start);
        }
        $this->_trackValueSet($this->start, $start);
        $this->start = $start;
        return $this;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * End position of the variant on the reference sequence. If the coordinate system
     * is 0-based then end is exclusive and does not include the last position. If the
     * coordinate system is 1-base, then end is inclusive and includes the last
     * position.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRInteger
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * A whole number
     * 32 bit number; for values larger than this, use decimal
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * End position of the variant on the reference sequence. If the coordinate system
     * is 0-based then end is exclusive and does not include the last position. If the
     * coordinate system is 1-base, then end is inclusive and includes the last
     * position.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRInteger $end
     * @return static
     */
    public function setEnd($end = null)
    {
        if (null !== $end && !($end instanceof FHIRInteger)) {
            $end = new FHIRInteger($end);
        }
        $this->_trackValueSet($this->end, $end);
        $this->end = $end;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An allele is one of a set of coexisting sequence variants of a gene
     * ([SO:0001023](http://www.sequenceontology.org/browser/current_svn/term/SO:0001023)).
     * Nucleotide(s)/amino acids from start position of sequence to stop position of
     * sequence on the positive (+) strand of the observed sequence. When the sequence
     * type is DNA, it should be the sequence on the positive (+) strand. This will lay
     * in the range between variant.start and variant.end.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getObservedAllele()
    {
        return $this->observedAllele;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An allele is one of a set of coexisting sequence variants of a gene
     * ([SO:0001023](http://www.sequenceontology.org/browser/current_svn/term/SO:0001023)).
     * Nucleotide(s)/amino acids from start position of sequence to stop position of
     * sequence on the positive (+) strand of the observed sequence. When the sequence
     * type is DNA, it should be the sequence on the positive (+) strand. This will lay
     * in the range between variant.start and variant.end.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString $observedAllele
     * @return static
     */
    public function setObservedAllele($observedAllele = null)
    {
        if (null !== $observedAllele && !($observedAllele instanceof FHIRString)) {
            $observedAllele = new FHIRString($observedAllele);
        }
        $this->_trackValueSet($this->observedAllele, $observedAllele);
        $this->observedAllele = $observedAllele;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An allele is one of a set of coexisting sequence variants of a gene
     * ([SO:0001023](http://www.sequenceontology.org/browser/current_svn/term/SO:0001023)).
     * Nucleotide(s)/amino acids from start position of sequence to stop position of
     * sequence on the positive (+) strand of the reference sequence. When the sequence
     * type is DNA, it should be the sequence on the positive (+) strand. This will lay
     * in the range between variant.start and variant.end.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getReferenceAllele()
    {
        return $this->referenceAllele;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An allele is one of a set of coexisting sequence variants of a gene
     * ([SO:0001023](http://www.sequenceontology.org/browser/current_svn/term/SO:0001023)).
     * Nucleotide(s)/amino acids from start position of sequence to stop position of
     * sequence on the positive (+) strand of the reference sequence. When the sequence
     * type is DNA, it should be the sequence on the positive (+) strand. This will lay
     * in the range between variant.start and variant.end.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString $referenceAllele
     * @return static
     */
    public function setReferenceAllele($referenceAllele = null)
    {
        if (null !== $referenceAllele && !($referenceAllele instanceof FHIRString)) {
            $referenceAllele = new FHIRString($referenceAllele);
        }
        $this->_trackValueSet($this->referenceAllele, $referenceAllele);
        $this->referenceAllele = $referenceAllele;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Extended CIGAR string for aligning the sequence with reference bases. See
     * detailed documentation
     * [here](http://support.illumina.com/help/SequencingAnalysisWorkflow/Content/Vault/Informatics/Sequencing_Analysis/CASAVA/swSEQ_mCA_ExtendedCIGARFormat.htm).
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString
     */
    public function getCigar()
    {
        return $this->cigar;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Extended CIGAR string for aligning the sequence with reference bases. See
     * detailed documentation
     * [here](http://support.illumina.com/help/SequencingAnalysisWorkflow/Content/Vault/Informatics/Sequencing_Analysis/CASAVA/swSEQ_mCA_ExtendedCIGARFormat.htm).
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRString $cigar
     * @return static
     */
    public function setCigar($cigar = null)
    {
        if (null !== $cigar && !($cigar instanceof FHIRString)) {
            $cigar = new FHIRString($cigar);
        }
        $this->_trackValueSet($this->cigar, $cigar);
        $this->cigar = $cigar;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A pointer to an Observation containing variant information.
     *
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference
     */
    public function getVariantPointer()
    {
        return $this->variantPointer;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A pointer to an Observation containing variant information.
     *
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRReference $variantPointer
     * @return static
     */
    public function setVariantPointer(FHIRReference $variantPointer = null)
    {
        $this->_trackValueSet($this->variantPointer, $variantPointer);
        $this->variantPointer = $variantPointer;
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
        if (null !== ($v = $this->getStart())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_START] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getEnd())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_END] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getObservedAllele())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_OBSERVED_ALLELE] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getReferenceAllele())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_REFERENCE_ALLELE] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getCigar())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_CIGAR] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getVariantPointer())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_VARIANT_POINTER] = $fieldErrs;
            }
        }
        if (isset($validationRules[self::FIELD_START])) {
            $v = $this->getStart();
            foreach($validationRules[self::FIELD_START] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MOLECULAR_SEQUENCE_DOT_VARIANT, self::FIELD_START, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_START])) {
                        $errs[self::FIELD_START] = [];
                    }
                    $errs[self::FIELD_START][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_END])) {
            $v = $this->getEnd();
            foreach($validationRules[self::FIELD_END] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MOLECULAR_SEQUENCE_DOT_VARIANT, self::FIELD_END, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_END])) {
                        $errs[self::FIELD_END] = [];
                    }
                    $errs[self::FIELD_END][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_OBSERVED_ALLELE])) {
            $v = $this->getObservedAllele();
            foreach($validationRules[self::FIELD_OBSERVED_ALLELE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MOLECULAR_SEQUENCE_DOT_VARIANT, self::FIELD_OBSERVED_ALLELE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_OBSERVED_ALLELE])) {
                        $errs[self::FIELD_OBSERVED_ALLELE] = [];
                    }
                    $errs[self::FIELD_OBSERVED_ALLELE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_REFERENCE_ALLELE])) {
            $v = $this->getReferenceAllele();
            foreach($validationRules[self::FIELD_REFERENCE_ALLELE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MOLECULAR_SEQUENCE_DOT_VARIANT, self::FIELD_REFERENCE_ALLELE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_REFERENCE_ALLELE])) {
                        $errs[self::FIELD_REFERENCE_ALLELE] = [];
                    }
                    $errs[self::FIELD_REFERENCE_ALLELE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_CIGAR])) {
            $v = $this->getCigar();
            foreach($validationRules[self::FIELD_CIGAR] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MOLECULAR_SEQUENCE_DOT_VARIANT, self::FIELD_CIGAR, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_CIGAR])) {
                        $errs[self::FIELD_CIGAR] = [];
                    }
                    $errs[self::FIELD_CIGAR][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_VARIANT_POINTER])) {
            $v = $this->getVariantPointer();
            foreach($validationRules[self::FIELD_VARIANT_POINTER] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_MOLECULAR_SEQUENCE_DOT_VARIANT, self::FIELD_VARIANT_POINTER, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_VARIANT_POINTER])) {
                        $errs[self::FIELD_VARIANT_POINTER] = [];
                    }
                    $errs[self::FIELD_VARIANT_POINTER][$rule] = $err;
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
     * @param null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMolecularSequence\FHIRMolecularSequenceVariant $type
     * @param null|int $libxmlOpts
     * @return null|\OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMolecularSequence\FHIRMolecularSequenceVariant
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
                throw new \DomainException(sprintf('FHIRMolecularSequenceVariant::xmlUnserialize - String provided is not parseable as XML: %s', implode(', ', array_map(function(\libXMLError $err) { return $err->message; }, libxml_get_errors()))));
            }
            libxml_use_internal_errors(false);
            $element = $dom->documentElement;
        }
        if (!($element instanceof \DOMElement)) {
            throw new \InvalidArgumentException(sprintf('FHIRMolecularSequenceVariant::xmlUnserialize - $node value must be null, \\DOMElement, or valid XML string, %s seen', is_object($element) ? get_class($element) : gettype($element)));
        }
        if (null === $type) {
            $type = new FHIRMolecularSequenceVariant(null);
        } elseif (!is_object($type) || !($type instanceof FHIRMolecularSequenceVariant)) {
            throw new \RuntimeException(sprintf(
                'FHIRMolecularSequenceVariant::xmlUnserialize - $type must be instance of \OpenEMR\FHIR\R4\FHIRElement\FHIRBackboneElement\FHIRMolecularSequence\FHIRMolecularSequenceVariant or null, %s seen.',
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
            if (self::FIELD_START === $n->nodeName) {
                $type->setStart(FHIRInteger::xmlUnserialize($n));
            } elseif (self::FIELD_END === $n->nodeName) {
                $type->setEnd(FHIRInteger::xmlUnserialize($n));
            } elseif (self::FIELD_OBSERVED_ALLELE === $n->nodeName) {
                $type->setObservedAllele(FHIRString::xmlUnserialize($n));
            } elseif (self::FIELD_REFERENCE_ALLELE === $n->nodeName) {
                $type->setReferenceAllele(FHIRString::xmlUnserialize($n));
            } elseif (self::FIELD_CIGAR === $n->nodeName) {
                $type->setCigar(FHIRString::xmlUnserialize($n));
            } elseif (self::FIELD_VARIANT_POINTER === $n->nodeName) {
                $type->setVariantPointer(FHIRReference::xmlUnserialize($n));
            } elseif (self::FIELD_MODIFIER_EXTENSION === $n->nodeName) {
                $type->addModifierExtension(FHIRExtension::xmlUnserialize($n));
            } elseif (self::FIELD_EXTENSION === $n->nodeName) {
                $type->addExtension(FHIRExtension::xmlUnserialize($n));
            } elseif (self::FIELD_ID === $n->nodeName) {
                $type->setId(FHIRStringPrimitive::xmlUnserialize($n));
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_START);
        if (null !== $n) {
            $pt = $type->getStart();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setStart($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_END);
        if (null !== $n) {
            $pt = $type->getEnd();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setEnd($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_OBSERVED_ALLELE);
        if (null !== $n) {
            $pt = $type->getObservedAllele();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setObservedAllele($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_REFERENCE_ALLELE);
        if (null !== $n) {
            $pt = $type->getReferenceAllele();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setReferenceAllele($n->nodeValue);
            }
        }
        $n = $element->attributes->getNamedItem(self::FIELD_CIGAR);
        if (null !== $n) {
            $pt = $type->getCigar();
            if (null !== $pt) {
                $pt->setValue($n->nodeValue);
            } else {
                $type->setCigar($n->nodeValue);
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
        if (null !== ($v = $this->getStart())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_START);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getEnd())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_END);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getObservedAllele())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_OBSERVED_ALLELE);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getReferenceAllele())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_REFERENCE_ALLELE);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getCigar())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_CIGAR);
            $element->appendChild($telement);
            $v->xmlSerialize($telement);
        }
        if (null !== ($v = $this->getVariantPointer())) {
            $telement = $element->ownerDocument->createElement(self::FIELD_VARIANT_POINTER);
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
        if (null !== ($v = $this->getStart())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_START] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRInteger::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_START_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getEnd())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_END] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRInteger::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_END_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getObservedAllele())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_OBSERVED_ALLELE] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRString::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_OBSERVED_ALLELE_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getReferenceAllele())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_REFERENCE_ALLELE] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRString::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_REFERENCE_ALLELE_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getCigar())) {
            if (null !== ($val = $v->getValue())) {
                $a[self::FIELD_CIGAR] = $val;
            }
            $ext = $v->jsonSerialize();
            unset($ext[FHIRString::FIELD_VALUE]);
            if ([] !== $ext) {
                $a[self::FIELD_CIGAR_EXT] = $ext;
            }
        }
        if (null !== ($v = $this->getVariantPointer())) {
            $a[self::FIELD_VARIANT_POINTER] = $v;
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