<?php

/**
 * Flattens a computed data-quality worklist into plain row arrays for the
 * worklist template.
 *
 * Pure presentation logic: the template carries no logic and this mapping is
 * unit-testable without a database or a rendering engine.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting\DataQuality;

/**
 * @phpstan-type GapCountRow array{label: string, count: int}
 * @phpstan-type WorklistPatientRow array{pid: int, gaps: list<string>}
 */
final class DataQualityWorklistPresenter
{
    /**
     * @return array{
     *     year: int,
     *     total: int,
     *     gapCounts: list<GapCountRow>,
     *     rows: list<WorklistPatientRow>
     * }
     */
    public function present(DataQualityWorklist $worklist): array
    {
        $gapCounts = [];
        foreach (UdsDataQualityGap::cases() as $gap) {
            $gapCounts[] = ['label' => $gap->label(), 'count' => $worklist->countOf($gap)];
        }

        $rows = [];
        foreach ($worklist->patients as $issues) {
            $rows[] = [
                'pid' => $issues->pid,
                'gaps' => array_map(static fn (UdsDataQualityGap $gap): string => $gap->label(), $issues->gaps),
            ];
        }

        return [
            'year' => $worklist->year,
            'total' => $worklist->total(),
            'gapCounts' => $gapCounts,
            'rows' => $rows,
        ];
    }
}
