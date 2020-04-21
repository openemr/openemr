<?php

/**
 * interface/therapy_groups/therapy_groups_models/therapy_groups_counselors_model.php contains the model for therapy group counselors.
 *
 * This model fetches the counselors for the therapy group from the DB.
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

class Therapy_Groups_Counselors
{

    const TABLE = 'therapy_groups_counselors';

    public function getAllCounselors()
    {

        $sql = 'SELECT * FROM ' . self::TABLE;

        $counselors = array();
        $result = sqlStatement($sql);
        while ($c = sqlFetchArray($result)) {
            $counselors[] = $c;
        }

        return $counselors;
    }

    public function getCounselors($groupId)
    {

        $sql = 'SELECT user_id FROM ' . self::TABLE . ' WHERE group_id = ?';

        $counselors = array();
        $result = sqlStatement($sql, array($groupId));
        while ($c = sqlFetchArray($result)) {
            $counselors[] = $c['user_id'];
        }

        return $counselors;
    }


    public function save($groupId, $userId)
    {

        $sql = "INSERT INTO " . self::TABLE . " (group_id, user_id) VALUES(?,?)";
        sqlStatement($sql, array($groupId, $userId));
    }

    public function remove($groupId, $userId = null)
    {

        $sql = "DELETE FROM " . self::TABLE . " WHERE group_id = ?";
        $condition[] = $groupId;

        if (!is_null($userId)) {
            $sql .= ' AND user_id = ?';
            $condition[] = $userId;
        }

        sqlStatement($sql, $condition);
    }

    public function getAllCounselorsNames($groupId)
    {

        $counselors = $this->getCounselors($groupId);
        $userModel = new Users();
        $result = array();
        foreach ($counselors as $counselor) {
            $counselorName = $userModel->getUserNameById($counselor);
            $result[] = $counselorName;
        }

        return $result;
    }
}
