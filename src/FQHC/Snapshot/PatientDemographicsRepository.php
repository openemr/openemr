<?php

/**
 * Loads a patient's UDS-relevant demographics and resolves coded values to
 * display labels.
 *
 * The boundary between OpenEMR's loosely-typed data layer and the snapshot's
 * typed value objects: it reads `patient_data` via QueryUtils, translates the
 * race/ethnicity/language/sex code lists to titles, and returns a fully
 * resolved PatientDemographics (or null when the patient is not found).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Snapshot;

use DateTimeImmutable;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Layouts\LayoutsUtils;

final class PatientDemographicsRepository
{
    public function findByPid(int $pid): ?PatientDemographics
    {
        if ($pid <= 0) {
            return null;
        }

        $row = QueryUtils::querySingleRow(
            'SELECT fname, lname, DOB, sex, race, ethnicity, language, postal_code '
            . 'FROM patient_data WHERE pid = ? LIMIT 1',
            [$pid],
        );

        if (!is_array($row)) {
            return null;
        }

        return new PatientDemographics(
            fullName: $this->joinName($this->stringField($row, 'fname'), $this->stringField($row, 'lname')),
            ageDisplay: $this->ageDisplay($this->stringField($row, 'DOB')),
            sex: $this->label('sex', $this->stringField($row, 'sex')),
            race: $this->label('race', $this->stringField($row, 'race')),
            ethnicity: $this->label('ethnicity', $this->stringField($row, 'ethnicity')),
            language: $this->label('language', $this->stringField($row, 'language')),
            zip: $this->stringField($row, 'postal_code'),
        );
    }

    /**
     * @param array<mixed> $row
     */
    private function stringField(array $row, string $key): ?string
    {
        $value = $row[$key] ?? null;

        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }

    private function label(string $listId, ?string $code): ?string
    {
        if ($code === null) {
            return null;
        }

        $title = LayoutsUtils::getListItemTitle($listId, $code);

        return is_string($title) && trim($title) !== '' ? $title : $code;
    }

    private function joinName(?string $first, ?string $last): ?string
    {
        $parts = array_values(array_filter(
            [$first, $last],
            static fn(?string $part): bool => $part !== null,
        ));

        return $parts === [] ? null : implode(' ', $parts);
    }

    private function ageDisplay(?string $dob): ?string
    {
        $years = AgeCalculator::years($dob, new DateTimeImmutable('today'));

        return $years === null ? null : (string) $years;
    }
}
