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
    public static function normalizeLineId(mixed $lineId): ?string
    {
        if (!is_int($lineId) && !is_string($lineId)) {
            return null;
        }

        $lineId = trim((string) $lineId);
        if ($lineId === '') {
            return null;
        }

        return $lineId;
    }

    /**
     * @param array<array-key, mixed> $formDiagnoses
     * @param array<array-key, mixed> $formOrderings
     */
    public static function save(string $lineId, array $formDiagnoses, array $formOrderings): void
    {
        QueryUtils::inTransaction(function () use ($lineId, $formDiagnoses, $formOrderings): void {
            QueryUtils::sqlStatementThrowException("DELETE FROM form_physical_exam_diagnoses WHERE line_id = ?", [$lineId]);

            foreach ($formDiagnoses as $i => $diagnosis) {
                if (!is_scalar($diagnosis) || (string) $diagnosis === '') {
                    continue;
                }

                $ordering = self::normalizeOrdering($formOrderings[$i] ?? null, $i);

                $query = "INSERT INTO form_physical_exam_diagnoses (
                    line_id, ordering, diagnosis
                    ) VALUES (
                    ?, ?, ?
                    )";
                QueryUtils::sqlStatementThrowException($query, [$lineId, $ordering, (string) $diagnosis]);
            }
        });
    }

    private static function normalizeOrdering(mixed $ordering, int|string $fallback): int
    {
        return self::integerValue($ordering) ?? self::integerValue($fallback) ?? 0;
    }

    private static function integerValue(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        if (!is_string($value) || !preg_match('/^-?\d+$/', trim($value))) {
            return null;
        }

        return (int) $value;
    }
}
