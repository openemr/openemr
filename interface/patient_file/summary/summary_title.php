<?
include_once("../../globals.php");
include_once("$srcdir/patient.inc");
include_once("$srcdir/calendar.inc");
require_once("$srcdir/classes/Pharmacy.class.php");
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
 $result = getPatientData($pid, "fname,lname,pid,pubpid,phone_home,pharmacy_id,DOB,DATE_FORMAT(DOB,'%Y%m%d') as DOB_YMD");
 $provider_results = sqlQuery("select * from users where username='" . $_SESSION{"authUser"} . "'");
 $age = getPatientAge($result["DOB_YMD"]);

 $info = 'ID: ' . $result['pubpid'];
 if ($result['DOB']) $info .= ', DOB: ' . $result['DOB'] . ', Age: ' . $age;
 if ($result['phone_home']) $info .= ', Home: ' . $result['phone_home'];

 if ($result['pharmacy_id']) {
  $pharmacy = new Pharmacy($result['pharmacy_id']);
  if ($pharmacy->get_phone()) $info .= ', Pharm: ' . $pharmacy->get_phone();
 }
?>

<table border="0" cellpadding="0" cellspacing="0" width="100%" height="100%">
 <tr>
  <td width="45%" valign="middle" nowrap>
   <span class="title_bar_top"><?echo $result{"fname"} . " " . $result{"lname"};?></span>
   <span style="font-size:8pt;">(<?php echo $info ?>)</span>
  </td>
  <td width="35%" align="center" valign="middle" nowrap>
   <span class="title"><? xl('Logged in as','e'); ?>: <?echo $provider_results{"fname"}.' '.$provider_results{"lname"};?></span>
  </td>
  <td width="20%" align="right" valign="middle" nowrap>
   <a href='' class='title_bar_top' onclick='return newEvt()'><? xl('New Appointment','e'); ?></a>
  </td>
 </tr>
</table>

</body>
</html>
