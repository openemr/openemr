<?php
/**
 * Hashing and salt generation algorithms for use in password management.
 * 
 * <pre>
 * These are the algorithms used for password hashing, including generation of salt and selection
 * of a hashing algorithm.  crypt() with blowfish will be used on systems that support it.
 * If blowfish is not available, then SHA1 with a prepended salt will be used.
 * 
 * When a system without blowfish (e.g. PHP 5.2) is upgraded to one with support (e.g. php 5.3),
 * the passwords will continue to be stored and useable in SHA1 format until a user either changes
 * his own password, or an administrator issues a new password.
 * </pre>
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

define("SALT_PREFIX_SHA1",'$SHA1$');

/**
 * 
 * Generate a salt to be used with the password_hash() function.
 * 
 * <pre>
 * This function checks for the availability of the preferred hashing algorithm (BLOWFISH)
 * on the system.  If it is available the salt returned is prefixed to indicate it is for BLOWFISH.
 * If it is not available, then SHA1 will be used instead.
 * 
 * See php documentation on crypt() for more details.
 * </pre>
 * 
 * 
 * @return type     The algorithm prefix + random data for salt.
 */
function oemr_password_salt()
{
    $Allowed_Chars ='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789./';
    $Chars_Len = 63;

    $Salt_Length = 21;

    $salt = "";
    
    for($i=0; $i<$Salt_Length; $i++)
    {
        $salt .= $Allowed_Chars[mt_rand(0,$Chars_Len)];
    }    

    // This is the preferred hashing mechanism
    if(CRYPT_BLOWFISH===1)
    {
        $rounds='05';
        //This string tells crypt to apply blowfish $rounds times.
        $Blowfish_Pre = '$2a$'.$rounds.'$';
        $Blowfish_End = '$';

        return $Blowfish_Pre.$salt.$Blowfish_End;        
    }
    error_log("Blowfish hashing algorithm not available.  Upgrading to PHP 5.3.x or newer is strongly recommended");
    
    return SALT_PREFIX_SHA1.$salt;
    
    
}

/**
 * Hash a plaintext password for comparison or initial storage.
 * 
 * <pre>
 * This function either uses the built in PHP crypt() function, or sha1() depending
 * on a prefix in the salt.  This on systems without a strong enough built in algorithm
 * for crypt(), sha1() can be used as a fallback.
 * </pre>
 * 
 * @param type $plaintext
 * @param type $salt
 * @return type
 */
function oemr_password_hash($plaintext,$salt)
{
    // if this is a SHA1 salt, the use prepended salt
    if(strpos($salt,SALT_PREFIX_SHA1)===0)
    {
        return SALT_PREFIX_SHA1 . sha1($salt.$plaintext);
    }
    else { // Otherwise use PHP crypt()
        
        return crypt($plaintext,$salt);
    }
}
?>