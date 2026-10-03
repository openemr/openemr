<?php

/**
 * Reads from (and prunes) the changefeed_log table.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ahmed Armaan <arman.ahmaed@carer.ai>
 * @copyright Copyright (c) 2026 Ahmed Armaan
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ChangeFeed;

use OpenEMR\Common\Database\QueryUtils;

final class ChangeFeedRepository
{
    /**
     * Highest cursor safe to serve. Rows are excluded until they are at least
     * $lagSeconds old, which lets in-flight transactions commit before their
     * auto-increment id is handed out - closing the window where a consumer
     * could advance past an id that had not yet become visible. A change is
     * therefore visible to consumers up to $lagSeconds after it commits.
     */
    public function watermark(int $lagSeconds): int
    {
        $value = QueryUtils::fetchSingleValue(
            'SELECT COALESCE(MAX(`id`), 0) AS wm FROM `' . TriggerManager::LOG_TABLE . '` '
            . 'WHERE `changed_at` <= (NOW() - INTERVAL ? SECOND)',
            'wm',
            [$lagSeconds]
        );

        return (int) $value;
    }

    /**
     * Change rows with id in ($sinceCursor, $watermark], oldest first.
     *
     * @return list<array<string, ?string>>
     */
    public function readSince(int $sinceCursor, int $watermark, int $limit): array
    {
        $limit = max(1, $limit);
        $rows = QueryUtils::fetchRecords(
            'SELECT `id`, `resource_table`, `resource_type`, `row_pk`, `row_uuid`, `op`, `changed_at` '
            . 'FROM `' . TriggerManager::LOG_TABLE . '` '
            . 'WHERE `id` > ? AND `id` <= ? '
            . 'ORDER BY `id` ASC '
            . 'LIMIT ' . $limit,
            [$sinceCursor, $watermark]
        );

        return array_values($rows);
    }

    /**
     * Delete acknowledged rows at or below $cursor to keep the log bounded.
     */
    public function pruneUpTo(int $cursor): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM `' . TriggerManager::LOG_TABLE . '` WHERE `id` <= ?',
            [$cursor]
        );
    }
}
