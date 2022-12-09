<?php
require_once("{$GLOBALS['srcdir']}/sql.inc");

if(!function_exists('checkApprovalMode')) {
function checkApprovalMode()
{
  $approve_on='';
  $sql= "SELECT * FROM user_settings WHERE setting_user=0 ".
      " AND setting_label='wmt::approve_mode'";
  $ures= sqlStatement($sql);
  if($ures) {
    $urow= sqlFetchArray($ures);
    $approve_on= $urow['setting_value'];
  }
  return $approve_on;
}

function canApproveForms()
{
  $allowed='';
  $pin='';
  $uname='';
  $user='';
  $sql= "SELECT * FROM user_settings WHERE setting_user=?".
      " AND setting_label='wmt::approve_allow'";
  $ures= sqlStatement($sql, array($_SESSION['authUserID']));
  if($ures) {
    $urow= sqlFetchArray($ures);
    $allowed= $urow['setting_value'];
  }
  if($allowed) {
    // Now we need the PIN for the user
    $sql= "SELECT * FROM user_settings WHERE setting_user='".
      $_SESSION["authUserID"]."' AND setting_label='wmt::approve_pin'";
    $ures= sqlStatement($sql);
    if($ures) {
      $urow= sqlFetchArray($ures);
      $pin= $urow['setting_value'];
    }
    // Now build a display user name
    $sql= "SELECT * FROM users WHERE id='".$_SESSION["authUserID"]."'";
    $ures= sqlStatement($sql);
    if($ures) {
      $urow= sqlFetchArray($ures);
      $mi=' ';
      if($urow['mname']) $mi=' '.$urow['mname'].' ';
      $uname= $urow['fname'].$mi.$urow['lname'];
    }
 
  }
	// Now check to make sure it's turned on globally
	// This allows a single flag to disable approval mode
	$master= checkApprovalMode();
	if(!$master) {
		$allowed= false;
		$pin='';
		$uname='';
	}

  $allow= array('allowed' => $allowed, 'pin' => $pin, 'allow_name' => $uname);
  return $allow;
}

function getApprovalUserName($thisUser)
{
  $uname = '';
  if(!$thisUser || $thisUser == '') return $uname;
  $uname = '** NOT ON FILE **';
  $sql= "SELECT * FROM users WHERE username=?";
  $ures= sqlStatement($sql, array($thisUser));
  if($ures) {
    $urow= sqlFetchArray($ures);
    $mi=' ';
    if($urow['mname']) $mi=' '.$urow['mname'].' ';
    if($urow) $uname= $urow['fname'].$mi.$urow['lname'];
  }
  return $uname;
}

// function checkAutoApproval()
// {
  // $auto_approve_on='';
  // $auto_user='';
  // $sql= "SELECT * FROM user_settings WHERE setting_user='".
      // "0' AND setting_label='wmt::approve_auto'";
  // $ures= sqlStatement($sql);
  // if($ures) {
    // $urow= sqlFetchArray($ures);
    // $auto_approve_on= $urow['setting_value'];
  // }
  // echo "Auto Approve: ".$auto_approve_on."<br />\n";
  // if($auto_approve_on) {
    // $sql= "SELECT * FROM user_settings WHERE setting_user='".
      // "0' AND setting_label='wmt::approve_auto_user'";
    // $ures= sqlStatement($sql);
    // if($ures) {
      // $urow= sqlFetchArray($ures);
      // $auto_user = $urow['setting_value'];
    // }
    // echo "Setting the User: ".$auto_user."<br />\n";
  // }
  // $auto = array('auto_on' => $auto_approve_on, 'auto_user' => $auto_user);
  // return $auto;
// }

function ApprovalSelect($thisField, $thisList, $exists, $default, $optional=0, $billed='b') {
	$bill_forms = getFormsByType(array('bill_form'));
  $rlist= sqlStatement("SELECT * FROM list_options WHERE " .
                   "list_id =? ORDER BY seq", array($thisList));
  echo "<option value=''";
  if(!$thisField && $exists) echo " selected='selected'";
  echo ">&nbsp;</option>";
  while ($rrow= sqlFetchArray($rlist)) {
		if(strtolower($rrow['option_id'] == 'a')) {
			if($optional && ($billed == 'b')) {
    		echo "<option value='" . $rrow['option_id'] . "'";
    		if(($thisField == $rrow['option_id']) || (!$exists && ($rrow['option_id'] == $default))) echo " selected='selected'";
    		echo ">" . $rrow['title'];
    		echo "</option>";
			}
		} else {
    	echo "<option value='" . $rrow['option_id'] . "'";
    	if(($thisField == $rrow['option_id']) || (!$exists && ($rrow['option_id'] == $default))) echo " selected='selected'";
    	echo ">" . $rrow['title'];
    	echo "</option>";
		}
  }
}

function loadUserPin($usr='') {
	if($usr == '') { return false; }
	$pin='';
	$sql = "SELECT id, username, setting_label, setting_value from users ".
				"LEFT JOIN user_settings on id=setting_user WHERE setting_label=".
				"'wmt::approve_pin' AND username=?";
	$fres=sqlStatement($sql, array($usr));
	$frow = sqlFetchArray($fres);
	$pin=trim($frow{'setting_value'});
	return($pin);
}
}

?>
