<?php
// Copyright (C) 2007 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This report shows past encounters with filtering and sorting.

require_once("../globals.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/billing.inc");
require_once("$srcdir/patient.inc");

$alertmsg = ''; // not used yet but maybe later

// For each sorting option, specify the ORDER BY argument.
//
$ORDERHASH = array(
  'doctor'  => 'lower(u.lname), lower(u.fname), fe.date',
  'patient' => 'lower(p.lname), lower(p.fname), fe.date',
  'time'    => 'fe.date, lower(u.lname), lower(u.fname)',
);

function bucks($amount) {
  if ($amount) printf("%.2f", $amount);
}

function show_doc_total($lastdocname, $doc_encounters) {
  if ($lastdocname) {
    echo " <tr>\n";
    echo "  <td class='detail'>$lastdocname</td>\n";
    echo "  <td class='detail' align='right'>$doc_encounters</td>\n";
    echo " </tr>\n";
  }
}

$form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
$form_to_date   = fixDate($_POST['form_to_date'], '');
$form_provider  = $_POST['form_provider'];
$form_details   = $_POST['form_details'] ? true : false;

$form_orderby = $ORDERHASH[$_REQUEST['form_orderby']] ?
  $_REQUEST['form_orderby'] : 'doctor';
$orderby = $ORDERHASH[$form_orderby];

// Get the info.
//
$query = "SELECT " .
  "fe.encounter, fe.date, fe.reason, " .
  "f.formdir, f.form_name, " .
  "p.fname, p.mname, p.lname, p.pid, " .
  "u.lname AS ulname, u.fname AS ufname, u.mname AS umname " .
  "FROM form_encounter AS fe, forms AS f " .
  "LEFT OUTER JOIN patient_data AS p ON p.pid = fe.pid " .
  "LEFT OUTER JOIN users AS u ON u.username = f.user " .
  "WHERE f.encounter = fe.encounter AND f.formdir = 'newpatient' ";
if ($form_to_date) {
  $query .= "AND fe.date >= '$form_from_date 00:00:00' AND fe.date <= '$form_to_date 23:59:59' ";
} else {
  $query .= "AND fe.date >= '$form_from_date 00:00:00' AND fe.date <= '$form_from_date 23:59:59' ";
}
if ($form_provider) {
  $query .= "AND f.user = '$form_provider' ";
}
$query .= "ORDER BY $orderby";

$res = sqlStatement($query);
?>
<html>
<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<title><?php xl('Encounters Report','e'); ?></title>
<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>

<style type="text/css">
 body       { font-family:sans-serif; font-size:10pt; font-weight:normal }
 .dehead    { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:bold;
              padding-left:3px; padding-right:3px; }
 .detail    { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:normal;
              padding-left:3px; padding-right:3px; }
</style>

<script type="text/javascript" src="../../library/textformat.js"></script>
<script type="text/javascript" src="../../library/dialog.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar_en.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>

<script LANGUAGE="JavaScript">

 var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

 function dosort(orderby) {
  var f = document.forms[0];
  f.form_orderby.value = orderby;
  f.submit();
  return false;
 }

 function refreshme() {
  document.forms[0].submit();
 }

</script>

</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>

<center>

<h2><?php xl('Encounters Report','e'); ?></h2>

<form method='post' name='theform' action='encounters_report.php'>

<table border='0' cellpadding='3'>

 <tr>
  <td>
   <?php xl('Provider','e'); ?>:
<?
 // Build a drop-down list of providers.
 //
 $query = "SELECT username, lname, fname FROM users WHERE " .
  "authorized = 1 ORDER BY lname, fname";
 $ures = sqlStatement($query);
 echo "   <select name='form_provider'>\n";
 echo "    <option value=''>-- All --\n";
 while ($urow = sqlFetchArray($ures)) {
  $provid = $urow['username'];
  echo "    <option value='$provid'";
  if ($provid == $_POST['form_provider']) echo " selected";
  echo ">" . $urow['lname'] . ", " . $urow['fname'] . "\n";
 }
 echo "   </select>\n";
?>

   &nbsp;<?php  xl('From','e'); ?>:
   <input type='text' name='form_from_date' id='form_from_date' size='10' value='<?php echo $form_from_date ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='Start date yyyy-mm-dd'>
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a date','e'); ?>'>

   &nbsp;<?php  xl('To','e'); ?>:
   <input type='text' name='form_to_date' id='form_to_date' size='10' value='<?php echo $form_to_date ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='End date yyyy-mm-dd'>
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_to_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a date','e'); ?>'>

   &nbsp;
   <input type='checkbox' name='form_details'<?php  if ($form_details) echo ' checked'; ?>>
   <?php  xl('Details','e'); ?>

   &nbsp;
   <input type='submit' name='form_refresh' value='<?php  xl('Refresh','e'); ?>'>
  </td>
 </tr>

 <tr>
  <td height="1">
  </td>
 </tr>

</table>

<table border='0' cellpadding='1' cellspacing='2'>

 <tr bgcolor="#dddddd">
<?php if ($form_details) { ?>
  <td class="dehead">
   <a href="nojs.php" onclick="return dosort('doctor')"
   <?php if ($form_orderby == "doctor") echo " style=\"color:#00cc00\"" ?>><?php  xl('Provider','e'); ?> </a>
  </td>
  <td class="dehead">
   <a href="nojs.php" onclick="return dosort('time')"
   <?php if ($form_orderby == "time") echo " style=\"color:#00cc00\"" ?>><?php  xl('Time','e'); ?></a>
  </td>
  <td class="dehead">
   <a href="nojs.php" onclick="return dosort('patient')"
   <?php if ($form_orderby == "patient") echo " style=\"color:#00cc00\"" ?>><?php  xl('Patient','e'); ?></a>
  </td>
  <td class="dehead">
   <?php  xl('Encounter','e'); ?>
  </td>
  <td class="dehead">
   <?php  xl('Form','e'); ?>
  </td>
  <td class="dehead">
   <?php  xl('Coding','e'); ?>
  </td>
<?php } else { ?>
  <td class="dehead"><?php  xl('Provider','e'); ?></td>
  <td class="dehead" align="right"><?php  xl('Encounters','e'); ?></td>
<?php } ?>
 </tr>
<?
if ($res) {
  $lastdocname = "";
  $doc_encounters = 0;
  while ($row = sqlFetchArray($res)) {
    $patient_id = $row['pid'];
    $docname  = $row['ulname'] . ', ' . $row['ufname'] . ' ' . $row['umname'];
    $errmsg  = "";
    if ($form_details) {
?>
 <tr bgcolor='<?php echo $bgcolor ?>'>
  <td class="detail" valign="top">
   <?php echo ($docname == $lastdocname) ? "" : $docname ?>
  </td>
  <td class="detail" valign="top">
   <?php echo substr($row['date'], 0, 10) ?>
  </td>
  <td class="detail" valign="top">
   <?php echo $row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname']; ?>
  </td>
  <td class="detail" valign="top">
   <?php echo $row['reason']; ?>
  </td>
  <td class="detail" valign="top">
<?php
      // Fetch and show all other forms for this encounter
      $encnames = '';
      $encarr = getFormByEncounter($patient_id, $row['encounter'],
        "formdir, user, form_name, form_id");
      foreach ($encarr as $enc) {
        if ($enc['formdir'] == 'newpatient') continue;
        if ($encnames) $encnames .= '<br />';
        $encnames .= $enc['form_name'];
      }
      echo $encnames;
?>
  </td>
  <td class="detail" valign="top">
<?php
      // Fetch and show coding for this encounter
      $coded = "";
      if ($billres = getBillingByEncounter($row['pid'], $row['encounter'])) {
        foreach ($billres as $billrow) {
          $title = addslashes($billrow['code_text']);
          $coded .= $billrow['code'] . ', ';
        }
        $coded = substr($coded, 0, strlen($coded) - 2);
      }
      echo $coded;
?>
  </td>
 </tr>
<?php
    } else {
      if ($docname != $lastdocname) {
        show_doc_total($lastdocname, $doc_encounters);
        $doc_encounters = 0;
      }
      ++$doc_encounters;
    }
    $lastdocname = $docname;
  }

  if (!$form_details) show_doc_total($lastdocname, $doc_encounters);
}
?>

</table>

<input type="hidden" name="form_orderby" value="<?php echo $form_orderby ?>" />

</form>
</center>
<script language='JavaScript'>
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});
<?php
  if ($alertmsg) {
    echo " alert('$alertmsg');\n";
  }
?>
</script>
</body>
</html>
