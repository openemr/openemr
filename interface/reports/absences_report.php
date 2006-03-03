<?
 // This module is for team sports use and reports on absences by
 // injury type (diagnosis) for a given time period.

 include_once("../globals.php");
 include_once("../../library/patient.inc");
 include_once("../../library/acl.inc");

 // Might want something different here.
 //
 // if (! acl_check('acct', 'rep')) die("Unauthorized access.");

 $from_date = fixDate($_POST['form_from_date']);
 $to_date   = fixDate($_POST['form_to_date'], date('Y-m-d'));
 $form_by   = $_POST['form_by'];
?>
<html>
<head>
<title>Absences by Diagnosis</title>
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

<h2><? xl('Days and Games Missed','e'); ?></h2>

<form name='theform' method='post' action='absences_report.php'>

<table border='0' cellpadding='3'>

 <tr>
  <td>
   By:
   <input type='radio' name='form_by' value='d'
    <? echo ($form_by == 'p') ? '' : 'checked' ?> /><? xl('Diagnosis','e'); ?>&nbsp;
   <input type='radio' name='form_by' value='p'
    <? echo ($form_by == 'p') ? 'checked' : '' ?> /><? xl('Player','e'); ?> &nbsp;
   From:
   <input type='text' name='form_from_date' size='10' value='<? echo $from_date ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
   <a href="javascript:show_calendar('theform.form_from_date')"
    title=".xl('Click here to choose a date')."
    ><img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22' border='0'></a>
   &nbsp;To:
   <input type='text' name='form_to_date' size='10' value='<? echo $to_date ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
   <a href="javascript:show_calendar('theform.form_to_date')"
    title=".xl('Click here to choose a date')."
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
<?  if ($form_by == 'p') { ?>
  <td class="dehead">
   <? xl('Name','e'); ?>
  </td>
<? } else { ?>
  <td class="dehead">
   <? xl('Code','e'); ?>
  </td>
  <td class="dehead">
   <? xl('Description','e'); ?>
  </td>
<? } ?>
  <td class='dehead' align='right'>
   <? xl('Issues','e'); ?>
  </td>
  <td class='dehead' align='right'>
   <? xl('Days','e'); ?>
  </td>
  <td class='dehead' align='right'>
   <? xl('Games','e'); ?>
  </td>
 </tr>
<?
 if ($_POST['form_refresh']) {
  $form_doctor = $_POST['form_doctor'];
  $from_date = fixDate($_POST['form_from_date']);
  $to_date   = fixDate($_POST['form_to_date'], date('Y-m-d'));

  if ($form_by == 'p') {
   $query = "SELECT patient_data.lname, patient_data.fname, patient_data.mname, " .
    "count(*) AS count, " .
    "SUM(lists.extrainfo) AS gmissed, " .
    "SUM(TO_DAYS(LEAST(IFNULL(lists.enddate,CURRENT_DATE),'$to_date')) - TO_DAYS(GREATEST(lists.begdate,'$from_date'))) AS dmissed " .
    "FROM lists, patient_data WHERE " .
    "(lists.enddate IS NULL OR lists.enddate >= '$from_date') AND lists.begdate <= '$to_date' AND " .
    "patient_data.pid = lists.pid " .
    "GROUP BY lname, fname, mname";
  }
  else {
   $query = "SELECT lists.diagnosis, codes.code_text, count(*) AS count, " .
    "SUM(lists.extrainfo) AS gmissed, " .
    "SUM(TO_DAYS(LEAST(IFNULL(lists.enddate,CURRENT_DATE),'$to_date')) - TO_DAYS(GREATEST(lists.begdate,'$from_date'))) AS dmissed " .
    "FROM lists " .
    "LEFT OUTER JOIN codes " .
    "ON codes.code = lists.diagnosis AND " .
    "(codes.code_type = 2 OR codes.code_type = 4 OR codes.code_type = 5 OR codes.code_type = 8) " .
    "WHERE " .
    "(lists.enddate IS NULL OR lists.enddate >= '$from_date') AND lists.begdate <= '$to_date' " .
    "GROUP BY lists.diagnosis";
  }

  echo "<!-- $query -->\n"; // debugging

  $res = sqlStatement($query);

  while ($row = sqlFetchArray($res)) {
?>

 <tr>
<?  if ($form_by == 'p') { ?>
  <td class='detail'>
   <? echo $row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname'] ?>
  </td>
<? } else { ?>
  <td class='detail'>
   <? echo $row['diagnosis'] ?>
  </td>
  <td class='detail'>
   <? echo $row['code_text'] ?>
  </td>
<? } ?>
  <td class='detail' align='right'>
   <? echo $row['count'] ?>
  </td>
  <td class='detail' align='right'>
   <? echo $row['dmissed'] ?>
  </td>
  <td class='detail' align='right'>
   <? echo $row['gmissed'] ?>
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
