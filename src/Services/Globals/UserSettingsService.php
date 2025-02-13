<?php

/**
 * UserSettingService manage user global settings. Originally refactored from user.inc.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2010 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2024 Sophisticated Acquisitions <sophisticated.acquisitions@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Globals;

use function sqlStatement;
use function sqlQuery;

class UserSettingsService
{
// Set effective user - If no user id is provided, then use the currently logged in user
    public static function effectiveUser($user)
    {
        return (is_null($user) ? $_SESSION['authUserID'] : $user);
    }

    /**
     * Return user setting(s) from the 'users' table
     *
     * @param string $label - Setting key
     * @param int $user - user id number from users table
     * @param int $defaultUser - user id to check as alternative/default
     * @return Effective user setting for $label (NULL if does not exist)
     */
    public static function getUserSetting($label, $user = null, $defaultUser = 0)
    {

        $user = self::effectiveUser($user);

        // Collect entry for specified user or 0 (global default user)
        $res = sqlQuery("SELECT setting_value FROM user_settings
      WHERE (setting_user=? OR setting_user=?) AND setting_label=?
      ORDER BY setting_user DESC", array($user, $defaultUser, $label));

        // If no entries exist, then return NULL.
        return (isset($res['setting_value']) ? $res['setting_value'] : null);
    }

    /**
     * Check if effective user setting matches given value
     *
     * @param string $label - Setting key
     * @param string $value - Setting value
     * @param int $user - user id number from users table
     * @return boolean - true if setting exist and false if does not exist
     */
    public static function checkUserSetting($label, $value, $user = null)
    {

        $user = self::effectiveUser($user);

        $curval = self::getUserSetting($label, $user);
        if (is_null($curval)) {
            return false;
        } else {
            return ($curval === $value);
        }
    }

    /**
     * Set a user setting
     *
     * @param string $label - Setting key
     * @param string $value - Setting value
     * @param int $user - user id number from users table
     * @param boolean $createDefault - If no current global default value, create one.
     * @param boolean $overwrite - If this is set to true, then overwrite the current setting
     */
    public static function setUserSetting($label, $value, $user = null, $createDefault = true, $overwrite = true)
    {

        $user = self::effectiveUser($user);

        $cur_value = self::getUserSetting($label, $user, $user);

        // Check for a custom settings
        if (is_null($cur_value)) {
            sqlStatement("INSERT INTO user_settings(setting_user, setting_label, setting_value) " .
                "VALUES (?,?,?)", array($user, $label, $value));
        } elseif (($cur_value !== $value) && $overwrite) {
            sqlStatement("UPDATE user_settings SET setting_value=? " .
                "WHERE setting_user=? AND setting_label=?", array($value, $user, $label));
        }

        // Call self to create default token
        // (Note this is only done if a default token does not yet exist, thus set overwrite to FALSE))
        if ($createDefault) {
            self::setUserSetting($label, $value, 0, false, false);
        }
    }

//This will remove the selected user setting from the 'user_settings' table.
// $label is used to determine which setting to remove
// $user is the user id number from users table
    public static function removeUserSetting($label, $user = null)
    {

        $user = self::effectiveUser($user);

        // mdsupport - DELETE has implicit select, no need to check and delete
        sqlQuery("DELETE FROM user_settings " .
            "WHERE setting_user=? AND setting_label=?", array($user, $label));
    }

    public static function getUserIDInfo($id)
    {
        return sqlQuery("SELECT fname, lname, username FROM users where id=?", array($id));
    }

    /**
     * Function to retain current user's choices from prior sessions
     * @param string $uspfx - Caller specified prefix to be used in settings key, typically script name
     * @param string $postvar - $_POST variable name containing current value
     * @param string $label - Caller specified constant added to $uspfx to create settings key
     * @param string $initval - Initial value to be saved in case user setting does not exist
     * @return Prior setting (if found) or initial value to be used in script
     */
    public static function prevSetting($uspfx, $postvar, $label, $initval)
    {

        $setting_key = $uspfx . $label;

        if (isset($_POST[$postvar])) {
            // If script provides current value, store it for future use.
            $pset = $_POST[$postvar];
            if ($pset != getUserSetting($setting_key)) {
                self::setUserSetting($setting_key, $_POST[$postvar]);
            }
        } else {
            // Script requires prior value
            $pset = getUserSetting($setting_key);
            if (is_null($pset)) {
                self::setUserSetting($setting_key, $initval);
                $pset = getUserSetting($setting_key);
            }
        }

        return $pset;
    }

    /**
     * Function to set the state of expandable forms as per user choice, user default or global default
     * @return string the current state of the file after updating table user_settings
     */
    public static function collectAndOrganizeExpandSetting($filenames = array())
    {
        $current_filename = $filenames[0];
        $global_value = $GLOBALS['expand_form'];

        if (self::getUserSetting($current_filename) > -1) {
            $current_state = self::getUserSetting($current_filename);
        } elseif ($global_value) {
            $current_state = $global_value;
        } else {
            $current_state = 0;
        }

        if (count($filenames)) {
            foreach ($filenames as $filename) {
                self::setUserSetting($filename, $current_state);
            }
        }

        return $current_state;
    }
}
