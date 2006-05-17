<?
include_once("../../globals.php");
include_once("$srcdir/forms.inc");
include_once("$srcdir/encounter.inc");
include_once("$srcdir/patient.inc");
require_once("$srcdir/classes/Pharmacy.class.php");
?>

<html>
<head>

<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

</head>
<body <?echo $title_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>

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

 if (!empty($_GET["set_encounter"])) {
  setencounter($_GET["set_encounter"]);
 }

 if(!empty($encounter)){
  $subresult = getEncounterDateByEncounter($encounter);
  $encounter_date = date("D F jS Y", strtotime($subresult['date']));
 } else {
  $encounter_date = date("D F jS Y"); //otherwise, set today's date
 }
?>

<table border="0" cellpadding="0" cellspacing="0" width="100%" height="100%">
 <tr>
  <td width="33%" valign="middle" nowrap>
   <span class="title_bar_top"><?echo $result{"fname"} . " " . $result{"lname"};?></span>
   <span style="font-size:8pt;">(<?php echo $info ?>)</span>
  </td>
  <td width="33%" align="center" valign="middle" nowrap>
   <span class="title">Logged in as: <?echo $provider_results{"fname"}.' '.$provider_results{"lname"};?></span>
  </td>
  <td width="33%" align="right" valign="middle" nowrap>
   <span class="title_bar_top">Encounter: <?echo $encounter_date?> </span>
  </td>
 </tr>
</table>

</body>
</html>
