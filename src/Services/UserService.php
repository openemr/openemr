<?php

/**
 * UserService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Victor Kofia <victor.kofia@gmail.com>
 * @copyright Copyright (c) 2017 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2017 Victor Kofia <victor.kofia@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Uuid\UuidRegistry;

class UserService
{
    /**
     * Default constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return array hydrated user object
     */
    public function getUser($userId)
    {
        return sqlQuery("SELECT * FROM `users` WHERE `id` = ?", [$userId]);
    }

    /**
     * @return array hydrated user object
     */
    public function getUserByUsername($username)
    {
        return sqlQuery("SELECT * FROM `users` WHERE `username` = ?", [$username]);
    }

    /**
     * @return array active users (fully hydrated)
     */
    public function getActiveUsers()
    {
        $users = [];
        $user = sqlStatement("SELECT * FROM `users` WHERE (`username` != '' AND `username` IS NOT NULL) AND `active` = 1 ORDER BY `lname` ASC, `fname` ASC, `mname` ASC");
        while ($row = sqlFetchArray($user)) {
            $users[] = $row;
        }
        return $users;
    }

    /**
     * @return array
     */
    public function getCurrentlyLoggedInUser()
    {
        return sqlQuery("SELECT * FROM `users` WHERE `id` = ?", [$_SESSION['authUserID']]);
    }

    /**
     * Returns a user by the given UUID.  Can take a byte string or a UUID in string format.
     * @param $userId string
     */
    public function getUserByUUID($uuid) {
        if (is_string($uuid)) {
            $uuid = UuidRegistry::uuidToBytes($uuid);
        }

        return sqlQuery("SELECT * FROM `users` WHERE `uuid` = ?", [$uuid]);
    }
}
