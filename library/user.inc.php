<?php

/**
 * user.inc.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @deprecated 7.0.3 see UserSettingsService
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Services\Globals\UserSettingsService;

// Set effective user - If no user id is provided, then use the currently logged in user
/**
 * @param $user
 * @deprecated 7.0.3 see UserSettingsService::effectiveUser
 * @return mixed
 */
function effectiveUser($user)
{
    return UserSettingsService::effectiveUser($user);
}

/**
 * Return user setting(s) from the 'users' table
 *
 * @param string $label - Setting key
 * @param int $user - user id number from users table
 * @param int $defaultUser - user id to check as alternative/default
 * @deprecated 7.0.3 see UserSettingsService::getUserSetting
 * @return Effective user setting for $label (NULL if does not exist)
 */
function getUserSetting($label, $user = null, $defaultUser = 0)
{
    return UserSettingsService::getUserSetting($label, $user, $defaultUser);
}

/**
 * Check if effective user setting matches given value
 *
 * @param string $label - Setting key
 * @param string $value - Setting value
 * @param int $user - user id number from users table
 * @deprecated 7.0.3 see UserSettingsService::checkUserSetting
 * @return boolean - true if setting exist and false if does not exist
 */
function checkUserSetting($label, $value, $user = null)
{
    return UserSettingsService::checkUserSetting($label, $value, $user);
}

/**
 * Set a user setting
 *
 * @param string $label - Setting key
 * @param string $value - Setting value
 * @param int $user - user id number from users table
 * @param boolean $createDefault - If no current global default value, create one.
 * @param boolean $overwrite - If this is set to true, then overwrite the current setting
 * @deprecated 7.0.3 see UserSettingsService::setUserSetting
 */
function setUserSetting($label, $value, $user = null, $createDefault = true, $overwrite = true)
{
    return UserSettingsService::setUserSetting($label, $value, $user, $createDefault, $overwrite);
}

//This will remove the selected user setting from the 'user_settings' table.
// $label is used to determine which setting to remove
// $user is the user id number from users table
/**
 * @param $label
 * @param $user
 * @deprecated 7.0.3 see UserSettingsService::removeUserSetting
 * @return null
 */
function removeUserSetting($label, $user = null)
{
    return UserSettingsService::removeUserSetting($label, $user);
}

/**
 * @param $id
 * @deprecated 7.0.3 see UserSettingsService::getUserIDInfo
 * @return array|false|null
 */
function getUserIDInfo($id)
{
    return UserSettingsService::getUserIDInfo($id);
}

/**
 * Function to retain current user's choices from prior sessions
 * @param string $uspfx - Caller specified prefix to be used in settings key, typically script name
 * @param string $postvar - $_POST variable name containing current value
 * @param string $label - Caller specified constant added to $uspfx to create settings key
 * @param string $initval - Initial value to be saved in case user setting does not exist
 * @deprecated 7.0.3 see UserSettingsService::prevSetting
 * @return Prior setting (if found) or initial value to be used in script
 */
function prevSetting($uspfx, $postvar, $label, $initval)
{
    return UserSettingsService::prevSetting($uspfx, $postvar, $label, $initval);
}

/**
 * Function to set the state of expandable forms as per user choice, user default or global default
 * @deprecated 7.0.3 see UserSettingsService::collectAndOrganizeExpandSetting
 * @return the current state of the file after updating table user_settings
 */
function collectAndOrganizeExpandSetting($filenames = array())
{
    return UserSettingsService::collectAndOrganizeExpandSetting($filenames);
}
