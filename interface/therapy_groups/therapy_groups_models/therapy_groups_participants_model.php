<?php

/**
 * interface/therapy_groups/therapy_groups_models/therapy_groups_participants_model.php contains the model for therapy group participants.
 *
 * This model fetches the participants of the therapy group from the DB.
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

class Therapy_groups_participants
{
    const TABLE = 'therapy_groups_participants';
    const PATIENT_TABLE = 'patient_data';

    public function getParticipants($groupId, $onlyActive = false)
    {

        $sql = "SELECT gp.*, p.fname, p.lname FROM " . self::TABLE . " AS gp ";
        $sql .= "JOIN " . self::PATIENT_TABLE . " AS p ON gp.pid = p.pid ";
        $sql .= "WHERE gp.group_id = ?";
        $binds = array($groupId);

        if ($onlyActive) {
            $sql .= " AND gp.group_patient_status = ?";
            $binds[] = 10;
        }

        $groupParticipants = array();
        $result = sqlStatement($sql, $binds);
        while ($gp = sqlFetchArray($result)) {
            $groupParticipants[] = $gp;
        }

        return $groupParticipants;
    }

    public function updateParticipant(array $participant, $patientId, $groupId)
    {

        if (empty($participant['group_patient_end'])) {
            $participant['group_patient_end'] = null;
        }

        $sql = "UPDATE " . self::TABLE . " SET ";
        foreach ($participant as $key => $value) {
            $sql .= $key . '=?,';
        }

        $sql = substr($sql, 0, -1);
        $sql .= ' WHERE pid = ? AND group_id = ?';

        $data = array_merge($participant, array($patientId, $groupId));
        $result = sqlStatement($sql, $data);
        return !$result ? false : true;
    }

    public function removeParticipant($groupId, $pid)
    {

        $sql = "DELETE FROM " . self::TABLE . " WHERE group_id = ? AND pid = ?";
        $result = sqlStatement($sql, array($groupId, $pid));
        return !$result ? false : true;
    }

    public function isAlreadyRegistered($pid, $groupId)
    {

        $sql = "SELECT COUNT(*) AS count FROM " . self::TABLE . " WHERE pid = ? AND group_id = ?";

//        $result = sqlStatement($sql, array($pid, $groupId));
//        $count = sqlFetchArray($result);
        $count = sqlQuery($sql, array($pid, $groupId));
        return($count['count'] > 0) ? true : false;
    }

    public function saveParticipant($participantData)
    {
           // print_r($participantData);die;
        $sql = "INSERT INTO " . self::TABLE . " VALUES(?,?,?,?,?,?);";
        $data[] = $participantData['group_id'];
        $data[] = $participantData['pid'];
        $data[] = $participantData['group_patient_status'];
        $data[] = $participantData['group_patient_start'];
        $data[] = $participantData['group_patient_end'];
        $data[] = $participantData['group_patient_comment'];

        $result = sqlStatement($sql, $data);

        return !$result ? false : true;
    }
}
