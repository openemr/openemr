<?php

/**
 * The one-line description staff see for a held patient app write, e.g.
 * "Patient app condition: Seasonal allergic rhinitis".
 *
 * The text comes from the app, so it is stored as given and escaped wherever it is shown.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\FhirWrite;

final class PatientWriteSummary
{
    /** patient_review_request.summary is varchar(255). */
    private const MAX_LENGTH = 255;

    /**
     * @param array<array-key, mixed> $resource the held resource
     */
    public static function of(PatientWritableType $type, array $resource): string
    {
        $label = match ($type) {
            PatientWritableType::QuestionnaireResponse => 'Patient app questionnaire response',
            PatientWritableType::VitalSignObservation => 'Patient app vital sign',
            PatientWritableType::AllergyIntolerance => 'Patient app allergy',
            PatientWritableType::Condition => 'Patient app condition',
        };
        $detail = match ($type) {
            PatientWritableType::QuestionnaireResponse => self::text($resource['questionnaire'] ?? null),
            PatientWritableType::VitalSignObservation,
            PatientWritableType::AllergyIntolerance,
            PatientWritableType::Condition => self::conceptText($resource['code'] ?? null),
        };
        $summary = $detail === null ? $label : $label . ': ' . $detail;

        return mb_substr($summary, 0, self::MAX_LENGTH);
    }

    /**
     * A CodeableConcept's text, or its first coding's display, then code.
     */
    private static function conceptText(mixed $concept): ?string
    {
        if (!is_array($concept)) {
            return null;
        }
        $text = self::text($concept['text'] ?? null);
        if ($text !== null) {
            return $text;
        }
        $codings = $concept['coding'] ?? null;
        $first = is_array($codings) ? (array_values($codings)[0] ?? null) : null;
        if (!is_array($first)) {
            return null;
        }

        return self::text($first['display'] ?? null) ?? self::text($first['code'] ?? null);
    }

    /**
     * App text on one line with surrounding space removed; null when there is none.
     */
    private static function text(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }
        // One line: the list shows it in a table cell.
        $line = trim(preg_replace('/\s+/u', ' ', $value) ?? '');

        return $line === '' ? null : $line;
    }
}
