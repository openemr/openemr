<?php
/**
 * This is a library of commonly used functions for managing data for authentication
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
 * 
 * @param type $username
 * @param type $password    password is passed by reference so that it can be "cleared out"
 *                          as soon as we are done with it.
 * @param type $provider
 */
function validate_user_password($username,&$password,$provider)
{
    $ip=$_SERVER['REMOTE_ADDR'];
    
    $valid=false;
    $getUserSecureSQL= " SELECT " . implode(",",array(COL_ID,COL_PWD,COL_SALT))
                       ." FROM ".TBL_USERS_SECURE
                       ." WHERE BINARY ".COL_UNM."=?";
                       // Use binary keyword to require case sensitive username match
    $userSecure=privQuery($getUserSecureSQL,array($username));
    if(is_array($userSecure))
    {
        $phash=oemr_password_hash($password,$userSecure[COL_SALT]);
        if($phash!=$userSecure[COL_PWD])
        {
            
            return false;
        }
        $valid=true;
    }
    else
    {  
        if((!isset($GLOBALS['password_compatibility'])||$GLOBALS['password_compatibility']))           // use old password scheme if allowed.
        {
            $getUserSQL="select username,id, password from users where BINARY username = ?";
            $userInfo = privQuery($getUserSQL,array($username));
            if($userInfo===false)
            {
                return false;
            }
                
            $username=$userInfo['username'];
            $dbPasswordLen=strlen($userInfo['password']);
            if($dbPasswordLen==32)
            {
                $phash=md5($password);
                $valid=$phash==$userInfo['password'];
            }
            else if($dbPasswordLen==40)
            {
                $phash=sha1($password);
                $valid=$phash==$userInfo['password'];
            }
            if($valid)
            {
                $phash=initializePassword($username,$userInfo['id'],$password);
                purgeCompatabilityPassword($username,$userInfo['id']);
                $_SESSION['relogin'] = 1;
            }
            else
            {
                return false;
            }
        }
        
    }
    $getUserSQL="select id, authorized, see_auth".
                        ", cal_ui, active ".
                        " from users where BINARY username = ?";
    $userInfo = privQuery($getUserSQL,array($username));
    
    if ($userInfo['active'] != 1) {
        newEvent( 'login', $username, $provider, 0, "failure: $ip. user not active or not found in users table");
        $password='';
        return false;
    }  
    // Done with the cleartext password at this point!
    $password='';
    if($valid)
    {
        if ($authGroup = privQuery("select * from groups where user=? and name=?",array($username,$provider)))
        {
            $_SESSION['authUser'] = $username;
            $_SESSION['authPass'] = $phash;
            $_SESSION['authGroup'] = $authGroup['name'];
            $_SESSION['authUserID'] = $userInfo['id'];
            $_SESSION['authProvider'] = $provider;
            $_SESSION['authId'] = $userInfo{'id'};
            $_SESSION['cal_ui'] = $userInfo['cal_ui'];
            $_SESSION['userauthorized'] = $userInfo['authorized'];
            // Some users may be able to authorize without being providers:
            if ($userInfo['see_auth'] > '2') $_SESSION['userauthorized'] = '1';
            newEvent( 'login', $username, $provider, 1, "success: $ip");
            $valid=true;
        } else {
            newEvent( 'login', $username, $provider, 0, "failure: $ip. user not in group: $provider");
            $valid=false;
        }
        
        
        
    }
    return $valid;
}

function verify_user_gacl_group($user)
{
    global $phpgacl_location;
    if (isset ($phpgacl_location)) {
      if (acl_get_group_titles($user) == 0) {
          newEvent( 'login', $user, $provider, 0, "failure: $ip. user not in any phpGACL groups. (bad username?)");
	  return false;
      }
    }
    return true;
}
?>
