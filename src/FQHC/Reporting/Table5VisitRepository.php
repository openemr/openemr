<?php

/**
 * Database-backed source of countable visits for UDS Table 5.
 *
 * Reads `form_encounter` rows dated within the reporting year, excluding the
 * non-visit calendar placeholders (no-show, lunch, vacation, …), classifies each
 * to a UDS service category from its calendar category, and flags virtual visits
 * from the place-of-service code or a telehealth category. This is only the thin
 * SQL boundary; the counting rules live in the pure aggregator it feeds.
 *
 * A visit is virtual when its POS code is 11 (telehealth) or its calendar
 * category is one of the telehealth categories. The optional telehealth module's
 * session table is intentionally not required, so this works whether or not that
 * module is installed.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting;

use OpenEMR\Common\Database\QueryUtils;

final class Table5VisitRepository implements Table5VisitSource
{
    /**
     * Calendar categories that never represent a countable patient visit.
     */
    private const PLACEHOLDER_CATEGORIES = [
        'no_show',
        'in_office',
        'out_of_office',
        'vacation',
        'holidays',
        'closed',
        'lunch',
        'reserved',
    ];

    private const TELEHEALTH_CATEGORIES = [
        'comlink_telehealth_new_patient',
        'comlink_telehealth_established_patient',
    ];

    private const TELEHEALTH_POS_CODE = 11;

    private UdsServiceClassifier $classifier;

    public function __construct(?UdsServiceClassifier $classifier = null)
    {
        $this->classifier = $classifier ?? new UdsServiceClassifier();
    }

    public function visitsForYear(int $year): array
    {
        $start = sprintf('%04d-01-01 00:00:00', $year);
        $end = sprintf('%04d-01-01 00:00:00', $year + 1);

        $exclusions = implode(', ', array_fill(0, count(self::PLACEHOLDER_CATEGORIES), '?'));
        $sql = 'SELECT fe.pid AS pid, cat.pc_constant_id AS constant_id, fe.pos_code AS pos_code '
            . 'FROM form_encounter fe '
            . 'LEFT JOIN openemr_postcalendar_categories cat ON cat.pc_catid = fe.pc_catid '
            . 'WHERE fe.date >= ? AND fe.date < ? '
            . 'AND (cat.pc_constant_id IS NULL OR cat.pc_constant_id NOT IN (' . $exclusions . ')) '
            . 'ORDER BY fe.pid';

        /** @var list<array<string, mixed>> $rows */
        $rows = QueryUtils::fetchRecords($sql, array_merge([$start, $end], self::PLACEHOLDER_CATEGORIES));

        $visits = [];
        foreach ($rows as $row) {
            $pid = $row['pid'] ?? null;
            if (!is_numeric($pid) || (int) $pid <= 0) {
                continue;
            }

            $constantId = is_string($row['constant_id'] ?? null) ? $row['constant_id'] : null;

            $visits[] = new Table5VisitRecord(
                (int) $pid,
                $this->classifier->classifyByEncounterCategory($constantId),
                $this->isVirtual($row['pos_code'] ?? null, $constantId),
            );
        }

        return $visits;
    }

    private function isVirtual(mixed $posCode, ?string $constantId): bool
    {
        if (is_numeric($posCode) && (int) $posCode === self::TELEHEALTH_POS_CODE) {
            return true;
        }

        return $constantId !== null && in_array($constantId, self::TELEHEALTH_CATEGORIES, true);
    }
}
