<?php

/**
 * Validates a portal record the way the Phreeze models did, so the portal pages keep getting
 * the same field errors: "{Field} exceeds the maximum length of {size}" and
 * "{Field} is not a valid number", keyed by the camelCase field name.
 *
 * The length check keeps Phreeze's rule exactly: a value fails when its length minus one is
 * greater than the size.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Validators\Portal;

final class FieldRuleValidator
{
    /**
     * @param array<string, FieldRule> $rules camelCase field => rule
     * @param array<string, mixed> $record camelCase field => value
     * @return array<string, string> camelCase field => message; empty when the record is valid
     * @throws \InvalidArgumentException when a field holds an array or object
     */
    public static function validate(array $rules, array $record): array
    {
        $errors = [];
        foreach ($rules as $field => $rule) {
            $value = $record[$field] ?? null;
            if ($value !== null && !is_scalar($value)) {
                throw new \InvalidArgumentException('Portal record field ' . $field . ' must be a single value');
            }
            $label = ucfirst($field);
            $text = (string) $value;

            if ($rule->size !== null && mb_strlen($text) - 1 > $rule->size) {
                $errors[$field] = $label . ' exceeds the maximum length of ' . $rule->size;
            }

            // Phreeze compared loosely with "": null and '' are empty, 0 and '0' are not.
            $isEmpty = $value === null || $value === '' || $value === false;
            if ($rule->numeric && !($isEmpty && $rule->optional) && !is_numeric($value)) {
                $errors[$field] = $label . ' is not a valid number';
            }
        }

        return $errors;
    }
}
