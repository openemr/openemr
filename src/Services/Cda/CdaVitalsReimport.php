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
     * @param mixed $vitals the entry as InsertVitals() takes it
     * @param mixed $stored the form_vitals row the import updates
     * @return array<mixed> the entry, with each reading it lacks (null or blank) taken from the stored row
     */
    public static function withStoredReadings(mixed $vitals, mixed $stored): array
    {
        if (!is_array($vitals)) {
            throw new \InvalidArgumentException('A vitals entry is an array of its readings.');
        }
        if (!is_array($stored)) {
            return $vitals;
        }
        foreach (self::READINGS as $reading) {
            $value = $vitals[$reading] ?? null;
            if (($value === null || (is_string($value) && trim($value) === '')) && array_key_exists($reading, $stored)) {
                $vitals[$reading] = $stored[$reading];
            }
        }
        return $vitals;
    }
}
