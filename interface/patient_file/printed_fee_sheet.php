<?php
// Copyright (C) 2007-2008 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../globals.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/classes/Address.class.php");
require_once("$srcdir/classes/InsuranceCompany.class.php");

$lines_per_page = 55;
$lines_in_stats = 16;

// This file is optional.  You can create it to customize how the printed
// fee sheet looks, otherwise you'll get a mirror of your actual fee sheet.
//
if (file_exists("../../custom/fee_sheet_codes.php"))
  include_once ("../../custom/fee_sheet_codes.php");

$fontsize = trim($_REQUEST['fontsize']) + 0;
if (!$fontsize) $fontsize = 7;
$page_height = trim($_REQUEST['page_height']) + 0;
if (!$page_height) $page_height = 700;
$padding = 0;

// The $SBCODES table is a simple indexed array whose values are
// strings of the form "code|text" where code may be either a billing
// code or one of the following:
//
// *H - A main heading, where "text" is its title (to be centered).
// *G - Specifies a new category, where "text" is its name.
// *B - A borderless blank row.
// *C - Ends the current column and starts a new one.

// If $SBCODES is not provided, then manufacture it from the Fee Sheet.
//
if (empty($SBCODES)) {
  $SBCODES = array();
  $last_category = '';

  // Create entries based on the fee_sheet_options table.
  $res = sqlStatement("SELECT * FROM fee_sheet_options " .
    "ORDER BY fs_category, fs_option");
  while ($row = sqlFetchArray($res)) {
    $fs_category = $row['fs_category'];
    $fs_option   = $row['fs_option'];
    $fs_codes    = $row['fs_codes'];
    if($fs_category !== $last_category) {
      $last_category = $fs_category;
      $SBCODES[] = '*G|' . substr($fs_category, 1);
    }
    $SBCODES[] = " |" . substr($fs_option, 1);
  }

  // Create entries based on categories defined within the codes.
  $pres = sqlStatement("SELECT option_id, title FROM list_options " .
    "WHERE list_id = 'superbill' ORDER BY seq");
  while ($prow = sqlFetchArray($pres)) {
    $SBCODES[] = '*G|' . $prow['title'];
    $res = sqlStatement("SELECT code_type, code, code_text FROM codes " .
      "WHERE superbill = '" . $prow['option_id'] . "' " .
      "ORDER BY code_text");
    while ($row = sqlFetchArray($res)) {
      $SBCODES[] = $row['code'] . '|' . $row['code_text'];
    }
  }

  // Create one more group, for Products.
  if ($GLOBALS['sell_non_drug_products']) {
    $SBCODES[] = '*G|' . xl('Products');
    $tres = sqlStatement("SELECT dt.drug_id, dt.selector, d.name " .
      "FROM drug_templates AS dt, drugs AS d WHERE " .
      "d.drug_id = dt.drug_id " .
      "ORDER BY d.name, dt.selector, dt.drug_id");
    while ($trow = sqlFetchArray($tres)) {
      $tmp = $trow['selector'];
      if ($trow['name'] !== $trow['selector']) $tmp .= ' ' . $trow['name'];
      $SBCODES[] = $trow['drug_id'] . '|' . $tmp;
    }
  }

  // Extra stuff for the labs section.
  $SBCODES[] = '*G|' . xl('Additional Labs');
  $percol = intval((count($SBCODES) + 2) / 3);
  while (count($SBCODES) < $percol * 3) $SBCODES[] = '*B|';

  // Adjust lines per page to distribute lines evenly among the pages.
  $pages = intval(($percol + $lines_in_stats + $lines_per_page - 1) / $lines_per_page);
  $lines_per_page = intval($percol + $lines_in_stats + $pages - 1) / $pages;

  // Figure out page and column breaks.
  $pages = 1;
  $lines = $percol;
  $page_start_index = 0;
  while ($lines + $lines_in_stats > $lines_per_page) {
    ++$pages;
    $lines_this_page = $lines > $lines_per_page ? $lines_per_page : $lines;
    $lines -= $lines_this_page;
    array_splice($SBCODES, $lines_this_page*3 + $page_start_index, 0, '*C|');
    array_splice($SBCODES, $lines_this_page*2 + $page_start_index, 0, '*C|');
    array_splice($SBCODES, $lines_this_page*1 + $page_start_index, 0, '*C|');
    $page_start_index += $lines_this_page * 3 + 3;
  }
  array_splice($SBCODES, $lines*2 + $page_start_index, 0, '*C|');
  array_splice($SBCODES, $lines*1 + $page_start_index, 0, '*C|');
}
?>
<html>
<head>
<?php html_header_show(); ?>

<style>
body {
 font-family: sans-serif;
 font-weight: normal;
}
.bordertbl {
 width: 100%;
 border-style: solid;
 border-width: 0 0 1px 1px;
 border-spacing: 0;
 border-collapse: collapse;
 border-color: #999999;
}
td.toprow {
 height: 1px;
 padding: 0;
 border-style: solid;
 border-width: 0 0 0 0;
 border-color: #999999;
}
td.fsgroup {
 font-family: sans-serif;
 font-weight: bold;
 font-size: <?php echo $fontsize ?>pt;
 background-color: #cccccc;
 padding: <?php echo $padding ?>pt 2pt 0pt 2pt;
 border-style: solid;
 border-width: 1px 1px 0 0;
 border-color: #999999;
}
td.fshead {
 font-family: sans-serif;
 font-weight: bold;
 font-size: <?php echo $fontsize ?>pt;
 padding: <?php echo $padding ?>pt 2pt 0pt 2pt;
 border-style: solid;
 border-width: 1px 1px 0 0;
 border-color: #999999;
}
td.fscode {
 font-family: sans-serif;
 font-weight: normal;
 font-size: <?php echo $fontsize ?>pt;
 padding: <?php echo $padding ?>pt 2pt 0pt 2pt;
 border-style: solid;
 border-width: 1px 1px 0 0;
 border-color: #999999;
}
</style>

<?php

// Get the co-pay amount that is effective on the given date.
// Or if no insurance on that date, return -1.
//
function getCopay($patient_id, $encdate) {
  $tmp = sqlQuery("SELECT provider, copay FROM insurance_data " .
    "WHERE pid = '$patient_id' AND type = 'primary' " .
    "AND date <= '$encdate' ORDER BY date DESC LIMIT 1");
  if ($tmp['provider']) return sprintf('%01.2f', 0 + $tmp['copay']);
  return -1;
}

function genColumn($ix) {
  global $SBCODES;
  for ($imax = count($SBCODES); $ix < $imax; ++$ix) {
    $a = explode('|', $SBCODES[$ix], 2);
    $cmd = trim($a[0]);
    if ($cmd == '*C') { // column break
      return ++$ix;
    }
    if ($cmd == '*B') { // Borderless and empty
      echo "<tr><td colspan='5' class='fscode' style='border-width:0 1px 0 0;padding-top:1px;' nowrap>&nbsp;</td></tr>\n";
    }
    else if ($cmd == '*G') {
      // $title = htmlentities(strtoupper($a[1]));
      $title = htmlentities($a[1]);
      if (!$title) $title='&nbsp;';
      echo "<tr><td colspan='5' align='center' class='fsgroup' style='vertical-align:middle' nowrap>$title</td></tr>\n";
    }
    else if ($cmd == '*H') {
      // $title = htmlentities(strtoupper($a[1]));
      $title = htmlentities($a[1]);
      if (!$title) $title='&nbsp;';
      echo "<tr><td colspan='5' class='fshead' style='vertical-align:middle' nowrap>$title</td></tr>\n";
    }
    else {
      $title = htmlentities($a[1]);
      if (!$title) $title='&nbsp;';
      $b = explode(':', $cmd);
      echo "<tr>";
      echo "<td class='fscode' style='vertical-align:middle;width:14pt' nowrap>&nbsp;</td>\n";
      if (count($b) <= 1) {
        $code = $b[0];
        if (!$code) $code='&nbsp;';
        echo "<td class='fscode' style='vertical-align:middle' nowrap>$code</td>\n";
        echo "<td colspan='3' class='fscode' style='vertical-align:middle' nowrap>$title</td>\n";
      }
      else {
        echo "<td colspan='2' class='fscode' style='vertical-align:middle' nowrap>" . $b[0] . '/' . $b[1] . "</td>\n";
        echo "<td colspan='2' class='fscode' style='vertical-align:middle' nowrap>$title</td>\n";
      }
      echo "</tr>\n";
    }
  }
  return $ix;
}

$today = date('Y-m-d');

$alertmsg = ''; // anything here pops up in an alert box

// Get details for what we guess is the primary facility.
$frow = sqlQuery("SELECT * FROM facility " .
  "ORDER BY billing_location DESC, accepts_assignment DESC, id LIMIT 1");

// Get the patient's name and chart number.
$patdata = getPatientData($pid);

// This tracks our position in the $SBCODES array.
$cindex = 0;
?>

<title><?php echo htmlentities($frow['name']); ?></title>
<script type="text/javascript" src="../../library/dialog.js"></script>
<script language="JavaScript">

<?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

 // Process click on Print button.
 function printme() {
  var divstyle = document.getElementById('hideonprint').style;
  divstyle.display = 'none';
  window.print();
 }

</script>
</head>
<body bgcolor='#ffffff'>
<form name='theform' method='post' action='printed_fee_sheet.php'
 onsubmit='return top.restoreSession()'>
<center>

<?php while (--$pages >= 0) { ?>

<table class='bordertbl' cellspacing='0' cellpadding='0' width='100%'>
 <tr>
  <td valign='top'>
   <table border='0' cellspacing='0' cellpadding='0' width='100%' style='height:<?php echo $page_height ?>pt'>
    <tr>
     <td class='toprow' style='width:10%'></td>
     <td class='toprow' style='width:10%'></td>
     <td class='toprow' style='width:25%'></td>
     <td class='toprow' style='width:55%'></td>
    </tr>
<?php
  $cindex = genColumn($cindex); // Column 1
?>

<?php if ($pages == 0) { // if this is the last page ?>

    <tr>
     <td colspan='3' valign='top' class='fshead' style='height:50pt'>
      Patient:<br>
<?php
echo $patdata['fname'] . ' ' . $patdata['mname'] . ' ' . $patdata['lname'] . "<br>\n";
echo $patdata['street'] . "<br>\n";
echo $patdata['city'] . ', ' . $patdata['state'] . ' ' . $patdata['postal_code'] . "\n";
?>
     </td>
     <td valign='top' class='fshead'>
      DOB:<br><?php echo $patdata['DOB']; ?><br>
      ID:<br><?php echo $patdata['pubpid']; ?>
     </td>
    </tr>
    <tr>
     <td colspan='3' valign='top' class='fshead' style='height:25pt'>
      Doctor:&nbsp;
      <?php // Doctor name here ?>
     </td>
     <td valign='top' class='fshead'>
      Reason:&nbsp;
      <?php // Encounter reason here ?>
     </td>
    </tr>
    <tr>
     <td colspan='4' valign='top' class='fshead' style='height:25pt'>
<?php
if (empty($GLOBALS['ippf_specific'])) {
  echo "Insurance:\n";
  foreach (array('primary','secondary','tertiary') as $instype) {
    $query = "SELECT * FROM insurance_data WHERE " .
      "pid = '$pid' AND type = '$instype' " .
      "ORDER BY date DESC LIMIT 1";
    $row = sqlQuery($query);
    if ($row['provider']) {
      $icobj = new InsuranceCompany($row['provider']);
      $adobj = $icobj->get_address();
      $insco_name = trim($icobj->get_name());
      if ($instype != 'primary') echo ",";
      if ($insco_name) {
        echo "&nbsp;$insco_name";
      } else {
        echo "&nbsp;<font color='red'><b>Missing Name</b></font>";
      }
    }
  }
}
else {
  // IPPF wants a visit date box with the current date in it.
  echo "Visit date:<br>\n";
  echo date('Y-m-d') . "\n";
}
?>
     </td>
    </tr>
    <tr>
     <td colspan='4' valign='top' class='fshead' style='height:25pt'>
      Prior balance:<br>
     </td>
    </tr>
    <tr>
     <td colspan='4' valign='top' class='fshead' style='height:25pt'>
      Today's charges:<br>
     </td>
    </tr>
    <tr>
     <td colspan='4' valign='top' class='fshead' style='height:25pt'>
      Today's payment:<br>
     </td>
    </tr>
    <tr>
     <td colspan='4' valign='top' class='fshead' style='height:25pt'>
      Notes:<br>
     </td>
    </tr>

<?php } // end if last page ?>

   </table>
  </td>
  <td valign='top'>
   <table border='0' cellspacing='0' cellpadding='0' width='100%' style='height:<?php echo $page_height ?>pt'>
    <tr>
     <td class='toprow' style='width:10%'></td>
     <td class='toprow' style='width:10%'></td>
     <td class='toprow' style='width:25%'></td>
     <td class='toprow' style='width:55%'></td>
    </tr>
<?php
  $cindex = genColumn($cindex); // Column 2
?>

<?php if ($pages == 0) { // if this is the last page ?>

    <tr>
     <td colspan='4' valign='top' class='fshead' style='height:100pt'>
      Additional procedures:<br>
     </td>
    </tr>
    <tr>
     <td colspan='4' valign='top' class='fshead' style='height:100pt'>
      Diagnosis:<br>
     </td>
    </tr>

<?php } // end if last page ?>

   </table>
  </td>
  <td valign='top'>
   <table border='0' cellspacing='0' cellpadding='0' width='100%' style='height:<?php echo $page_height ?>pt'>
    <tr>
     <td class='toprow' style='width:10%'></td>
     <td class='toprow' style='width:10%'></td>
     <td class='toprow' style='width:25%'></td>
     <td class='toprow' style='width:55%'></td>
    </tr>
<?php
  $cindex = genColumn($cindex); // Column 3
?>

<?php if ($pages == 0) { // if this is the last page ?>

    <tr>
     <td valign='top' colspan='4' class='fshead' style='height:150pt;border-width:0 1px 0 0'>
      &nbsp;
     </td>
    </tr>
    <tr>
     <td valign='top' colspan='4' class='fshead' style='height:50pt'>
      M.D. Signature:<br>
     </td>
    </tr>

<?php } // end if last page ?>

   </table>
  </td>
 </tr>

</table>

<?php } // end while ?>

<div id='hideonprint'>
<p>
<input type='button' value='<?php xl('Print','e'); ?>' onclick='printme()' />
&nbsp;&nbsp;Font Size in Points:
<input type='text' name='fontsize' size='2' maxlength='5' value='<?php echo $fontsize ?>' />
&nbsp;&nbsp;Page Height in Points:
<input type='text' name='page_height' size='3' maxlength='4' value='<?php echo $page_height ?>' />
&nbsp;&nbsp;
<input type='submit' name='form_submit' value='Refresh' />
</div>

</form>
</center>
</body>
</html>
