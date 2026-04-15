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
use OpenEMR\FHIR\Validation\Rules\MaxOccursRule;
use OpenEMR\FHIR\Validation\Rules\MinOccursRule;
use OpenEMR\FHIR\Validation\Rules\ValueMaxLengthRule;
use OpenEMR\FHIR\Validation\Rules\ValueMinLengthRule;
use OpenEMR\FHIR\Validation\Rules\ValueOneOfRule;
use OpenEMR\FHIR\Validation\Rules\ValuePatternMatchRule;

class Validator
{
    /**
     * Map of rules, keyed by name.
     *
     * @var \OpenEMR\FHIR\Validation\RuleInterface[]
     */
    protected static array $_rules = [];

    private static bool $_initialized = false;

    /**
     * Define a validation rule.  Will overwrite any pre-existing rule with the same name.
     *
     * @param \OpenEMR\FHIR\Validation\RuleInterface $rule
     */
    public static function setRule(RuleInterface $rule): void
    {
        self::$_rules[$rule->getName()] = $rule;
    }

    /**
     * Return a rule by name, if it exists
     *
     * @param string $ruleName
     * @return null|\OpenEMR\FHIR\Validation\RuleInterface
     */
    public static function getRule(string $ruleName): null|RuleInterface
    {
        return self::$_rules[$ruleName] ?? null;
    }

    /**
     * Return the current map of rules
     * @return \OpenEMR\FHIR\Validation\RuleInterface[]
     */
    public static function getRules(): array
    {
        return self::$_rules;
    }

    /**
     * @param \OpenEMR\FHIR\Types\TypeInterface $type
     * @param string $field
     * @param string|\OpenEMR\FHIR\Validation\RuleInterface $rule Name of registered validation rule, or a specific rule instance to run.
     * @param mixed $constraint
     * @param mixed $value
     * @return null|string
     */
    public static function runRule(TypeInterface $type,
                                   string $field,
                                   string|RuleInterface $rule,
                                   mixed $constraint,
                                   mixed $value): null|string
    {
        if ($rule instanceof RuleInterface) {
            return $rule->assert($type, $field, $constraint, $value);
        }
        if (isset(self::$_rules[$rule])) {
            return self::$_rules[$rule]->assert($type, $field, $constraint, $value);
        }
        throw new \OutOfBoundsException(sprintf('No rule named "%s" registered.', $rule));
    }

    public static function _init(): void
    {
        if (self::$_initialized) {
            return;
        }
        self::$_initialized = true;
        self::$_rules[ValueOneOfRule::NAME] = new ValueOneOfRule();
        self::$_rules[ValueMinLengthRule::NAME] = new ValueMinLengthRule();
        self::$_rules[ValueMaxLengthRule::NAME] = new ValueMaxLengthRule();
        self::$_rules[ValuePatternMatchRule::NAME] = new ValuePatternMatchRule();
        self::$_rules[MinOccursRule::NAME] = new MinOccursRule();
        self::$_rules[MaxOccursRule::NAME] = new MaxOccursRule();
    }
}

Validator::_init();
