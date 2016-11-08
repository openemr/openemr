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

}