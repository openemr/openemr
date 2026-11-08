<?php

/**
 * The users the module's User Permissions apply to.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\FaxSMS\Service;

use OpenEMR\Common\Database\QueryUtils;

/**
 * Every active login user. Setup Services lists these users and its save
 * writes a permission for each one, so both must use this one list: a user
 * the save covers but the page doesn't list is reset to no permission on
 * every save, with no checkbox to turn it back on.
 *
 * A login user can carry an address book type (a provider listed as a
 * specialist, for one); address book contacts without a login have no
 * username.
 */
final class PermissionUsers
{
    /**
     * @return list<array{id: int, username: string, fname: string, lname: string}>
     */
    public static function active(): array
    {
        $rows = QueryUtils::fetchRecords(
            "SELECT `id`, `username`, `fname`, `lname` FROM `users`
              WHERE `active` = 1 AND `username` IS NOT NULL AND `username` <> ''
              ORDER BY `lname`, `fname`, `username`"
        );

        $users = [];
        foreach ($rows as $row) {
            if (!is_numeric($row['id'] ?? null) || !is_string($row['username'] ?? null)) {
                continue;
            }
            $users[] = [
                'id' => (int) $row['id'],
                'username' => $row['username'],
                'fname' => is_string($row['fname'] ?? null) ? $row['fname'] : '',
                'lname' => is_string($row['lname'] ?? null) ? $row['lname'] : '',
            ];
        }
        return $users;
    }
}
