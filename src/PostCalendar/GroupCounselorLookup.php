<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\PostCalendar;

use OpenEMR\Common\Database\QueryUtils;

/**
 * Resolves therapy-group counselor names for calendar display.
 *
 * Counselors are attached to the group rather than to an individual
 * appointment, so a calendar view needs one lookup per distinct group on
 * screen -- not one per appointment.
 */
class GroupCounselorLookup
{
    /**
     * Counselor names keyed by therapy group id, each a display-ready list.
     *
     * @param list<int> $groupIds
     * @return array<int, string>
     */
    public function namesByGroupId(array $groupIds): array
    {
        if ($groupIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($groupIds), '?'));
        $rows = QueryUtils::fetchRecords(
            "SELECT c.group_id, CONCAT(u.fname, ' ', u.lname) AS counselor_name "
            . "FROM therapy_groups_counselors AS c "
            . "JOIN users AS u ON u.id = c.user_id "
            . "WHERE c.group_id IN ($placeholders) "
            . "ORDER BY u.lname, u.fname",
            $groupIds,
        );

        $namesByGroup = [];
        foreach ($rows as $row) {
            $groupId = $row['group_id'] ?? null;
            $counselorName = $row['counselor_name'] ?? null;
            if (is_numeric($groupId) && is_string($counselorName)) {
                $namesByGroup[(int) $groupId][] = trim($counselorName);
            }
        }

        return array_map(
            static fn(array $names): string => implode(', ', $names),
            $namesByGroup,
        );
    }
}
