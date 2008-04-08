<?php
include_once("../../globals.php");
include_once("$srcdir/patient.inc");
require_once("$srcdir/classes/Pharmacy.class.php");
?>

<html>
<head>
<?php html_header_show();?>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

</head>
<body class="body_title">

<?php
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
  <td style="width:45%; text-align: left; vertical-align:middle; white-space: nowrap">
   <span class="title_bar_top"><?php echo $result{"fname"} . " " . $result{"lname"};?></span>
   <span style="font-size:0.7em;">(<?php echo $info ?>)</span>
  </td>
  <td style="width:35%; text-align: center; vertical-align:middle; white-space: nowrap">
   <span class="title_bar_top"><?php xl('Logged in as','e'); ?>: <?php echo $provider_results{"fname"}.' '.$provider_results{"lname"};?></span>
  </td>
  <td style="width:20%; text-align: right; vertical-align:middle; white-space: nowrap">
   &nbsp;
  </td>
 </tr>
</table>

</body>
</html>
