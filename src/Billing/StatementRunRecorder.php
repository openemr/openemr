<?php

/**
 * Records statement runs after statement production.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenEMR
 * @copyright Copyright (c) 2026 OpenEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing;

use OpenEMR\Common\Database\QueryUtils;

final class StatementRunRecorder
{
    /**
     * A non-portal run counts when the renderer produces statement content.
     * This is independent of delivery: email failure does not undo a produced
     * statement, and print, download, and PDF paths do not expose a reliable
     * delivery-success signal. Empty output, including minimum-print exclusions,
     * is not a produced statement.
     *
     * @param list<int> $encounterIds
     */
    public static function recordIfProduced(
        array $encounterIds,
        string $statementContents,
        string $statementDate,
        bool $withoutUpdate,
        bool $debugMode,
    ): bool {
        if ($encounterIds === [] || !self::shouldRecord($statementContents, $withoutUpdate, $debugMode)) {
            return false;
        }

        $encounterIds = array_values(array_unique($encounterIds));
        $placeholders = implode(', ', array_fill(0, count($encounterIds), '?'));
        QueryUtils::sqlStatementThrowException(
            "UPDATE form_encounter
                SET last_stmt_date = ?, stmt_count = stmt_count + 1
              WHERE id IN ($placeholders)",
            [$statementDate, ...$encounterIds]
        );

        return true;
    }

    public static function shouldRecord(string $statementContents, bool $withoutUpdate, bool $debugMode): bool
    {
        return $statementContents !== '' && !$withoutUpdate && !$debugMode;
    }

    public static function hasPositiveDuePatientBalance(string $netBalance): bool
    {
        return $netBalance > 0;
    }
}
