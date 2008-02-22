<?

 // Copyright (C) 2005-2007 Rod Roark <rod@sunsetsystems.com>

 //

 // This program is free software; you can redistribute it and/or

 // modify it under the terms of the GNU General Public License

 // as published by the Free Software Foundation; either version 2

 // of the License, or (at your option) any later version.



 include_once("../../globals.php");

 include_once("$srcdir/patient.inc");



 $info_msg = "";



 // If we are searching, search.

 //

 if ($_REQUEST['searchby'] && $_REQUEST['searchparm']) {

  $searchby = $_REQUEST['searchby'];

  $searchparm = $_REQUEST['searchparm'];



  if ($searchby == "Last") {

   $result = getPatientLnames("$searchparm","*");

  } elseif ($searchby == "Phone") {                  //(CHEMED) Search by phone number

   $result = getPatientPhone("$searchparm","*");

  } elseif ($searchby == "ID") {

   $result = getPatientId("$searchparm","*");

  } elseif ($searchby == "DOB") {

   $result = getPatientDOB("$searchparm","*");

  } elseif ($searchby == "SSN") {

   $result = getPatientSSN("$searchparm","*");

  }

 }

?>

<html>

<head>
<? html_header_show();?>

<title><?php xl('Patient Finder','e'); ?></title>

<link rel=stylesheet href='<? echo $css_header ?>' type='text/css'>



<style>

td { font-size:10pt; }

</style>



<script language="JavaScript">



 function selpid(pid, lname, fname, dob) {

  if (opener.closed || ! opener.setpatient)

   alert('The destination form was closed; I cannot act on your selection.');

  else

   opener.setpatient(pid, lname, fname, dob);

  window.close();

  return false;

 }



</script>



</head>



<body <?echo $top_bg_line;?>>

<?

?>

<form method='post' name='theform' action='find_patient_popup.php?'>

<center>



<table border='0' cellpadding='5' cellspacing='0'>



 <tr>

  <td height="1">

  </td>

 </tr>



 <tr bgcolor='#ddddff'>

  <td>

   <b>

   <?php xl('Search by:','e'); ?>

   <select name='searchby'>

    <option value="Last"><? xl ('Name','e'); ?></option>

    <!-- (CHEMED) Search by phone number -->

    <option value="Phone"<? if ($searchby == 'Phone') echo ' selected' ?>><? xl ('Phone','e'); ?></option>

    <option value="ID"<? if ($searchby == 'ID') echo ' selected' ?>><? xl ('ID','e'); ?></option>

    <option value="SSN"<? if ($searchby == 'SSN') echo ' selected' ?>><? xl ('SSN','e'); ?></option>

    <option value="DOB"<? if ($searchby == 'DOB') echo ' selected' ?>><? xl ('DOB','e'); ?></option>

   </select>

 <?php xl('for:','e'); ?>

   <input type='text' name='searchparm' size='12' value='<? echo $_REQUEST['searchparm']; ?>'

    title='<?php xl('If name, any part of lastname or lastname,firstname','e'); ?>'>

   &nbsp;

   <input type='submit' value='<?php xl('Search','e'); ?>'>

   <!-- &nbsp; <input type='button' value='<?php xl('Close','e'); ?>' onclick='window.close()' /> -->

   </b>

  </td>

 </tr>



 <tr>

  <td height="1">

  </td>

 </tr>



</table>



<? if (isset($result)) { ?>



<table border='0'>

 <tr>

  <td><b><? xl ('Name','e'); ?></b></td>

  <td><b><? xl ('Phone','e'); ?></b></td> <!-- (CHEMED) Search by phone number -->

  <td><b><? xl ('SS','e'); ?></b></td>

  <td><b><? xl ('DOB','e'); ?></b></td>

  <td><b><? xl ('ID','e'); ?></b></td>

 </tr>

<?

  foreach ($result as $iter) {

   $iterpid   = $iter['pid'];

   $iterlname = addslashes($iter['lname']);

   $iterfname = addslashes($iter['fname']);

   $itermname = addslashes($iter['mname']);

   $iterdob   = $iter['DOB'];

   $anchor = "<a href='' " .

    "onclick='return selpid($iterpid, \"$iterlname\", \"$iterfname\", \"$iterdob\")'>";

   echo " <tr>";

   echo "  <td>$anchor$iterlname, $iterfname $itermname</a></td>\n";

   echo "  <td>$anchor" . $iter['phone_home'] . "</a></td>\n"; //(CHEMED) Search by phone number

   echo "  <td>$anchor" . $iter['ss'] . "</a></td>\n";

   echo "  <td>$anchor" . $iter['DOB'] . "</a></td>\n";

   echo "  <td>$anchor" . $iter['pubpid'] . "</a></td>\n";

   if ($iter['genericname2'] == 'Billing') {

    echo "  <td><b><font color='red'>" . $iter['genericval2'] . "</font></b></td>\n";

   } else {

    echo "<td>&nbsp;</td>\n";

   }

   echo " </tr>";

  }

?>

</table>



<? } ?>



</center>

</form>

</body>

</html>

