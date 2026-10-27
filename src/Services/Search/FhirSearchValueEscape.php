<?php

/**
 * Reads the escapes that FHIR allows in a search parameter value
 *
 * A backslash makes the next ',', '$', '|' or '\' a literal character.
 * @see https://hl7.org/fhir/R4/search.html#escaping
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    oldrobotdev <hi@oldrobot.us>
 * @copyright Copyright (c) 2026 oldrobotdev
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Search;

final class FhirSearchValueEscape
{
    /**
     * Splits the value on each separator that is not escaped. The parts keep their escapes.
     *
     * @return non-empty-list<string>
     */
    public static function split(string $value, string $separator): array
    {
        $parts = [];
        $current = '';
        $length = strlen($value);
        for ($i = 0; $i < $length; $i++) {
            $char = $value[$i];
            if ($char === '\\' && $i + 1 < $length) {
                $current .= $char . $value[++$i];
            } elseif ($char === $separator) {
                $parts[] = $current;
                $current = '';
            } else {
                $current .= $char;
            }
        }
        $parts[] = $current;
        return $parts;
    }

    public static function unescape(string $value): string
    {
        return preg_replace('/\\\\([,$|\\\\])/', '$1', $value) ?? $value;
    }
}
