<?php
/**
 * Created by PhpStorm.
 * User: amiel
 * Date: 08/11/2016
 * Time: 08:54
 */

class Users{

    const TABLE = 'users';

    public function getAllUsers(){

        $sql = 'SELECT id, fname, lname FROM ' . self::TABLE . ' WHERE active = 1';

        $users = array();
        $result = sqlStatement($sql);
        while($u = sqlFetchArray($result)){
            $users[] = $u;
        }

        return $users;
    }
}