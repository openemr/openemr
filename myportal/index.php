<?php
// +-----------------------------------------------------------------------------+ 
// Copyright (C) 2011 Z&H Consultancy Services Private Limited <sam@zhservices.com>
//
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
//
// A copy of the GNU General Public License is included along with this program:
// openemr/interface/login/GnuGPL.html
// For more information write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// 
// Author:   Eldho Chacko <eldho@zhservices.com>
//           Jacob T Paul <jacob@zhservices.com>
//           Paul Simon   <paul@zhservices.com>
//
// +------------------------------------------------------------------------------+

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//
if (!extension_loaded('soap')) {
   die("PLEASE ENABLE SOAP EXTENSION");
}
require_once("../interface/globals.php");
 $emr_path = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
 $emrpatharr = explode("/myportal",$emr_path);
 $emr_path = (!empty($_SERVER['HTTPS'])) ? "https://".$emrpatharr[0] : "http://".$emrpatharr[0];
 $row = sqlQuery("SELECT fname,lname FROM users WHERE id=?",array($_SESSION['authId']));
 sqlStatement("DELETE FROM audit_details WHERE audit_master_id IN(SELECT id FROM audit_master WHERE type=5 AND created_time<'".date("Y-m-d H:m",(strtotime(date("Y-m-d H:m")-7200))).":00')");
 sqlStatement("DELETE FROM audit_master WHERE type=5 AND created_time<'".date("Y-m-d H:m",(strtotime(date("Y-m-d H:m")-7200))).":00'");
 
 function md5_pass($length = 8)
 {
  $randkey = substr(md5(rand().rand()), 0, $length);
  $res = sqlStatement("SELECT * FROM audit_master AS am LEFT OUTER JOIN audit_details AS ad ON ad.audit_master_id=am.id WHERE type=5 AND field_value=?",array($randkey));
  if(sqlNumRows($res)){
  md5_pass();
  }
  else{
  $grpID = sqlInsert("INSERT INTO audit_master SET type=5");
  sqlStatement("INSERT INTO audit_details SET field_value=? , audit_master_id=?",array($randkey,$grpID));
  return $randkey;
  }
 }
 for($i=1;$i<=5;$i++){//some times php is continuing without getting the return value from the function md5_pass()
   if(!$randkey){
     if($i>1)
     sleep(1);
     $randkey = md5_pass();
   }
   else{
     break;
   }
 }
?>
<html>
<head>
    <?php require_once($GLOBALS['fileroot'].'/library/sha1.js');?>
<script type="text/javascript">
 function getshansubmit(){
   	randkey = "<?php echo $randkey;?>";
	pass = SHA1(document.portal.pass.value+"<?php echo gmdate('Y-m-d H');?>"+randkey);
	document.portal.pwd.value=pass;
	document.portal.randkey.value=randkey;
	document.forms[0].submit();
 }
 
</script>
</head>
<title><?php echo xlt('Redirection');?></title>
<body onload="getshansubmit()">
    <form name="portal" method="post" action="<?php echo htmlspecialchars($GLOBALS['portal_offsite_address'],ENT_QUOTES);?>">
    <input type="hidden" name="user" value="<?php echo htmlspecialchars($GLOBALS['portal_offsite_username'],ENT_QUOTES);?>">
    <input type="hidden" name="emr_path" value="<?php echo htmlspecialchars($emr_path,ENT_QUOTES);?>">
    <input type="hidden" name="emr_site" value="<?php echo htmlspecialchars($_SESSION['site_id'],ENT_QUOTES);?>">
    <input type="hidden" name="uname" value="<?php echo htmlspecialchars($row['fname']." ".$row['lname'],ENT_QUOTES);?>">
    <input type="hidden" name="pass" value="<?php echo htmlspecialchars($GLOBALS['portal_offsite_password'],ENT_QUOTES);?>">
	<input type="hidden" name="randkey" value="">
	<input type="hidden" name="pwd" value="">
    </form>
</body>
</html>