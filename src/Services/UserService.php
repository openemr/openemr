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

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Validators\ProcessingResult;

class UserService
{
    private $_includeUsername;

    /**
     * The name of the system user used for api requests.
     */
    const SYSTEM_USER_USERNAME = 'oe-system';

    /**
     * Default constructor.
     */
    public function __construct()
    {
        $this->_includeUsername = false;
    }

    /**
     * Sensitive fields in the database that are excluded by default from the service can be included here.
     * Things such as username are normally excluded.
     * @param $fields
     * @return void
     */
    public function toggleSensitiveFields($fields)
    {
        foreach ($fields as $field) {
            switch ($field) {
                case 'username':
                    $this->_includeUsername = !$this->_includeUsername;
                    break;
            }
        }
    }

    public function getUuidFields()
    {
        return ['uuid'];
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

    /**
     * @return array hydrated user object
     */
    public function getUser($userId)
    {
        // TODO: look at deserializing uuid with createResultRecordFromDatabaseResult here
        $record = sqlQuery("SELECT * FROM `users` WHERE `id` = ?", [$userId]);
        return $this->createResultRecordFromDatabaseResult($record);
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
     * Retrieves the API System User if it exists, returns null if the user does not exist.
     * @return array
     */
    public function getSystemUser()
    {
        $user = $this->getUserByUsername(self::SYSTEM_USER_USERNAME);

        if (!empty($user)) {
            if (empty($user['uuid'])) {
                // we should always have this setup, but create them just in case.
                UuidRegistry::createMissingUuidsForTables(['users']);
            }
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
            // TODO: look at deserializing uuid with createResultRecordFromDatabaseResult here
            $users[] = $row;
        }
        return $users;
    }

    /**
     * @return array
     */
    public function getCurrentlyLoggedInUser()
    {
        // TODO: look at deserializing uuid with createResultRecordFromDatabaseResult here
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
            $user = $this->createResultRecordFromDatabaseResult($user);
        }
        return $user;
    }

    /**
     * Retrieves a single user that is authorized for the calendar
     * @param $userId
     * @return array|null
     */
    public function getUserForCalendar($userId)
    {
        // TODO: eventually we'd like to leverage the inner search piece here and combine these methods
        return $this->searchUsersForCalendar("", $userId);
    }

    /**
     * Retrieves all the users that have been set to show up on the calendar optionally filtered by the facility if one
     * is provided.
     * @param string $facility
     * @return array|null
     */
    public function getUsersForCalendar($facility = "")
    {
        return $this->searchUsersForCalendar($facility);
    }

    private function searchUsersForCalendar($facility = "", $userId = null)
    {
        // this originally came from patient.inc.php::getProviderInfo()
        $param1 = " AND authorized = 1 AND calendar = 1 ";
        $bind = [];
        if (!empty($userId)) {
            $param1 .= " AND id = ? ";
            $bind[] = $userId;
        }

        //--------------------------------
        //(CHEMED) facility filter
        $param2 = "";
        if (!empty($facility)) {
            if ($GLOBALS['restrict_user_facility']) {
                $param2 = " AND (facility_id = ? OR  ? IN (select facility_id from users_facility where tablename = 'users' and table_id = id))";
                $bind[] = $facility;
                $bind[] = $facility;
            } else {
                $param2 = " AND facility_id = ? ";
                $bind[] = $facility;
            }
        }

        $query = "select distinct id, username, lname, fname, authorized, info, facility, suffix " .
            "from users where username != '' " . $param1 . $param2;
        // sort by last name -- JRM June 2008
        $query .= " ORDER BY lname, fname ";
        $records = QueryUtils::fetchRecords($query, $bind);

        //if only one result returned take the key/value pairs in array [0] and merge them down into
        // the base array so that $resultval[0]['key'] is also accessible from $resultval['key']
        if (count($records) == 1) {
            $akeys = array_keys($records[0]);
            foreach ($akeys as $key) {
                $records[0][$key] = $records[0][$key];
            }
        }

        return ($records ?? null);
    }

    public function search($search, $isAndCondition = true)
    {
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
                        last_updated ";
        if ($this->_includeUsername) {
            $sql .= ", username";
        }
        // grab our address book type, make sure to use the index w/ list_id and option_id
        $sql .= "
                FROM  users
                LEFT JOIN (
                    SELECT list_id,option_id, title FROM list_options
                ) abook ON abook.list_id = 'abook_type' AND abook.option_id = users.abook_type";
        $whereClause = FhirSearchWhereClauseBuilder::build($search, $isAndCondition);

        $sql .= $whereClause->getFragment();
        $sqlBindArray = $whereClause->getBoundValues();
        $statementResults =  QueryUtils::sqlStatementThrowException($sql, $sqlBindArray);

        $processingResult = new ProcessingResult();
        while ($row = sqlFetchArray($statementResults)) {
            $resultRecord = $this->createResultRecordFromDatabaseResult($row);
            $processingResult->addData($resultRecord);
        }
        return $processingResult;
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
                        abook.title as abook_title";
        if ($this->_includeUsername) {
            $sql .= ", username";
        }
        $sql .= "
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
            $results[] = $this->createResultRecordFromDatabaseResult($row);
        }

        return $results;
    }

    /**
     * @return array id of User
     */
    public function getIdByUsername($username)
    {
        $id = sqlQuery("SELECT `id` FROM `users` WHERE BINARY `username` = ?", [$username]);
        if (!empty($id['id'])) {
            return $id['id'];
        } else {
            return false;
        }
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
}
