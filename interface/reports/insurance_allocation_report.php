<?php
 // This module shows relative insurance usage by unique patients
 // that are seen within a given time period.  Each patient that had
 // a visit is counted only once, regardless of how many visits.

 include_once("../globals.php");
 include_once("../../library/patient.inc");
 include_once("../../library/acl.inc");

 // Might want something different here.
 //
 // if (! acl_check('acct', 'rep')) die("Unauthorized access.");

 $from_date = fixDate($_POST['form_from_date']);
 $to_date   = fixDate($_POST['form_to_date'], date('Y-m-d'));

if ($_POST['form_csvexport']) {
  header("Pragma: public");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Content-Type: application/force-download");
  header("Content-Disposition: attachment; filename=insurance_distribution.csv");
  header("Content-Description: File Transfer");
  // CSV headers:
  if (true) {
    echo '"Insurance",';
    echo '"Charges",';
    echo '"Visits",';
    echo '"Patients",';
    echo '"Pt Pct"' . "\n";
  }
}
else {
?>
<html>
<head>
<? html_header_show();?>
<title><? xl('Patient Insurance Distribution','e'); ?></title>
<script type="text/javascript" src="../../library/overlib_mini.js"></script>
<script type="text/javascript" src="../../library/calendar.js"></script>
<script type="text/javascript" src="../../library/textformat.js"></script>
<script language="JavaScript">
 var mypcc = '<? echo $GLOBALS['phone_country_code'] ?>';
</script>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>

<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<center>

<h2><? xl('Patient Insurance Distribution','e'); ?></h2>

<form name='theform' method='post' action='insurance_allocation_report.php'>

<table border='0' cellpadding='3'>

 <tr>
  <td>
   <?php xl('From','e'); ?>:
   <input type='text' name='form_from_date' size='10' value='<?php echo $from_date ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
   <a href="javascript:show_calendar('theform.form_from_date')"
    title=".xl('Click here to choose a date')"
    ><img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22' border='0'></a>
   &nbsp;<? xl('To','e'); ?>:
   <input type='text' name='form_to_date' size='10' value='<? echo $to_date ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
   <a href="javascript:show_calendar('theform.form_to_date')"
    title=".xl('Click here to choose a date')"
    ><img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22' border='0'></a>
   &nbsp;
   <input type='submit' name='form_refresh' value='<?php xl('Refresh','e'); ?>'>
   &nbsp;
   <input type='submit' name='form_csvexport' value='<?php xl('Export as CSV','e'); ?>' />
  </td>
 </tr>

 <tr>
  <td height="1">
  </td>
 </tr>

</table>

<table border='0' cellpadding='1' cellspacing='2' width='98%'>

 <tr bgcolor="#dddddd">
  <td class="dehead">
   <?php xl('Primary Insurance','e'); ?>
  </td>
  <td class='dehead' align='right'>
   <?php xl('Charges','e'); ?>
  </td>
  <td class='dehead' align='right'>
   <?php xl('Visits','e'); ?>
  </td>
  <td class='dehead' align='right'>
   <?php xl('Patients','e'); ?>
  </td>
  <td class='dehead' align='right'>
   <?php xl('Pt %','e'); ?>
  </td>
 </tr>
<?php
} // end not export
if ($_POST['form_refresh'] || $_POST['form_csvexport']) {

  $from_date = fixDate($_POST['form_from_date']);
  $to_date   = fixDate($_POST['form_to_date'], date('Y-m-d'));

  $query = "SELECT b.pid, b.encounter, SUM(b.fee) AS charges, " .
    "MAX(fe.date) AS date " .
    "FROM form_encounter AS fe, billing AS b " .
    "WHERE fe.date >= '$from_date' AND fe.date <= '$to_date' " .
    "AND b.pid = fe.pid AND b.encounter = fe.encounter " .
    "AND b.code_type != 'COPAY' AND b.activity > 0 AND b.fee != 0 " .
    "GROUP BY b.pid, b.encounter ORDER BY b.pid, b.encounter";

  $res = sqlStatement($query);
  $insarr = array();
  $prev_pid = 0;
  $patcount = 0;

  while ($row = sqlFetchArray($res)) {
    $patient_id = $row['pid'];
    $encounter_date = $row['date'];
    $irow = sqlQuery("SELECT insurance_companies.name " .
      "FROM insurance_data, insurance_companies WHERE " .
      "insurance_data.pid = $patient_id AND " .
      "insurance_data.type = 'primary' AND " .
      "insurance_data.date <= '$encounter_date' AND " .
      "insurance_companies.id = insurance_data.provider " .
      "ORDER BY insurance_data.date DESC LIMIT 1");
    $plan = $irow['name'] ? $irow['name'] : '-- No Insurance --';
    $insarr[$plan]['visits'] += 1;
    $insarr[$plan]['charges'] += sprintf('%0.2f', $row['charges']);
    if ($patient_id != $prev_pid) {
      ++$patcount;
      $insarr[$plan]['patients'] += 1;
      $prev_pid = $patient_id;
    }
  }

  ksort($insarr);

  while (list($key, $val) = each($insarr)) {
    if ($_POST['form_csvexport']) {
        echo '"' . $key                                                . '",';
        echo '"' . sprintf('%0.2f', $val['charges'])                   . '",';
        echo '"' . $val['visits']                                      . '",';
        echo '"' . $val['patients']                                    . '",';
        echo '"' . sprintf("%.1f", $val['patients'] * 100 / $patcount) . '"' . "\n";
    }
    else {
?>
 <tr>
  <td class='detail'>
   <?php echo $key ?>
  </td>
  <td class='detail' align='right'>
   <?php echo sprintf('%0.2f', $val['charges']) ?>
  </td>
  <td class='detail' align='right'>
   <?php echo $val['visits'] ?>
  </td>
  <td class='detail' align='right'>
   <?php echo $val['patients'] ?>
  </td>
  <td class='detail' align='right'>
   <?php printf("%.1f", $val['patients'] * 100 / $patcount) ?>
  </td>
 </tr>
<?php
    } // end not export
  } // end while
} // end if

if (! $_POST['form_csvexport']) {
?>

</table>
</form>
</center>
</body>
</html>
<?php
} // end not export
?>