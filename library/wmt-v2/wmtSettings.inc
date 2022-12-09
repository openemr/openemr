<?php
if(!function_exists('checkSettingMode')) {

function checkSettingMode($thisSetting, $thisUser='', $thisSub='')
{
	if(!$thisUser) $thisUser = $_SESSION['authUserID'];
  $value=false;
	// First check for a practice setting
  $sql= "SELECT * FROM user_settings WHERE setting_user='".
      "0' AND setting_label=?";
  $urow= sqlQuery($sql,array($thisSetting));
	if($urow['setting_label'] == $thisSetting) $value=$urow['setting_value'];
	// if($thisSetting == 'wmt::diag_use_checkbox') echo "First Value [$value]<br>\n";
	// Second - check for the user over-ride
	if($thisUser) { 
  	$sql= "SELECT * FROM user_settings WHERE setting_user=? ".
     		"AND setting_label=?";
  	$urow= sqlQuery($sql, array($thisUser, $thisSetting));
		if($urow['setting_label'] == $thisSetting) $value=$urow['setting_value'];
		// if($thisSetting == 'wmt::diag_use_checkbox') echo "Second Value [$value]<br>\n";
	}
	// Third - is there is a sub-setting
	if($thisSub != '') {
		$subSetting = $thisSetting .'::'. $thisSub;
  	$sql= "SELECT * FROM user_settings WHERE setting_user='".
      	"0' AND setting_label=?";
  	$urow= sqlQuery($sql,array($subSetting));
		if($urow['setting_label'] == $subSetting) $value=$urow['setting_value'];
		//  if ($thisSetting == 'wmt::diag_use_checkbox') echo "Third Value [$value]<br>\n";
		// Fourth - if there is a sub, is there a user over-ride?
		if($thisUser) { 
  		$sql= "SELECT * FROM user_settings WHERE setting_user=? ".
     			"AND setting_label=?";
  		$urow= sqlQuery($sql, array($thisUser, $subSetting));
			if($urow['setting_label'] == $subSetting) $value=$urow['setting_value'];
		// 	if($thisSetting == 'wmt::diag_use_checkbox') echo "Fourth Value [$value]<br>\n";
		}
	}
  return $value;
}

function saveSettingMode($thisLabel='', $thisSetting='', $thisUser='')
{
	if(!isset($_SESSION['authUserID']) && !$thisUser) return true;
	if(!$thisUser) $thisUser = $_SESSION['authUserID'];
	if($thisLabel == '' || $thisSetting == '') return false;
	if(!$thisUser) $thisUser = 0;
  $sql= "INSERT INTO user_settings (setting_user, setting_label, ".
		"setting_value) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE setting_value=?";
  $test= sqlInsert($sql,array($thisUser, $thisLabel, $thisSetting, $thisSetting));
  return $test;
}

}
?>
