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

        $sql = 'SELECT * FROM ' . self::TABLE . ' ORDER BY ' . self::TABLE . '.group_start_date DESC;';

        $therapy_groups = array();
        $result = sqlStatement($sql);
        while($tg = sqlFetchArray($result)){
            $therapy_groups[] = $tg;
        }
        return $therapy_groups;
    }

    public function saveNewGroup(array $groupData){

        $sql = "INSERT INTO " . self::TABLE . " (group_name, group_start_date,group_type,group_participation,group_status,group_notes,group_guest_counselors) VALUES(?,?,?,?,?,?,?)";
        $groupId = sqlInsert($sql, $groupData);

        return $groupId;
    }

}