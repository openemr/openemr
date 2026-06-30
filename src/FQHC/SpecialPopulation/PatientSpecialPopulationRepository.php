<?php

/**
 * Loads and stores a patient's special-population statuses.
 *
 * One row per population per patient (upserted), so a patient can hold several
 * statuses. The boundary between OpenEMR's data layer and the typed
 * SpecialPopulationStatus value objects.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\SpecialPopulation;

use DomainException;
use OpenEMR\Common\Database\QueryUtils;

final class PatientSpecialPopulationRepository
{
    /**
     * @return list<SpecialPopulationStatus>
     */
    public function findByPid(int $pid): array
    {
        if ($pid <= 0) {
            return [];
        }

        $rows = QueryUtils::fetchRecords(
            'SELECT population, subtype, as_of_date FROM fqhc_special_population WHERE pid = ? ORDER BY population',
            [$pid],
        );

        $statuses = [];
        foreach ($rows as $row) {
            $populationRaw = $row['population'] ?? null;
            if (!is_string($populationRaw)) {
                continue;
            }

            $population = SpecialPopulation::tryFrom($populationRaw);
            if ($population === null) {
                continue;
            }

            $subtypeRaw = $row['subtype'] ?? null;
            $subtype = is_string($subtypeRaw) && $subtypeRaw !== '' ? $subtypeRaw : null;

            $dateRaw = $row['as_of_date'] ?? null;
            $asOfDate = is_string($dateRaw) && $dateRaw !== '' ? $dateRaw : null;

            try {
                $statuses[] = new SpecialPopulationStatus($population, $subtype, $asOfDate);
            } catch (DomainException) {
                // A stored subtype no longer valid for its population: keep the
                // population, drop the stale subtype rather than failing the page.
                $statuses[] = new SpecialPopulationStatus($population, null, $asOfDate);
            }
        }

        return $statuses;
    }

    public function save(int $pid, SpecialPopulationStatus $status, ?int $recordedBy): void
    {
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO fqhc_special_population (pid, population, subtype, as_of_date, recorded_by) '
            . 'VALUES (?, ?, ?, ?, ?) '
            . 'ON DUPLICATE KEY UPDATE '
            . 'subtype = VALUES(subtype), as_of_date = VALUES(as_of_date), recorded_by = VALUES(recorded_by)',
            [$pid, $status->population->value, $status->subtype, $status->asOfDate, $recordedBy],
        );
    }

    public function remove(int $pid, SpecialPopulation $population): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM fqhc_special_population WHERE pid = ? AND population = ?',
            [$pid, $population->value],
        );
    }
}
