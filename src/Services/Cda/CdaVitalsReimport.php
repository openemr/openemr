<?php

/**
 * Keeps a patient's stored vital signs when a C-CDA imported again doesn't carry them.
 *
 * A vitals entry imported before (same external_id) is updated in place, and the update writes every vital.
 * The direct import hands a reading the document doesn't carry over as null, and the review-and-approve form as
 * an empty field, so a second import of the entry cleared each stored reading it lacked: a document with only
 * blood pressure erased the weight and height from the first import. A missing reading now keeps the stored one.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Cda;

final class CdaVitalsReimport
{
    /** The form_vitals readings CdaTemplateImportDispose::InsertVitals() writes. */
    private const READINGS = ['bps', 'bpd', 'height', 'weight', 'temperature', 'pulse', 'respiration', 'head_circ', 'oxygen_saturation', 'BMI'];

    /**
     * Only the importing patient's stored row fills anything: matched on external_id alone, as the update was
     * before #14926, the row can be another patient's. BMI comes from weight and height, so a stored BMI is
     * kept only while the document leaves both as they were (the review-and-approve form never sends BMI).
     *
     * @param mixed $vitals the entry as InsertVitals() takes it
     * @param mixed $stored the form_vitals row the import updates
     * @param mixed $pid    the importing patient's pid
     * @return array<mixed> the entry, with each reading it lacks (null or blank) taken from the stored row
     */
    public static function withStoredReadings(mixed $vitals, mixed $stored, mixed $pid): array
    {
        if (!is_array($vitals)) {
            throw new \InvalidArgumentException('A vitals entry is an array of its readings.');
        }
        if (!is_array($stored) || !self::samePatient($stored['pid'] ?? null, $pid)) {
            return $vitals;
        }
        $bmiStillHolds = !self::changes($vitals, $stored, 'weight') && !self::changes($vitals, $stored, 'height');
        foreach (self::READINGS as $reading) {
            if ($reading === 'BMI' && !$bmiStillHolds) {
                $vitals['BMI'] ??= null;
                continue;
            }
            if (self::lacks($vitals, $reading) && array_key_exists($reading, $stored)) {
                $vitals[$reading] = $stored[$reading];
            }
        }
        return $vitals;
    }

    /** @param array<mixed> $vitals */
    private static function lacks(array $vitals, string $reading): bool
    {
        $value = $vitals[$reading] ?? null;
        return $value === null || (is_string($value) && trim($value) === '');
    }

    /**
     * Whether the document carries a reading different from the stored one (to the hundredth, as the
     * converted readings and form_vitals' decimals can differ past that).
     *
     * @param array<mixed> $vitals
     * @param array<mixed> $stored
     */
    private static function changes(array $vitals, array $stored, string $reading): bool
    {
        if (self::lacks($vitals, $reading)) {
            return false;
        }
        $new = $vitals[$reading];
        $old = $stored[$reading] ?? null;
        if (!is_numeric($new) || !is_numeric($old)) {
            return $new !== $old;
        }
        return abs((float) $new - (float) $old) >= 0.005;
    }

    private static function samePatient(mixed $storedPid, mixed $pid): bool
    {
        return is_scalar($storedPid) && is_scalar($pid) && (string) $storedPid === (string) $pid;
    }
}
