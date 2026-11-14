<?php

/**
 * Reads the loosely typed values of repository and response rows for the assessment pages.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Interface\PatientFile\Assessment;

final class AssessmentValues
{
    /**
     * A string, or a number as text; anything else is empty.
     */
    public static function text(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        return is_int($value) || is_float($value) ? (string)$value : '';
    }

    /**
     * A positive int, or a string of digits that is one; anything else is null.
     */
    public static function positiveInt(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value > 0 ? $value : null;
        }
        if (!is_string($value) || $value === '' || !ctype_digit($value)) {
            return null;
        }

        $validated = filter_var($value, FILTER_VALIDATE_INT);
        return is_int($validated) && $validated > 0 ? $validated : null;
    }

    /**
     * The questionnaire's description, else its purpose, from the repository row's JSON.
     *
     * @param array<mixed> $record
     */
    public static function description(array $record): string
    {
        $json = self::text($record['questionnaire'] ?? null);
        if ($json === '') {
            return '';
        }

        $questionnaire = json_decode($json, true);
        if (!is_array($questionnaire)) {
            return '';
        }

        $description = $questionnaire['description'] ?? null;
        if (is_string($description) && $description !== '') {
            return $description;
        }

        $purpose = $questionnaire['purpose'] ?? null;
        return is_string($purpose) ? $purpose : '';
    }

    /**
     * The Bootstrap badge class for a QuestionnaireResponse status.
     */
    public static function statusClass(string $status): string
    {
        return match ($status) {
            'completed' => 'badge-success',
            'in-progress' => 'badge-warning',
            'stopped', 'entered-in-error' => 'badge-danger',
            default => 'badge-info',
        };
    }

    /**
     * When a response was last saved: last_updated, else create_time.
     *
     * @param array<mixed> $response
     */
    public static function lastSaved(array $response): string
    {
        $updated = self::text($response['last_updated'] ?? null);
        return $updated !== '' ? $updated : self::text($response['create_time'] ?? null);
    }
}
