<?php
/**
 * Created by PhpStorm.
 * User: amiel
 * Date: 07/11/16
 * Time: 14:07
 */

class Therapy_Groups{

    const TABLE = 'therapy_groups';

    public function getAllTherapyGroups(){

        $sql = 'SELECT * FROM ' . SELF::TABLE . ' ORDER BY ' . SELF::TABLE . '.group_start_date DESC;';

        $therapy_groups = array();
        $result = sqlStatement($sql);
        while($tg = sqlFetchArray($result)){
            $therapy_groups[] = $tg;
        }
        return $therapy_groups;
    }

    public function getGroup($groupId){

        $sql = "SELECT * FROM " . self::TABLE . " WHERE group_id = ?";

        $result = sqlStatement($sql, array($groupId));
        $group = sqlFetchArray($result);

        return $group;
    }

    public function saveNewGroup(array $groupData){

        $sql = "INSERT INTO " . self::TABLE . " (group_name, group_start_date,group_type,group_participation,group_status,group_notes,group_guest_counselors) VALUES(?,?,?,?,?,?,?)";
        $groupId = sqlInsert($sql, $groupData);

        return $groupId;
    }

    public function existGroup($name, $startDate){

        $sql = "SELECT COUNT(*) AS count FROM " . self::TABLE . " WHERE group_name = ? AND group_start_date = ?";

        $result = sqlStatement($sql, array($name, $startDate));
        $count = sqlFetchArray($result);
        return($count['count'] > 0) ? true : false;
    }

}