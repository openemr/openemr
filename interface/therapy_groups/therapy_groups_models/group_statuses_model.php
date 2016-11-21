<?php
/**
 * Created by PhpStorm.
 * User: shaharzi
 * Date: 21/11/16
 * Time: 13:28
 */

class Group_Statuses{

    const TABLE = 'list_options';

    public function getGroupStatuses(){
        $sql = 'SELECT  option_id, title FROM ' . SELF::TABLE . ' WHERE list_id = ?;';
        $result = sqlStatement($sql, array('groupstat'));
        $final_result =array();
        while($row = sqlFetchArray($result)){
            $final_result[] = $row;
        }
        return $final_result;
    }
}