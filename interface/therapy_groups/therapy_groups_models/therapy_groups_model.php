<?php

/**
 * interface/therapy_groups/therapy_groups_models/therapy_groups_model.php contains the model for the therapy groups.
 *
 * This model fetches the therapy groups from the DB.
 *
 * Copyright (C) 2016 Shachar Zilbershlag <shaharzi@matrix.co.il>
 * Copyright (C) 2016 Amiel Elboim <amielel@matrix.co.il>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @author  Amiel Elboim <amielel@matrix.co.il>
 * @link    http://www.open-emr.org
 */

class Therapy_Groups
{
    const TABLE = 'therapy_groups';

    public function getAllGroups()
    {

        $sql = 'SELECT * FROM ' . self::TABLE . ' ORDER BY ' . self::TABLE . '.group_start_date DESC;';

        $therapy_groups = array();
        $result = sqlStatement($sql);
        while ($tg = sqlFetchArray($result)) {
            $therapy_groups[] = $tg;
        }

        return $therapy_groups;
    }

    public function getGroup($groupId)
    {

        $sql = "SELECT * FROM " . self::TABLE . " WHERE group_id = ?";

        $group = sqlQuery($sql, array($groupId));

        return $group;
    }

    public function saveNewGroup(array $groupData)
    {

        $sql = "INSERT INTO " . self::TABLE . " (group_name, group_start_date,group_type,group_participation,group_status,group_notes,group_guest_counselors) VALUES(?,?,?,?,?,?,?)";
        $groupId = sqlInsert($sql, $groupData);

        return $groupId;
    }

    public function updateGroup(array $groupData)
    {

        $sql = "UPDATE " . self::TABLE . " SET ";
        foreach ($groupData as $key => $value) {
            $sql .= $key . '=?,';
        }

        $sql = substr($sql, 0, -1);
        $sql .= ' WHERE group_id = ?';
        array_push($groupData, $groupData['group_id']);
        $result = sqlStatement($sql, $groupData);
        return !$result ? false : true;
    }

    public function existGroup($name, $startDate, $groupId = null)
    {

        $sql = "SELECT COUNT(*) AS count FROM " . self::TABLE . " WHERE group_name = ? AND group_start_date = ?";
        $conditions = array($name, $startDate);

        if (!is_null($groupId)) {
            $sql .= " AND group_id <> ?";
            $conditions[] = $groupId;
        }

//        $result = sqlStatement($sql, $conditions);
//        $count = sqlFetchArray($result);
        $count = sqlQuery($sql, $conditions);
        return($count['count'] > 0) ? true : false;
    }

    /**
     * Changes group status in DB.
     * @param $group_id
     * @param $status
     */
    public function changeGroupStatus($group_id, $status)
    {

        $sql = "UPDATE " . self::TABLE . " SET `group_status` = ? WHERE group_id = ?";

        sqlStatement($sql, array($status, $group_id));
    }

    /**
     * Fetches groups data by given search parameter (used in popup search when in add_edit_event for groups).
     * @param $search_params
     * @param $result_columns
     * @param $column
     * @return array
     */
    public function getGroupData($search_params, $result_columns, $column, $onlyActive = true)
    {
        $sql = 'SELECT ' . $result_columns . ' FROM ' . self::TABLE . ' WHERE ' . $column . ' LIKE ? ';
        // status 20 is 'deleted'
        if ($onlyActive) {
            $sql .= ' AND group_status = 10 ';
        }

        $sql .= 'ORDER BY group_start_date DESC;';
        $search_params = '%' . $search_params . '%';
        $result = sqlStatement($sql, array($search_params));
        $final_result = array();
        while ($row = sqlFetchArray($result)) {
            $final_result[] = $row;
        }

        return $final_result;
    }
}
