<?php
 // Copyright (C) 2008 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 // This report lists referrals for a given date range.

 include_once("../globals.php");
 include_once("$srcdir/patient.inc");

 $from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
 $to_date   = fixDate($_POST['form_to_date'], date('Y-m-d'));
 $form_facility = isset($_POST['form_facility']) ? $_POST['form_facility'] : '';
?>
<html>
<head>
<?php html_header_show();?>
<title><?php xl('Referrals','e'); ?></title>

<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>

<script type="text/javascript" src="../../library/dialog.js"></script>
<script type="text/javascript" src="../../library/textformat.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar_en.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
<script language="JavaScript">

<?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

 var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

 // The OnClick handler for referral display.
 function show_referral(transid) {
  dlgopen('../patient_file/transaction/print_referral.php?transid=' + transid,
   '_blank', 550, 400);
  return false;
 }

</script>

<link rel='stylesheet' href='<?php echo $css_header ?>' type='text/css'>
<style type="text/css">

/* specifically include & exclude from printing */
@media print {
    #referreport_parameters {
        visibility: hidden;
        display: none;
    }
    #referreport_parameters_daterange {
        visibility: visible;
        display: inline;
    }
}

/* specifically exclude some from the screen */
@media screen {
    #referreport_parameters_daterange {
        visibility: hidden;
        display: none;
    }
}

#referreport_parameters {
    width: 100%;
    background-color: #ddf;
}
#referreport_parameters table {
    border: none;
    border-collapse: collapse;
}
#referreport_parameters table td {
    padding: 3px;
}

#referreport_results {
    width: 100%;
    margin-top: 10px;
}
#referreport_results table {
   border: 1px solid black;
   width: 98%;
   border-collapse: collapse;
}
#referreport_results table thead {
    display: table-header-group;
    background-color: #ddd;
}
#referreport_results table th {
    border-bottom: 1px solid black;
    font-size: 0.7em;
}
#referreport_results table td {
    padding: 1px;
    margin: 2px;
    border-bottom: 1px solid #eee;
    font-size: 0.7em;
}
.referreport_totals td {
    background-color: #77ff77;
    font-weight: bold;
}
</style>
</head>

<body class="body_top">

<center>

<h2><?php xl('Referrals','e'); ?></h2>

<div id="referreport_parameters_daterange">
<?php echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($form_to_date)); ?>
</div>

<div id="referreport_parameters">
<form name='theform' method='post' action='referrals_report.php'>
<table>
 <tr>
  <td>
<?php
 // Build a drop-down list of facilities.
 //
 $query = "SELECT id, name FROM facility ORDER BY name";
 $fres = sqlStatement($query);
 echo "   <select name='form_facility'>\n";
 echo "    <option value=''>-- All Facilities --\n";
 while ($frow = sqlFetchArray($fres)) {
  $facid = $frow['id'];
  echo "    <option value='$facid'";
  if ($facid == $form_facility) echo " selected";
  echo ">" . $frow['name'] . "\n";
 }
 echo "    <option value='0'";
 if ($form_facility === '0') echo " selected";
 echo ">-- Unspecified --\n";
 echo "   </select>\n";
?>
   &nbsp;<?php xl('From','e'); ?>:
   <input type='text' size='10' name='form_from_date' id='form_from_date'
    value='<?php echo $from_date ?>'
    title='<?php xl('yyyy-mm-dd','e'); ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a date','e'); ?>' />
   &nbsp;<?php xl('To','e'); ?>:
   <input type='text' size='10' name='form_to_date' id='form_to_date'
    value='<?php echo $to_date ?>'
    title='<?php xl('yyyy-mm-dd','e'); ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_to_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a date','e'); ?>' />
   &nbsp;
   <input type='submit' name='form_refresh' value=<?php xl('Refresh','e'); ?>>
   &nbsp;
   <input type='button' value='<?php xl('Print','e'); ?>' onclick='window.print()' />
  </td>
 </tr>
</table>
</div> <!-- end of parameters -->


<div id="referreport_results">
<table>
 <thead>
  <th> <?php xl('Refer To','e'); ?> </th>
  <th> <?php xl('Refer Date','e'); ?> </th>
  <th> <?php xl('Reply Date','e'); ?> </th>
  <th> <?php xl('Patient','e'); ?> </th>
  <th> <?php xl('ID','e'); ?> </th>
  <th> <?php xl('Reason','e'); ?> </th>
 </thead>
 <tbody>
<?php
 if ($_POST['form_refresh']) {
  $query = "SELECT t.id, t.refer_date, t.reply_date, t.body, " .
    "ut.organization, uf.facility_id, p.pubpid, " .
    "CONCAT(uf.fname,' ', uf.lname) AS referer_name, " .
    "CONCAT(p.fname,' ', p.lname) AS patient_name " .
    "FROM transactions AS t " .
    "LEFT OUTER JOIN patient_data AS p ON p.pid = t.pid " .
    "LEFT OUTER JOIN users AS ut ON ut.id = t.refer_to " .
    "LEFT OUTER JOIN users AS uf ON uf.id = t.refer_from " .
    "WHERE t.title = 'Referral' AND " .
    "t.refer_date >= '$from_date' AND t.refer_date <= '$to_date' " .
    "ORDER BY ut.organization, t.refer_date, t.id";

  // echo "<!-- $query -->\n"; // debugging
  $res = sqlStatement($query);

  while ($row = sqlFetchArray($res)) {
    // If a facility is specified, ignore rows that do not match.
    if ($form_facility !== '') {
      if ($form_facility) {
        if ($row['facility_id'] != $form_facility) continue;
      }
      else {
        if (!empty($row['facility_id'])) continue;
      }
    }
?>
 <tr>
  <td>
   <?php echo $row['organization'] ?>
  </td>
  <td>
   <a href='#' onclick="return show_referral(<?php echo $row['id']; ?>)">
   <?php echo $row['refer_date']; ?>&nbsp;
   </a>
  </td>
  <td>
   <?php echo $row['reply_date'] ?>
  </td>
  <td>
   <?php echo $row['patient_name'] ?>
  </td>
  <td>
   <?php echo $row['pubpid'] ?>
  </td>
  <td>
   <?php echo $row['body'] ?>
  </td>
 </tr>
<?php
  }
 }
?>
</tbody>
</table>
</div> <!-- end of results -->
</form>
</center>

<script language='JavaScript'>
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});
</script>

</body>
</html>
