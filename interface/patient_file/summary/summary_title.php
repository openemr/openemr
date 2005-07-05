<?
include_once("../../globals.php");
include_once("$srcdir/patient.inc");
include_once("$srcdir/calendar.inc");
?>
<html>
<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<script type="text/javascript" src="../../../library/dialog.js"></script>

<script language='JavaScript'>

 function newEvt() {
  dlgopen('../../main/calendar/add_edit_event.php?patientid=<? echo $pid ?>',
   '_blank', 550, 270);
  return false;
 }

</script>

</head>

<body <?echo $title_bg_line;?> topmargin='0' rightmargin='0' leftmargin='2' bottommargin='0'
 marginwidth='2' marginheight='0'>

<?
 $result = getPatientData($pid, "fname,lname,providerID,pid,pubpid,DATE_FORMAT(DOB,'%Y%m%d') as DOB_YMD");
 $provider_results = sqlQuery("select * from users where username='".$_SESSION{"authUser"}."'");
 $age = getPatientAge($result["DOB_YMD"]);
?>

<table border="0" cellpadding="0" cellspacing="0" width="100%" height="100%">
 <tr>
  <td width="50%" valign="middle" nowrap>
   <span class="title_bar_top"><?echo $result{"fname"} . " " . $result{"lname"};?></span>
   <span style="font-size:10pt;">(Age: <?=$age?> ID: <?=$result['pubpid']?>)</span>
   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
   <span class="title">Logged in as: <?echo $provider_results{"fname"}.' '.$provider_results{"lname"};?> </span>
   <span style="font-size:9pt;"> (<?=$_SESSION['authGroup']?>)</span>
  </td>
  <td width="50%" align="right" valign="middle" nowrap>
<?
 /****
 $ampm = 1;
 if (date("H") >= 12) {
  $ampm = 2;
 }
 $dbconn = $GLOBALS['adodb']['db'];

 $sql = "SELECT pc_catid,pc_catname,pc_catcolor,pc_catdesc,
  pc_recurrtype,pc_recurrspec,pc_recurrfreq,pc_duration,
  pc_dailylimit,pc_end_date_flag,pc_end_date_type,pc_end_date_freq,
  pc_end_all_day FROM openemr_postcalendar_categories
  WHERE pc_catid = " . $GLOBALS['default_category'];
 $catresult = $dbconn->Execute($sql);

 $event_dur_minutes  = ($catresult->fields['pc_duration']%(60 * 60))/60;
 $event_dur_hours = ($catresult->fields['pc_duration']/(60 * 60));
 $event_title = $catresult->fields['pc_catname'];
 $qstring = "&event_startampm=$ampm&event_starttimeh=" . date("H") .
  "&event_category=" . $GLOBALS['default_category'] .
  "&provider_id=". $result['providerID'] .
  "&event_dur_hours=" . $event_dur_hours .
  "&event_dur_minutes=" . $event_dur_minutes .
  "&patient_id=" . $result['pid'] .
  "&event_subject=" . $event_title;

 echo '<a href="../../main/calendar/find_patient.php?no_nav=1&show_pnote_link=1' .
  $qstring . '" target="Notes" class=title_bar_top>New Appointment</a>';
 ****/
?>
   <a href='' class='title_bar_top' onclick='return newEvt()'>New Appointment</a>
  </td>
 </tr>
</table>

</body>
</html>
