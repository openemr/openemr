<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Validation;

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
 */

use OpenEMR\FHIR\Types\TypeInterface;

trait TypeValidationsTrait
{
    /**
     * Map of custom vlaidation rules for a given Type.
     *
     * @var array
     */
    private array $_customValidationRules = [];

    /**
     * Returns the pre-defined validations as extracted from the source FHIR schema.
     *
     * @return array
     */
    public function _getFHIRValidationRules(): array
    {
        return self::_FHIR_VALIDATION_RULES;
    }

    /**
     * Return all custom validation rules
     *
     * @return array
     */
    public function _getCustomValidationRules(): array
    {
        return $this->_customValidationRules;
    }

    /**
     * Returns all validation rules for this type, with custom validations overridding those extracted from the
     * FHIR schema during generation.
     *
     * The returned map has the structure: ["fieldname" => ["rule" => {constraint}]].
     *
     * @return array
     */
    public function _getCombinedValidationRules(): array
    {
        $out = self::_FHIR_VALIDATION_RULES;
        foreach ($this->_customValidationRules as $field => $rules) {
            $out[$field] = array_merge($out[$field] ?? [], $rules);
        }
        return $out;
    }

    /**
     * Set the entire validation rule map for a given field
     *
     * @param string $field Field name
     * @param array $rules Map of ["rule" => {constraint}] for this field
     */
    public function _setFieldValidationRules(string $field, array $rules): void
    {
        $this->_customValidationRules[$field] = $rules;
    }

    /**
     * Set a specific rule's constraints for a given field.  Set $constraint to null to prevent a given rule from
     * being run.
     *
     * @param string $field Field name
     * @param string $rule Rule name
     * @param mixed $constraint Rule constraint, value differs depending upon rule
     */
    public function _setFieldValidationRule(string $field, string $rule, mixed $constraint): void
    {
        if (!isset($this->_customValidationRules[$field])) {
            $this->_customValidationRules[$field] = [];
        }
        $this->_customValidationRules[$field][$rule] = $constraint;
    }

    /**
     * Executes all defined validation rules for this type, returning a map of validation failures.
     *
     * The returned map is keyed by the field and valued by a list of validation failures.  An empty array must be seen
     * as no validation errors occurring.
     *
     * @return array
     */
    public function _getValidationErrors(): array
    {
        $rules = $this->_getCombinedValidationRules();
        $errs = [];
        foreach ($this as $prop => $value) {
            if (str_starts_with($prop, '_')) {
                continue;
            }
            if (isset($rules[$prop])) {
                foreach ($rules[$prop] as $rule => $constraint) {
                    $err = Validator::runRule($this, $prop, $rule, $constraint, $value);
                    if (null !== $err) {
                        if (!isset($errs[$prop])) {
                            $errs[$prop] = [];
                        }
                        $errs[$prop][$rule] = $err;
                    }
                }
            }
            if ($value instanceof TypeInterface) {
                foreach ($value->_getValidationErrors() as $subPath => $subErrs) {
                    $errs["{$prop}.{$subPath}"] = $subErrs;
                }
            } else if (is_array($value)) {
                foreach($value as $i => $vv) {
                    if ($vv instanceof TypeInterface) {
                        foreach ($vv->_getValidationErrors() as $subPath => $subErrs) {
                            $errs["{$prop}.{$i}.{$subPath}"] = $subErrs;
                        }
                    }
                }
            }
        }
        return $errs;
    }
}
