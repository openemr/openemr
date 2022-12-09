<?php 
/** **************************************************************************
 *	wmtSetting
 *
 *	Copyright (c)2016 - Medical Technology Services <MDTechSvcs.com>
 *
 *	This program is free software: you can redistribute it and/or modify it under the 
 *  terms of the GNU General Public License as published by the Free Software Foundation, 
 *  either version 3 of the License, or (at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful, but WITHOUT ANY
 *	WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 *  PARTICULAR PURPOSE. DISTRIBUTOR IS NOT LIABLE TO USER FOR ANY DAMAGES, INCLUDING 
 *  COMPENSATORY, SPECIAL, INCIDENTAL, EXEMPLARY, PUNITIVE, OR CONSEQUENTIAL DAMAGES, 
 *  CONNECTED WITH OR RESULTING FROM THIS AGREEMENT OR USE OF THIS SOFTWARE.
 *
 *	See the GNU General Public License <http://www.gnu.org/licenses/> for more details.
 *
 *  @package wmt
 *  @subpackage form
 *  @version 2.0.0
 *  @category Form Base Class
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 *
 ******************************************************************************************** */

/**
 * All new classes are defined in the WMT namespace
 */
namespace wmt;

/**
 * Provides standardized processing for user and system settings.
 *
 * @package wmt
 * @subpackage settings
 */
class Setting {

	public static function checkSettingMode($thisSetting, $thisUser='', $thisSub='') {
		$value = false;
		
		// First check for a practice setting
		$sql = "SELECT * FROM user_settings WHERE setting_user='0' AND setting_label = ?";
		$urow= sqlQuery($sql, array($thisSetting));
		if ($urow{'setting_label'} == $thisSetting) $value = $urow['setting_value'];

		// Second - check for the user over-ride
		if (isset($_SESSION['authUserID'])) { 
			$sql = "SELECT * FROM user_settings WHERE setting_user=? AND setting_label = ?";
			$urow= sqlQuery($sql,array($_SESSION['authUserID'],$thisSetting));
			if ($urow['setting_label'] == $thisSetting) $value = $urow['setting_value'];
		}
		
		// Third - is there is a sub-setting
		if ($thisSub != '') {
			$subSetting = $thisSetting .'::'. $thisSub;
			$sql= "SELECT * FROM user_settings WHERE setting_user='0' AND setting_label = ?";
			$urow = sqlQuery($sql, array($subSetting));
			if ($urow['setting_label'] == $subSetting) $value = $urow['setting_value'];

			// Fourth - if there is a sub, is there a user over-ride?
			if (isset($_SESSION['authUserID'])) { 
				$sql= "SELECT * FROM user_settings WHERE setting_user = ? AND setting_label = ?";
				$urow = sqlQuery($sql, array($_SESSION['authUserID'], $subSetting));
				if($urow['setting_label'] == $subSetting) $value = $urow['setting_value'];
			}
		}
		return $value;
	}

	public static function saveSettingMode($thisLabel='', $thisSetting='', $thisUser='') {
		if (!isset($_SESSION['authUserID']) && !$thisUser) return true;
		if (!$thisUser) $thisUser = $_SESSION['authUserID'];
		if ($thisLabel == '' || $thisSetting == '') return false;
		if (!$thisUser) $thisUser = 0;
  
		$sql = "INSERT INTO user_settings (setting_user, setting_label, setting_value) VALUES (?, ?, ?) ";
		$sql .= "ON DUPLICATE KEY UPDATE setting_value = ?";
		$test = sqlInsert($sql, array($thisUser, $thisLabel, $thisSetting, $thisSetting));

		return $test;
	}
}
?>
