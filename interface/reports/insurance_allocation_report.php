<?
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
?>
<html>
<head>
<title>Patient Insurance Distribution</title>
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

<h2>Patient Insurance Distribution</h2>

<form name='theform' method='post' action='insurance_allocation_report.php'>

<table border='0' cellpadding='3'>

 <tr>
  <td>
   From:
   <input type='text' name='form_from_date' size='10' value='<? echo $from_date ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
   <a href="javascript:show_calendar('theform.form_from_date')"
    title="Click here to choose a date"
    ><img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22' border='0'></a>
   &nbsp;To:
   <input type='text' name='form_to_date' size='10' value='<? echo $to_date ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
   <a href="javascript:show_calendar('theform.form_to_date')"
    title="Click here to choose a date"
    ><img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22' border='0'></a>
   &nbsp;
   <input type='submit' name='form_refresh' value='Refresh'>
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
   Primary Insurance
  </td>
  <td class='dehead' align='right'>
   Patients
  </td>
  <td class='dehead' align='right'>
   Percent
  </td>
 </tr>
<?
 if ($_POST['form_refresh']) {
  $from_date = fixDate($_POST['form_from_date']);
  $to_date   = fixDate($_POST['form_to_date'], date('Y-m-d'));

  $query = "SELECT DISTINCT billing.pid, insurance_companies.name " .
   "FROM  billing " .
   "LEFT OUTER JOIN insurance_data ON " .
   "insurance_data.pid = billing.pid AND " .
   "insurance_data.type = 'primary' " .
   "LEFT OUTER JOIN insurance_companies ON " .
   "insurance_companies.id = insurance_data.provider " .
   "WHERE " .
   "billing.date >= '$from_date' AND " .
   "billing.date <= '$to_date' " .
   "ORDER BY insurance_companies.name, billing.pid";

  // echo "<!-- $query -->\n"; // debugging
  $res = sqlStatement($query);
  $insarr = array();

  while ($row = sqlFetchArray($res)) {
   // echo "<!-- " . $row['name'] . " / " . $row['pid'] . " -->\n"; // debugging
   $plan = $row['name'] ? $row['name'] : '-- No Insurance --';
   $insarr[$plan] += 1;
   $inscount += 1;
  }

  while (list($key, $val) = each($insarr)) {
?>
 <tr>
  <td class='detail'>
   <? echo $key ?>
  </td>
  <td class='detail' align='right'>
   <? echo $val ?>
  </td>
  <td class='detail' align='right'>
   <? printf("%.1f", $val * 100 / $inscount) ?>
  </td>
 </tr>
<?
  }
 }
?>

</table>
</form>
</center>
</body>
</html>
