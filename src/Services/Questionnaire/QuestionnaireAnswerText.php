<?php

/**
 * The text shown for one QuestionnaireResponse answer, for each FHIR R4 answer type: in the
 * encounter view, and on the staff screen that reviews a patient app's response before it is
 * added to the chart, so both show the same text.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Questionnaire;

final class QuestionnaireAnswerText
{
    /**
     * The text the encounter view shows for one answer, for each FHIR R4 answer type. A date, dateTime
     * or time is shown as saved; a Coding by its display, else its code.
     *
     * @param array<mixed> $answer
     */
    public static function of(array $answer): string
    {
        $text = self::firstText($answer, 'valueString', 'valueDate', 'valueDateTime', 'valueTime', 'valueUri');
        if ($text !== null) {
            return $text;
        }
        if (isset($answer['valueBoolean'])) {
            return $answer['valueBoolean'] ? 'Yes' : 'No';
        }
        foreach (['valueInteger', 'valueDecimal'] as $key) {
            $number = $answer[$key] ?? null;
            if (is_int($number) || is_float($number)) {
                return (string)$number;
            }
        }
        $quantity = $answer['valueQuantity'] ?? null;
        if (is_array($quantity) && (is_int($quantity['value'] ?? null) || is_float($quantity['value'] ?? null))) {
            return trim($quantity['value'] . ' ' . (self::firstText($quantity, 'unit', 'code') ?? ''));
        }
        foreach (['valueCoding' => ['display', 'code'], 'valueReference' => ['display', 'reference'], 'valueAttachment' => ['title', 'url']] as $key => $fields) {
            $value = $answer[$key] ?? null;
            if (is_array($value)) {
                return self::firstText($value, ...$fields) ?? ($key === 'valueAttachment' ? 'Attachment' : 'N/A');
            }
        }
        return 'N/A';
    }

    /**
     * The first of the keys whose value is a non-empty string.
     *
     * @param array<mixed> $values
     */
    private static function firstText(array $values, string ...$keys): ?string
    {
        foreach ($keys as $key) {
            if (isset($values[$key]) && is_string($values[$key]) && $values[$key] !== '') {
                return $values[$key];
            }
        }
        return null;
    }
}
