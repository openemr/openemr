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
     * The name of the system user used for api requests.
     */
    const SYSTEM_USER_USERNAME = 'oe-system';

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
     * Retrieves the API System User if it exists, returns null if the user does not exist.
     * @return array
     */
    public function getSystemUser()
    {
        $user = $this->getUserByUsername(self::SYSTEM_USER_USERNAME);
        if (!empty($user)) {
            // convert to a string value here
            $user['uuid'] = UuidRegistry::uuidToString($user['uuid']);
        }
        return $user;
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
    public function getUserByUUID($uuid)
    {
        if (is_string($uuid)) {
            $uuid = UuidRegistry::uuidToBytes($uuid);
        }

        $user = sqlQuery("SELECT * FROM `users` WHERE `uuid` = ?", [$uuid]);
        // this is very annoying...
        if (!empty($user)) {
            $user['uuid'] = UuidRegistry::uuidToString($user['uuid']);
        }
        return $user;
    }

    /**
     * Returns a list of users matching optional search criteria.
     * Search criteria is conveyed by array where key = field/column name, value = field value.
     * If no search criteria is provided, all records are returned.
     *
     * @param  $search search array parameters
     * @param  $isAndCondition specifies if AND condition is used for multiple criteria. Defaults to true.
     * @return array of users that matched the results.
     */
    public function getAll($search = array(), $isAndCondition = true)
    {
        $sqlBindArray = array();

        $sql = "SELECT  id,
                        uuid,
                        users.title as title,
                        fname,
                        lname,
                        mname,
                        federaltaxid,
                        federaldrugid,
                        upin,
                        facility_id,
                        facility,
                        npi,
                        email,
                        active,
                        specialty,
                        billname,
                        url,
                        assistant,
                        organization,
                        valedictory,
                        street,
                        streetb,
                        city,
                        state,
                        zip,
                        phone,
                        fax,
                        phonew1,
                        phonecell,
                        users.notes,
                        state_license_number,
                        abook.title as abook_title,
                FROM  users
                LEFT JOIN list_options as abook ON abook.option_id = users.abook_type";

        if (!empty($search)) {
            $sql .= ' AND ';
            $whereClauses = array();
            foreach ($search as $fieldName => $fieldValue) {
                array_push($whereClauses, $fieldName . ' = ?');
                array_push($sqlBindArray, $fieldValue);
            }
            $sqlCondition = ($isAndCondition == true) ? 'AND' : 'OR';
            $sql .= implode(' ' . $sqlCondition . ' ', $whereClauses);
        }

        $statementResults = sqlStatement($sql, $sqlBindArray);
        $results = [];
        while ($row = sqlFetchArray($statementResults)) {
            $row['uuid'] = UuidRegistry::uuidToString($row['uuid']);
            $results[] = $row;
        }

        return $results;
    }
}
