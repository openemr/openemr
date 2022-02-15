<?php

/**
 * UserService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Victor Kofia <victor.kofia@gmail.com>
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2017 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2017 Victor Kofia <victor.kofia@gmail.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
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

    public function getUuidFields()
    {
        return ['uuid'];
    }

    /**
     * @return array hydrated user object
     */
    public function getUser($userId)
    {
        return sqlQuery("SELECT * FROM `users` WHERE `id` = ?", [$userId]);
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
     * @return array hydrated user object
     */
    public function getUserByUsername($username)
    {
        $record = sqlQuery("SELECT * FROM `users` WHERE BINARY `username` = ?", [$username]);
        if (!empty($record)) {
            return $this->createResultRecordFromDatabaseResult($record);
        }
        return $record;
    }

    /**
     * Allows any mapping data conversion or other properties needed by a service to be returned.
     * @param $row The record returned from the database
     */
    protected function createResultRecordFromDatabaseResult($row)
    {
        $uuidFields = $this->getUuidFields();
        if (empty($uuidFields)) {
            return $row;
        } else {
            // convert all of our byte columns to strings
            foreach ($uuidFields as $fieldName) {
                if (isset($row[$fieldName])) {
                    $row[$fieldName] = UuidRegistry::uuidToString($row[$fieldName]);
                }
            }
        }
        return $row;
    }

    /**
     * Given a username, check to ensure user is in a group (and collect the group name)
     * Returns the group name if successful, or false if failure
     *
     * @param $username
     * @return string|bool
     */
    public static function getAuthGroupForUser($username)
    {
        $return = false;
        $result = privQuery("select `name` from `groups` where BINARY `user` = ?", [$username]);
        if ($result !== false && !empty($result['name'])) {
            $return = $result['name'];
        }
        return $return;
    }
}
