<?
 // Copyright (C) 2005 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 /////////////////////////////////////////////////////////////////////
 // This program invokes ReferCare to create a new referral.
 /////////////////////////////////////////////////////////////////////

 include_once("../interface/globals.php");

 $query = "SELECT * FROM patient_data WHERE pid = '$pid' LIMIT 1";
 $row = sqlFetchArray(sqlStatement($query));

 $phone = "";
 if ($row['phone_home']) {
  $phone .= "Home: " . $row['phone_home'];
 }
 if ($row['phone_biz']) {
  if ($phone) $phone .= "; ";
  $phone .= "Work: " . $row['phone_biz'];
 }
 if ($row['phone_cell']) {
  if ($phone) $phone .= "; ";
  $phone .= "Cell: " . $row['phone_cell'];
 }
 if ($row['phone_contact']) {
  if ($phone) $phone .= "; ";
  $phone .= "Contact: " . $row['phone_contact'];
 }

 $rcurl = "https://www.refercare.org/edit_referral.php?chart=$pid";
 $rcurl .= "&lastname="    . htmlentities(trim($row['lname']), ENT_QUOTES);
 $rcurl .= "&firstname="   . htmlentities(trim($row['fname']), ENT_QUOTES);
 $rcurl .= "&ssn="         . htmlentities(trim($row['ss'])   , ENT_QUOTES);
 $rcurl .= "&dob="         . htmlentities(trim($row['DOB'])  , ENT_QUOTES);
 $rcurl .= "&contactinfo=" . htmlentities(trim($phone)       , ENT_QUOTES);
?>
<html>
<head>
<? html_header_show();?>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<title>Create a Referral</title>
</head>
<body>
<script language="JavaScript">
 window.location.href='<?php echo $rcurl ?>';
</script>
</body>
</html>
