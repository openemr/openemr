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
}