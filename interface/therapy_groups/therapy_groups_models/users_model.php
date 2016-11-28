<?php
/**
 * interface/therapy_groups/therapy_groups_models/users_model.php contains the model for users.
 *
 * This model fetches the users data to be used for the therapy group from the DB.
 *
 * Copyright (C) 2016 Shachar&Amiel <shachar058@gmail.com> <amielboim@gmail.com>
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
 * @author  Shachar Zilbershlag <shachar058@gmail.com>
 * @author  Amiel Elboim <amielboim@gmail.com>
 * @link    http://www.open-emr.org
 */

class Users{

    const TABLE = 'users';
    const EVENTS_TABLE = 'openemr_postcalendar_events';

    /**
     * Get all users' ids and full names from users table.
     * @return array
     */
    public function getAllUsers(){

        $sql = 'SELECT id, fname, lname FROM ' . self::TABLE . ' WHERE active = 1';

        $users = array();
        $result = sqlStatement($sql);
        while($u = sqlFetchArray($result)){
            $users[] = $u;
        }

        return $users;
    }

    /**
     * Get user name by user id from users table.
     * @param $uid
     * @return string
     */
    public function getUserNameById($uid){
        $sql = 'SELECT fname, lname FROM ' . self::TABLE . ' WHERE id = ?';

        $result = sqlStatement($sql, array($uid));
        while($u = sqlFetchArray($result)){
            $user_name[] = $u;
        }

        $user_full_name = $user_name[0]['fname'] . "   " . $user_name[0]['lname'];

        return $user_full_name;

    }

    public function getProvidersOfEvent($eid){

        $multiple = $this->checkIfMultiple($eid);
        if($multiple > 0){
            $sql = "SELECT pc_aid From " . SELF::EVENTS_TABLE . " WHERE pc_multiple = ?";
            $result = sqlStatement($sql, array($multiple));
            while($p = sqlFetchArray($result)){
                $providers[] = $p['pc_aid'];
            }
            return $providers;
        }
        else{
            $sql = "SELECT pc_aid From " . SELF::EVENTS_TABLE . " WHERE pc_eid = ?";
            $result = sqlStatement($sql, array($eid));
            while($p = sqlFetchArray($result)){
                $providers[] = $p['pc_aid'];
            }
            return $providers;
        }

    }


    /**
     * Checks if event has multiple providers and if so returns the key of multiple providers
     * @param $eid
     * @return bool|ADORecordSet_mysqli
     */
    private function checkIfMultiple($eid){

        $sql = "SELECT pc_multiple FROM " . SELF::EVENTS_TABLE . " WHERE pc_eid = ?";
        $result = sqlStatement($sql, array($eid));
        if($result->fields['pc_multiple'] == 0){
            return false;
        }
        return $result->fields['pc_multiple'];

    }
}