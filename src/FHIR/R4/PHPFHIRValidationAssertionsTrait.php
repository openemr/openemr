<?php

namespace OpenEMR\FHIR\R4;

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

/**
 * Trait PHPFHIRValidationAssertionsTrait
 * @package \OpenEMR\FHIR\R4
 */
trait PHPFHIRValidationAssertionsTrait
{
    /**
     * Asserts that a given collection field is of a specific minimum length
     * @param string $typeName
     * @param string $fieldName
     * @param int $expected
     * @param null|array $value)
     * @return null|string
     */
    protected function _assertMinOccurs($typeName, $fieldName, $expected, $value)
    {
        if (0 >= $expected) {
            return null;
        }
        if (null === $value || !is_array($value) || [] === $value) {
            return sprintf('Field "%s" on type "%s" must have at least %d elements, but it is empty', $fieldName, $typeName, $expected);
        }
        if ($expected > ($cnt = count($value))) {
            return sprintf('Field "%s" on type "%s" must have at least %d elements, %d seen.', $fieldName, $typeName, $expected, $cnt);
        }
        return null;
    }

    /**
     * Asserts that a given collection field has no more than the specified number of elements
     * @param string $typeName
     * @param string $fieldName
     * @param int $expected
     * @param null|array $value
     * @return null|string
     */
    protected function _assertMaxOccurs($typeName, $fieldName, $expected, $value)
    {
        if (PHPFHIRConstants::UNLIMITED === $expected || null === $value || !is_array($value) || [] === $value || $expected >= ($cnt = count($value))) {
            return null;
        }
        return sprintf('Field "%s" on type "%s" must have no more than %d elements, %d seen', $fieldName, $typeName, $expected, $cnt);
    }

    /**
     * Asserts that a given string value is at least x characters long
     * @param string $typeName
     * @param string $fieldName
     * @param int $expected
     * @param null|string $value
     * @return null|string
     */
    protected function _assertMinLength($typeName, $fieldName, $expected, $value)
    {
        if (0 >= $expected) {
            return null;
        }
        if (null === $value || !is_string($value) || '' === $value) {
            return sprintf('Field "%s" on type "%s" must be at least %d characters long, but it is empty', $fieldName, $typeName, $expected);
        }
        $cnt = strlen($value);
        if ($expected <= $cnt) {
            return null;
        }
        return sprintf('Field "%s" on type "%s" must be at least %d characters long, %d seen.', $fieldName, $typeName, $expected, $cnt);
    }

    /**
     * Asserts that a given string value is no more than x characters long
     * @param string $typeName
     * @param string $fieldName
     * @param int $expected
     * @param null|string $value
     * @return null|string
     */
    protected function _assertMaxLength($typeName, $fieldName, $expected, $value)
    {
        if (PHPFHIRConstants::UNLIMITED === $expected || null === $value || !is_string($value) || '' === $value) {
            return null;
        }
        $cnt = strlen($value);
        if ($expected >= $cnt) {
            return null;
        }
        return sprintf('Field "%s" on type "%s" must be no more than %d characters long, %d seen', $fieldName, $typeName, $expected, $cnt);
    }

    /**
     * Asserts that a given value is within the expected list of values
     * @param string $typeName
     * @param string $fieldName
     * @param array $expected
     * @param mixed $value
     * @return null|string
     */
    protected function _assertValueInEnum($typeName, $fieldName, array $expected, $value)
    {
        if ([] === $expected || in_array($value, $expected, true)) {
            return null;
        }
        return sprintf(
            'Field "%s" on type "%s" value "%s" not in allowed list: [%s]',
            $fieldName,
            $typeName,
            var_export($value, true),
            implode(
                ', ',
                array_map(
                    function($v) { return var_export($v, true); },
                    $expected
                )
            )
        );
    }

    /**
     * Asserts that a given string value matches the specified pattern
     * @param string $typeName
     * @param string $fieldName
     * @param string $pattern
     * @param null|string $value
     * @return null|string
     */
    protected function _assertPatternMatch($typeName, $fieldName, $pattern, $value)
    {
        if (null === $value || !is_string($pattern) || '' === $pattern || (bool)preg_match($pattern, $value)) {
            return null;
        }
        return sprintf('Field "%s" on type "%s" value of "%s" does not match pattern: %s', $fieldName, $typeName, $value, $pattern);
    }

    /**
     * @param string $typeName
     * @param string $fieldName
     * @param string $ruleName
     * @param mixed $constraint
     * @param mixed $value
     * @return null|string
     */
    protected function _performValidation($typeName, $fieldName, $ruleName, $constraint, $value)
    {
        switch($ruleName) {
            case PHPFHIRConstants::VALIDATE_ENUM:
                return $this->_assertValueInEnum($typeName, $fieldName, $constraint, $value);
            case PHPFHIRConstants::VALIDATE_MIN_LENGTH:
                return $this->_assertMinLength($typeName, $fieldName, $constraint, $value);
            case PHPFHIRConstants::VALIDATE_MAX_LENGTH:
                return $this->_assertMaxLength($typeName, $fieldName, $constraint, $value);
            case PHPFHIRConstants::VALIDATE_MIN_OCCURS:
                return $this->_assertMinOccurs($typeName, $fieldName, $constraint, $value);
            case PHPFHIRConstants::VALIDATE_MAX_OCCURS:
                return $this->_assertMaxOccurs($typeName, $fieldName, $constraint, $value);
            case PHPFHIRConstants::VALIDATE_PATTERN:
                return $this->_assertPatternMatch($typeName, $fieldName, $constraint, $value);

            default:
                return sprintf('Type "%s" specifies unknown validation for field "%s": Name "%s"; Constraint "%s"', $typeName, $fieldName, $ruleName, var_export($constraint, true));
        }
    }
}
