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

    public function updateGroup(array $groupData){

        $sql = "UPDATE " . self::TABLE . " SET ";
        foreach($groupData as $key => $value){
            $sql .= $key . '=?,';
        }
        $sql = substr($sql,0, -1);
        $sql .= ' WHERE group_id = ' . $groupData['group_id'];
        $result = sqlStatement($sql, $groupData);
        return !$result ? false :true;
    }

    public function existGroup($name, $startDate, $groupId = null){

        $sql = "SELECT COUNT(*) AS count FROM " . self::TABLE . " WHERE group_name = ? AND group_start_date = ?";
        $conditions = array($name, $startDate);

        if(!is_null($groupId)){
            $sql .= " AND group_id <> ?";
            $conditions[] = $groupId;
        }

        $result = sqlStatement($sql, $conditions);
        $count = sqlFetchArray($result);
        return($count['count'] > 0) ? true : false;
    }

    /**
     *
     * @param $group_id
     * @param $status
     */
    public function changeGroupStatus($group_id, $status){

        $sql = "UPDATE " . self::TABLE . " SET `group_status` = ? WHERE group_id = ?";

        sqlStatement($sql, array($status, $group_id));
    }

}