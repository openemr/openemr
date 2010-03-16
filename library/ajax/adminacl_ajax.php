<?php
// Copyright (C) 2007 Brady Miller <brady@sparmy.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// 
// This file contains functions that service ajax requests for 
// ACL(php-gacl) administration within OpenEMR. All returns are 
// done via xml.
//
// Important - Ensure that display_errors=Off in php.ini settings.
//
include_once("../../interface/globals.php");
include_once("$srcdir/acl.inc");

header("Content-type: text/xml");
header("Cache-Control: no-cache");

//initiate error array
$error = array();

//PENDING, need to clean this up on client side
//ensure user has proper access
if (!acl_check('admin', 'acl')) {
echo error_xml(xl('ACL Administration Not Authorized'));
exit;
}
//ensure php is installed
if (!isset($phpgacl_location)) {
echo error_xml(xl('PHP-gacl is not installed'));
exit;
}

//Display red alert if Emergency Login ACL is activated for a user.
if($_POST["action"] == "add"){
  if (in_array("Emergency Login",$_POST["selection"])) {
      array_push($error, (xl('Emergency Login ACL is chosen. The user is still in active state, please de-activate the user and activate the same when required during emergency situations. Visit Administration->Users for activation or de-activation.') ));
   }
 }

//PROCESS USERNAME REQUESTS
if ($_POST["control"] == "username") {
 if ($_POST["action"] == "list") {
  //return username list with alert if user is not joined to group
  echo username_listings_xml($error);
 }
}


//PROCESS MEMBERSHIP REQUESTS
if ($_POST["control"] == "membership") {
 if ($_POST["action"] == "list") {
  //return membership data
  echo user_group_listings_xml($_POST["name"], $error);
 }
    
 if ($_POST["action"] == "add") {     
  if ($_POST["selection"][0] == "null") { 
   //no selection, return soft error, and just return membership data
   array_push($error, (xl('No group was selected') . "!"));
   echo user_group_listings_xml($_POST["name"], $error);
   exit;
  }     
  //add the group, then return updated membership data
  add_user_aros($_POST["name"], $_POST["selection"]);
  echo user_group_listings_xml($_POST["name"], $error);
 }
    
 if ($_POST["action"] == "remove") {
  if ($_POST["selection"][0] == "null") {
   //no selection, return soft error, and just return membership data
   array_push($error, (xl('No group was selected') . "!"));
   echo user_group_listings_xml($_POST["name"], $error);
   exit;
  }
  if (($_POST["name"] == "admin") && in_array("Administrators",$_POST["selection"])) {
   //unable to remove admin user from administrators group, process remove,
   // send soft error, then return data
   array_push($error, (xl('Not allowed to remove the admin user from the Administrators group') . "!"));
   remove_user_aros($_POST["name"], $_POST["selection"]);
   echo user_group_listings_xml($_POST["name"], $error);
   exit;
  }   
  //remove the group(s), then return updated membership data
  remove_user_aros($_POST["name"], $_POST["selection"]);
  echo user_group_listings_xml($_POST["name"], $error);
 }   
}


//PROCESS ACL REQUESTS
if ($_POST["control"] == "acl") {
 if ($_POST["action"] == "list") {
  //return acl titles with return values
  echo acl_listings_xml($error);
 }
    
 if ($_POST["action"] == "add") {
  //validate form data
  $form_error = false;
  if (empty($_POST["title"])) {
   $form_error = true;
   array_push($error, ("title_" . xl('Need to enter title') . "!"));
  }
  else if (!ctype_alpha(str_replace(' ', '', $_POST["title"]))) {
   $form_error = true;
   array_push($error, ("title_" . xl('Please only use alphabetic characters') . "!"));
  }
  else if (acl_exist($_POST["title"], FALSE, $_POST["return_value"])) {
   $form_error = true;
   array_push($error, ("title_" . xl('Already used, choose another title') . "!"));
  }
  if (empty($_POST["identifier"])) {
   $form_error = true;
   array_push($error, ("identifier_" . xl('Need to enter identifier') . "!"));
  }
  else if (!ctype_alpha($_POST["identifier"])) {   
   $form_error = true;
   array_push($error, ("identifier_" . xl('Please only use alphabetic characters with no spaces') . "!"));
  }
  else if (acl_exist(FALSE, $_POST["identifier"], $_POST["return_value"])) {
   $form_error = true;
   array_push($error, ("identifier_" . xl('Already used, choose another identifier') . "!"));
  }
  if (empty($_POST["return_value"])) {
   $form_error = true;
   array_push($error, ("return_" . xl('Need to enter a Return Value') . "!"));
  }
  if (empty($_POST["description"])) {
   $form_error = true;
   array_push($error, ("description_" . xl('Need to enter a description') . "!"));
  }
  else if (!ctype_alpha(str_replace(' ', '', $_POST["description"]))) {
   $form_error = true;
   array_push($error, ("description_" . xl('Please only use alphabetic characters') . "!"));
  }
  //process if data is valid
  if (!$form_error) {   
   acl_add($_POST["title"], $_POST["identifier"], $_POST["return_value"], $_POST["description"]);
   echo "<?xml version=\"1.0\"?>\n" .
    "<response>\n" .
    "\t<success>SUCCESS</success>\n" .
    "</response>\n";
  }
  else { //$form_error = true, so return errors
   echo error_xml($error);
  }
 }
    
 if ($_POST["action"] == "remove") {
  //validate form data
  $form_error = false;
  if (empty($_POST["title"])) {
   $form_error = true;
   array_push($error, ("aclTitle_" . xl('Need to enter title') . "!"));
  }
  if ($_POST["title"] == "Administrators") {
   $form_error = true;
   array_push($error, ("aclTitle_" . xl('Not allowed to delete the Administrators group') . "!"));
  }
  //process if data is valid
  if (!$form_error) {
   acl_remove($_POST["title"], $_POST["return_value"]);
   echo "<?xml version=\"1.0\"?>\n" .
    "<response>\n" .
    "\t<success>SUCCESS</success>\n" .
    "</response>\n";
  }
  else { //$form_error = true, so return errors
   echo error_xml($error);
  }
 }
    
 if ($_POST["action"] == "returns") {
  //simply return all the possible acl return_values
  echo return_values_xml($error);
 }
}


//PROCESS ACO REQUESTS
if ($_POST["control"] == "aco") {
 if ($_POST["action"] == "list") {
  //send acl data
  echo aco_listings_xml($_POST["name"], $_POST["return_value"], $error); 
 }
 
 if ($_POST["action"] == "add") {
  if ($_POST["selection"][0] == "null") {
   //no selection, return soft error, and just return data
   array_push($error, (xl('Nothing was selected') . "!"));
   echo aco_listings_xml($_POST["name"], $_POST["return_value"], $error);
   exit;
  }
  //add the aco, then return updated membership data
  acl_add_acos($_POST["name"], $_POST["return_value"], $_POST["selection"]);
  echo aco_listings_xml($_POST["name"], $_POST["return_value"], $error);
 }
    
 if ($_POST["action"] == "remove") {
  if ($_POST["selection"][0] == "null") {
   //no selection, return soft error, and just return data
   array_push($error, (xl('Nothing was selected') . "!"));
   echo aco_listings_xml($_POST["name"], $_POST["return_value"], $error);
   exit;
  }
  if ($_POST["name"] == "Administrators") {
   //will not allow removal of acos from Administrators ACL
   array_push($error, (xl('Not allowed to inactivate anything from the Administrators ACL') . "!"));
   echo aco_listings_xml($_POST["name"], $_POST["return_value"], $error);
   exit;
  }
  //remove the acos, then return updated data
  acl_remove_acos($_POST["name"], $_POST["return_value"], $_POST["selection"]);
  echo aco_listings_xml($_POST["name"], $_POST["return_value"], $error);
 }
}


//
// Returns username listings via xml message.
// It will also include alert if user is not joined
// to a group yet
//   $err = error strings (array)
//
function username_listings_xml($err) {
 $message = "<?xml version=\"1.0\"?>\n" .
  "<response>\n";  
 $res = sqlStatement("select * from users where username != '' order by username");
 for ($iter = 0;$row = sqlFetchArray($res);$iter++)
  $result4[$iter] = $row;
 foreach ($result4 as $iter) {
  $message .= "\t<user>\n" .
    "\t\t<username>" . $iter{"username"} . "</username>\n";
  $username_acl_groups = acl_get_group_titles($iter{"username"});
  if (!$username_acl_groups) {
   //not joined to any group, so send alert
   $message .= "\t\t<alert>no membership</alert>\n";
  }
  $message .= "\t</user>\n";
 }
 if (isset($err)) {
  foreach ($err as $value) {
   $message .= "\t<error>" . $value . "</error>\n";
  }
 }
 $message .= "</response>\n";
 return $message;
}

//
// Returns user group listings(active and inactive lists) 
// via xml message.
//   $username = username
//   $err = error strings (array)
//
function user_group_listings_xml($username, $err) {
 $list_acl_groups = acl_get_group_title_list();
 $username_acl_groups = acl_get_group_titles($username);
  //note acl_get_group_titles() returns a 0 if user in no groups
    
 $message = "<?xml version=\"1.0\"?>\n" .
  "<response>\n" .
  "\t<inactive>\n";
 foreach ($list_acl_groups as $value) {
  if ((!$username_acl_groups) || (!(in_array($value, $username_acl_groups)))) {
   $message .= "\t\t<group>\n";
   $message .= "\t\t\t<value>" . $value . "</value>\n";
      
   // Modified 6-2009 by BM - Translate gacl group name if applicable   
   $message .= "\t\t\t<label>" . xl_gacl_group($value) . "</label>\n";
      
   $message .= "\t\t</group>\n";
  }
 }
 $message .= "\t</inactive>\n" .
  "\t<active>\n";
 if ($username_acl_groups) {
  foreach ($username_acl_groups as $value) {
   $message .= "\t\t<group>\n";
   $message .= "\t\t\t<value>" . $value . "</value>\n";

   // Modified 6-2009 by BM - Translate gacl group name if applicable
   $message .= "\t\t\t<label>" . xl_gacl_group($value) . "</label>\n";
      
   $message .= "\t\t</group>\n";
  }
 }
 $message .= "\t</active>\n";
 if (isset($err)) {
  foreach ($err as $value) {   
   $message .= "\t<error>" . $value . "</error>\n";
  }
 }
 $message .= "</response>\n";
 return $message;
}

//
// Returns acl listings(including return value) via xml message.
//   $err = error strings (array)
//
function acl_listings_xml($err) {    
 global $phpgacl_location;
 include_once("$phpgacl_location/gacl_api.class.php");
 $gacl = new gacl_api();
 
 $message = "<?xml version=\"1.0\"?>\n" .
  "<response>\n"; 
 foreach (acl_get_group_title_list() as $value) {
  $acl_id = $gacl->search_acl(FALSE, FALSE, FALSE, FALSE, $value, FALSE, FALSE, FALSE, FALSE);
  foreach ($acl_id as $value2) {
   $acl = $gacl->get_acl($value2);
   $ret = $acl["return_value"];
   $note = $acl["note"];
      
   // Modified 6-2009 by BM - Translate gacl group name if applicable
   //                         Translate return value
   //                         Translate description
   $message .= "\t<acl>\n" .
    "\t\t<value>" . $value . "</value>\n" .
    "\t\t<title>" . xl_gacl_group($value) . "</title>\n" .
    "\t\t<returnid>" . $ret  . "</returnid>\n" .
    "\t\t<returntitle>" . xl($ret)  . "</returntitle>\n" .
    "\t\t<note>" . xl($note)  . "</note>\n" .
    "\t</acl>\n";
  }
 }
 if (isset($err)) {
  foreach ($err as $value) {
   $message .= "\t<error>" . $value . "</error>\n";
  }
 }
 $message .= "</response>\n";
 return $message;
}

//
// Return aco listings by sections(active and inactive lists)
// via xml message. 
//   $group = group title (string)
//   $return_value = return value (string)
//   $err = error strings (array)
//
function aco_listings_xml($group, $return_value, $err) {
 global $phpgacl_location;
 include_once("$phpgacl_location/gacl_api.class.php");
 $gacl = new gacl_api();
 
 //collect and sort all aco objects
 $list_aco_objects = $gacl->get_objects(NULL, 0, 'ACO');
 foreach ($list_aco_objects as $key => $value) {
  asort($list_aco_objects[$key]);
 }
    
 //collect aco objects within the specified acl(already sorted)
 $acl_id = $gacl->search_acl(FALSE, FALSE, FALSE, FALSE, $group, FALSE, FALSE, FALSE, $return_value);
 $acl = $gacl->get_acl($acl_id[0]);
 $active_aco_objects = $acl["aco"];
      
 $message = "<?xml version=\"1.0\"?>\n" .
  "<response>\n" .
  "\t<inactive>\n";
 foreach ($list_aco_objects as $key => $value) {
  $counter = 0;   
  foreach($list_aco_objects[$key] as $value2) {
   if (!array_key_exists($key,$active_aco_objects) || !in_array($value2, $active_aco_objects[$key])) {
       
    if ($counter == 0) {
     $counter = $counter + 1;
     $aco_section_data = $gacl->get_section_data($key, 'ACO');
     $aco_section_title = $aco_section_data[3];
	
     // Modified 6-2009 by BM - Translate gacl aco section name
     $message .= "\t\t<section>\n" .
     "\t\t\t<name>" . xl($aco_section_title) . "</name>\n";
	
    }
    $aco_id = $gacl->get_object_id($key, $value2,'ACO');
    $aco_data = $gacl->get_object_data($aco_id, 'ACO');
    $aco_title = $aco_data[0][3];
    $message .= "\t\t\t<aco>\n";
       
    // Modified 6-2009 by BM - Translate gacl aco name       
    $message .= "\t\t\t\t<title>" . xl($aco_title) . "</title>\n";
       
    $message .= "\t\t\t\t<id>" . $aco_id . "</id>\n";
    $message .= "\t\t\t</aco>\n";   
   }
  }
  if ($counter != 0) {
   $message .= "\t\t</section>\n";
  }
 }
 $message .= "\t</inactive>\n" .
  "\t<active>\n";  
 foreach ($active_aco_objects as $key => $value) {
  $aco_section_data = $gacl->get_section_data($key, 'ACO');
  $aco_section_title = $aco_section_data[3];
     
  // Modified 6-2009 by BM - Translate gacl aco section name
  $message .= "\t\t<section>\n" .
   "\t\t\t<name>" . xl($aco_section_title) . "</name>\n";
     
  foreach($active_aco_objects[$key] as $value2) {
   $aco_id = $gacl->get_object_id($key, $value2,'ACO');
   $aco_data = $gacl->get_object_data($aco_id, 'ACO');
   $aco_title = $aco_data[0][3];
   $message .= "\t\t\t<aco>\n";
      
   // Modified 6-2009 by BM - Translate gacl aco name
   $message .= "\t\t\t\t<title>" . xl($aco_title) . "</title>\n";
      
   $message .= "\t\t\t\t<id>" . $aco_id . "</id>\n";
   $message .= "\t\t\t</aco>\n";
  }
  $message .= "\t\t</section>\n";
 }    
 $message .= "\t</active>\n";    
 if (isset($err)) {
  foreach ($err as $value) {
   $message .= "\t<error>" . $value . "</error>\n";
  }
 }
 $message .= "</response>\n";
 return $message;
}

//
// Returns listing of all possible return values via xml message.
//   $err = error strings (array)
//
function return_values_xml($err) {
 global $phpgacl_location;
 include_once("$phpgacl_location/gacl_api.class.php");
 $gacl = new gacl_api();
 $returns = array();
 
 $message = "<?xml version=\"1.0\"?>\n" .
  "<response>\n";
 foreach(acl_get_group_title_list() as $value) {
  $acl_id = $gacl->search_acl(FALSE, FALSE, FALSE, FALSE, $value, FALSE, FALSE, FALSE, FALSE);
   foreach($acl_id as $value2){
    $acl = $gacl->get_acl($value2);
    $ret = $acl["return_value"];
    if (!in_array($ret, $returns)) {
	
     // Modified 6-2009 by BM - Translate return value	
     $message .= "\t<return>\n";
     $message .= "\t\t<returnid>" . $ret  . "</returnid>\n";
     $message .= "\t\t<returntitle>" . xl($ret)  . "</returntitle>\n"; 
     $message .= "\t</return>\n";
	
     array_push($returns, $ret);
    }
   }
 }
 if (isset($err)) {
  foreach ($err as $value) {
   $message .= "\t<error>" . $value . "</error>\n";
   }
 }
 $message .= "</response>\n";
 return $message;
}

//
// Returns error string(s) via xml   
//   $err = error (string or array)
//
function error_xml($err) {
 $message = "<?xml version=\"1.0\"?>\n" .
  "<response>\n";
 if (is_array($err)){
  foreach ($err as $value){
   $message .= "\t<error>" . $value . "</error>\n";
  }
 }
 else {  
 $message .= "\t<error>" . $err . "</error>\n";
 }  
 $message .= "</response>\n";
 return $message;
}
?>
