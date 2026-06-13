<?php

/**
 * Helpers for the physical exam diagnosis editor.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2026 OpenEMR Contributors
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Forms\PhysicalExam;

use OpenEMR\Common\Database\QueryUtils;

class DiagnosisHelper
{
    /**
     * @param array<array-key, mixed> $formDiagnoses
     * @param array<array-key, mixed> $formOrderings
     */
    public static function save(string $lineId, array $formDiagnoses, array $formOrderings): void
    {
        QueryUtils::sqlStatementThrowException("DELETE FROM form_physical_exam_diagnoses WHERE line_id = ?", [$lineId]);

        foreach ($formDiagnoses as $i => $diagnosis) {
            if (!is_scalar($diagnosis) || (string) $diagnosis === '') {
                continue;
            }

            $ordering = $formOrderings[$i] ?? '';
            if (!is_scalar($ordering)) {
                $ordering = '';
            }

            $query = "INSERT INTO form_physical_exam_diagnoses (
                line_id, ordering, diagnosis
                ) VALUES (
                ?, ?, ?
                )";
            QueryUtils::sqlStatementThrowException($query, [$lineId, (string) $ordering, (string) $diagnosis]);
        }
    }
}
