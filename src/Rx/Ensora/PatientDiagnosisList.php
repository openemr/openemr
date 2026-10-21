<?php

/**
 * Selects the problem-list diagnoses sent to Ensora when launching eRx.
 *
 * Ensora identifies a diagnosis by its ICD-10 code and its NCScript schema
 * accepts at most 100 PatientDiagnosis elements, while a problem list can
 * hold the same code on many rows (re-added on later visits). Sending every
 * row repeats codes and can exceed the schema limit.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Rx\Ensora;

final class PatientDiagnosisList
{
    /** NCScript's maxOccurs for PatientDiagnosis. */
    public const MAX_DIAGNOSES = 100;

    /** NCScript types diagnosisName as AlphaNumericOptional_255. */
    private const MAX_NAME_LENGTH = 255;

    /**
     * Keep active ICD-10 codes, once each, up to MAX_DIAGNOSES. The first row
     * seen for a code wins, so pass rows newest onset first.
     *
     * @param iterable<mixed> $rows `lists` rows with diagnosis, begdate, enddate, title, date
     * @return list<PatientDiagnosis>
     */
    public static function fromProblemRows(iterable $rows): array
    {
        $diagnoses = [];
        $seen = [];
        foreach ($rows as $row) {
            if (!is_array($row) || !self::isActive($row['enddate'] ?? null)) {
                continue;
            }
            $codes = $row['diagnosis'] ?? null;
            if (!is_string($codes)) {
                continue;
            }
            // Multiple codes on one issue are stored as "ICD10:E11.9;ICD10:I10".
            foreach (explode(';', $codes) as $coded) {
                [$type, $code] = array_pad(explode(':', trim($coded), 2), 2, '');
                if ($type !== 'ICD10' || $code === '' || isset($seen[$code])) {
                    continue;
                }
                $seen[$code] = true;
                $diagnoses[] = new PatientDiagnosis(
                    $code,
                    self::compactDate($row['begdate'] ?? null),
                    self::cleanName($row['title'] ?? null, $code),
                    self::compactDate($row['date'] ?? null),
                );
                if (count($diagnoses) === self::MAX_DIAGNOSES) {
                    return $diagnoses;
                }
            }
        }

        return $diagnoses;
    }

    /** A zero date is legacy data for "no end date", as in the allergy import. */
    private static function isActive(mixed $endDate): bool
    {
        return $endDate === null
            || $endDate === ''
            || (is_string($endDate) && str_starts_with($endDate, '0000-00-00'));
    }

    private static function compactDate(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }
        $date = str_replace('-', '', substr($value, 0, 10));

        return strlen($date) === 8 && ctype_digit($date) && $date !== '00000000' ? $date : null;
    }

    /** Titles often repeat the code ("E11.9 - Type 2 diabetes") or span lines. */
    private static function cleanName(mixed $title, string $code): ?string
    {
        if (!is_string($title)) {
            return null;
        }
        $name = trim(str_replace([chr(13) . chr(10), chr(13), chr(10)], ' ', $title));
        $prefix = $code . ' -';
        if (str_starts_with($name, $prefix)) {
            $name = trim(substr($name, strlen($prefix)));
        }

        return $name === '' ? null : mb_substr($name, 0, self::MAX_NAME_LENGTH);
    }
}
