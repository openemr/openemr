<?
include_once("../../globals.php");
include_once("$srcdir/patient.inc");
?>

<html>
<head>

<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

</head>
<body <?echo $title_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>

<?
 $result = getPatientData($pid, "fname,lname,pubpid,phone_home,phone_pharmacy,DOB");
 $provider_results = sqlQuery("select * from users where username='".$_SESSION{"authUser"}."'");

 $info = 'ID: ' . $result['pubpid'];
 if ($result['DOB']) $info .= ', DOB: ' . $result['DOB'];
 if ($result['phone_home']) $info .= ', Home: ' . $result['phone_home'];
 if ($result['phone_pharmacy']) $info .= ', Pharm: ' . $result['phone_pharmacy'];
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
   &nbsp;
  </td>
 </tr>
</table>

</body>
</html>
