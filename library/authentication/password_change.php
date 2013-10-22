<?php
/**
 * Function used when changing a user's password 
 * (either the user's own password or an administrator updating a different user)
 * 
 * Copyright (C) 2013 Kevin Yeh <kevin.y@integralemr.com> and OEMR <www.oemr.org>
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
 * @author  Kevin Yeh <kevin.y@integralemr.com>
 * @link    http://www.open-emr.org
 */
require_once("$srcdir/authentication/common_operations.php");

/**
 * Does the new password meet the security requirements?
 * 
 * @param type $pwd     the password to test
 * @param type $errMsg  why there was a failure
 * @return boolean      is the password good enough?
 */
function test_password_strength($pwd,&$errMsg)
{
    $require_strong=$GLOBALS['secure_password'] !=0;
    if($require_strong)
    {
        if(strlen($pwd)<8)
        {
            $errMsg=xl("Password too short. Minimum 8 characters required.");
            return false;
        }
        $features=0;
        $reg_security=array("/[a-z]+/","/[A-Z]+/","/\d+/","/[\W_]+/");
        foreach($reg_security as $expr)
        {
            if(preg_match($expr,$pwd))
            {
                $features++;
            }
        }
        if($features<3)
        {
            $errMsg=xl("Password does not meet minimum requirements and should contain at least three of the four following items: A number, a lowercase letter, an uppercase letter, a special character (Not a leter or number).");
            return false;
        }
    }
    return true;
}
/**
 * Setup or change a user's password
 * 
 * @param type $activeUser      ID of who is trying to make the change (either the user himself, or an administrator)
 * @param type $targetUser      ID of what account's password is to be updated (for a new user this doesn't exist yet).
 * @param type $currentPwd      the active user's current password 
 * @param type $newPwd          the new password for the target user
 * @param type $errMsg          passed by reference to return any 
 * @param type $create          Are we creating a new user or 
 * @param type $insert_sql      SQL to run to create the row in "users" (and generate a new id) when needed.
 * @param type $new_username    The username for a new user
 * @param type $newid           Return by reference of the ID of a created user
 * @return boolean              Was the password successfully updated/created? If false, then $errMsg will tell you why it failed.
 */
function update_password($activeUser,$targetUser,&$currentPwd,&$newPwd,&$errMsg,$create=false,$insert_sql="",$new_username=null,&$newid=null)
{
    $userSQL="SELECT ".implode(",",array(COL_PWD,COL_SALT,COL_PWD_H1,COL_SALT_H1,COL_PWD_H2,COL_SALT_H2))
            ." FROM ".TBL_USERS_SECURE
            ." WHERE ".COL_ID."=?";
    $userInfo=privQuery($userSQL,array($targetUser));
    
    // Verify the active user's password
    if($activeUser==$targetUser)
    {
        if($create)
        {
            $errMsg=xl("Trying to create user with existing username!");
            return false;
        }
        // If this user is changing his own password, then confirm that they have the current password correct
        $hash_current = oemr_password_hash($currentPwd,$userInfo[COL_SALT]);
        if(($hash_current!=$userInfo[COL_PWD]))
        {
            $errMsg=xl("Incorrect password!");
            return false;            
        }
    }
    else {
        // If this is an administrator changing someone else's password, then check that they have the password right

        $adminSQL=" SELECT ".implode(",",array(COL_PWD,COL_SALT))
                  ." FROM ".TBL_USERS_SECURE
                  ." WHERE ".COL_ID."=?";
        $adminInfo=privQuery($adminSQL,array($activeUser));
        $hash_admin = oemr_password_hash($currentPwd,$adminInfo[COL_SALT]);
        if($hash_admin!=$adminInfo[COL_PWD])
        {
            $errMsg=xl("Incorrect password!");
            return false;
        }
        if(!acl_check('admin', 'users'))
        {
            
            $errMsg=xl("Not authorized to manage users!");
            return false;
        }
    }
    // End active user check
    
    
    //Test password validity
    if(strlen($newPwd)==0)
    {
        $errMsg=xl("Empty Password Not Allowed");
        return false;
    }
    if(!test_password_strength($newPwd,$errMsg))
    {
        return false;
    }
    // End password validty checks
    
    if($userInfo===false)
    {
        // No userInfo means either a new user, or an existing user who has not been migrated to blowfish yet
        // In these cases don't worry about password history
        if($create)
        {
            privStatement($insert_sql,array());
            $getUserID=  " SELECT ".COL_ID
                        ." FROM ".TBL_USERS
                        ." WHERE ".COL_UNM."=?";
                $user_id=privQuery($getUserID,array($new_username));
                initializePassword($new_username,$user_id[COL_ID],$newPwd);
                $newid=$user_id[COL_ID];
            }
            else
            {
                $getUserNameSQL="SELECT ".COL_UNM
                        ." FROM ".TBL_USERS
                        ." WHERE ".COL_ID."=?";
                $unm=privQuery($getUserNameSQL,array($targetUser));
                if($unm===false)
                {
                    $errMsg=xl("Unknown user id:".$targetUser);
                    return false;
                }
                initializePassword($unm[COL_UNM],$targetUser,$newPwd);
                purgeCompatabilityPassword($unm[COL_UNM],$targetUser);            
                
            }
    }
    else
    {
        if($create)
        {
            $errMsg=xl("Trying to create user with existing username!");
            return false;
        }
        
        $forbid_reuse=$GLOBALS['password_history'] != 0;
        if($forbid_reuse)
        {
            // password reuse disallowed
            $hash_current = oemr_password_hash($newPwd,$userInfo[COL_SALT]);
            $hash_history1 = oemr_password_hash($newPwd,$userInfo[COL_SALT_H1]);
            $hash_history2 = oemr_password_hash($newPwd,$userInfo[COL_SALT_H2]);
            if(($hash_current==$userInfo[COL_PWD]) 
                ||($hash_history1==$userInfo[COL_PWD_H1]) 
                || ($hash_history2==$userInfo[COL_PWD_H2]))
            {
                $errMsg=xl("Reuse of three previous passwords not allowed!");
                return false;
            }
        }
        
        // Everything checks out at this point, so update the password record
        $newSalt = oemr_password_salt();
        $newHash = oemr_password_hash($newPwd,$newSalt);
        $updateParams=array();
        $updateSQL= "UPDATE ".TBL_USERS_SECURE;
        $updateSQL.=" SET ".COL_PWD."=?,".COL_SALT."=?"; array_push($updateParams,$newHash); array_push($updateParams,$newSalt);
        if($forbid_reuse){ 
            $updateSQL.=",".COL_PWD_H1."=?".",".COL_SALT_H1."=?"; array_push($updateParams,$userInfo[COL_PWD]); array_push($updateParams,$userInfo[COL_SALT]);
            $updateSQL.=",".COL_PWD_H2."=?".",".COL_SALT_H2."=?"; array_push($updateParams,$userInfo[COL_PWD_H1]); array_push($updateParams,$userInfo[COL_SALT_H1]);

            }
        $updateSQL.=" WHERE ".COL_ID."=?"; array_push($updateParams,$targetUser);
        privStatement($updateSQL,$updateParams);
    }
   
    if($GLOBALS['password_expiration_days'] != 0){
            $exp_days=$GLOBALS['password_expiration_days'];
            $exp_date = date('Y-m-d', strtotime("+$exp_days days"));
            privStatement("update users set pwd_expiration_date=? where id=?",array($exp_date,$targetUser));
    }    
    return true;
}

?>
