<?php
/**
 * Created by PhpStorm.
 * User: amiel
 * Date: 08/11/2016
 * Time: 08:54
 */

class Users{

    const TABLE = 'users';

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

        $user_full_name = $user_name[0]['fname'] . "    " . $user_name[0]['lname'];

        return $user_full_name;

    }
}