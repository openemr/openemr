<?php

/**
 * Active list option ids from list_options.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\Validation;

use OpenEMR\Common\Database\QueryUtils;

final class DatabaseListOptions implements ListOptionsSource
{
    /** @var array<string, list<string>> */
    private array $cache = [];

    public function optionIds(string $listId): array
    {
        if (!isset($this->cache[$listId])) {
            $rows = QueryUtils::fetchRecordsNoLog(
                'SELECT `option_id` FROM `list_options` WHERE `list_id` = ? AND `activity` = 1',
                [$listId],
            );
            $ids = [];
            foreach ($rows as $row) {
                $id = $row['option_id'] ?? null;
                if (is_string($id)) {
                    $ids[] = $id;
                }
            }
            $this->cache[$listId] = $ids;
        }
        return $this->cache[$listId];
    }
}
