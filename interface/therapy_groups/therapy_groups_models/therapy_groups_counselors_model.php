<?php
/**
 * Created by PhpStorm.
 * User: shaharzi
 * Date: 08/11/16
 * Time: 15:06
 */

class Therapy_Groups_Counselors{

    const TABLE = 'therapy_groups_counselors';

    public function getAllCounselors(){

        $sql = 'SELECT * FROM ' . SELf::TABLE;

        $counselors = array();
        $result = sqlStatement($sql);
        while($c = sqlFetchArray($result)){
            $counselors[] = $c;
        }
        return $counselors;

    }

    public function getCounselors($groupId){

        $sql = 'SELECT user_id FROM ' . self::TABLE . ' WHERE group_id = ?';

        $counselors = array();
        $result = sqlStatement($sql, array($groupId));
        while($c = sqlFetchArray($result)){
            $counselors[] = $c['user_id'];
        }
        return $counselors;

    }


    public function save($groupId, $userId){

        $sql = "INSERT INTO " . self::TABLE . " (group_id, user_id) VALUES(?,?)";
        sqlStatement($sql, array($groupId, $userId));
    }

    public function remove($groupId, $userId = null){

        $sql = "DELETE FROM " . self::TABLE . " WHERE group_id = ?";
        $condition[] = $groupId;

        if(!is_null($userId)){
            $sql .= ' AND user_id = ?';
            $condition[]= $userId;
        }
        sqlStatement($sql, $condition);
    }
}