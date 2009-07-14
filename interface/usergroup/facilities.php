<?php
require_once("../globals.php");
require_once("../../library/acl.inc");
require_once("$srcdir/sql.inc");
require_once("$srcdir/formdata.inc.php");

$alertmsg = '';

if (isset($_POST["mode"]) && $_POST["mode"] == "facility") {
  sqlStatement("INSERT INTO facility SET " .
  "name = '"         . trim(formData('facility'    )) . "', " .
  "phone = '"        . trim(formData('phone'       )) . "', " .
  "fax = '"          . trim(formData('fax'         )) . "', " .
  "street = '"       . trim(formData('street'      )) . "', " .
  "city = '"         . trim(formData('city'        )) . "', " .
  "state = '"        . trim(formData('state'       )) . "', " .
  "postal_code = '"  . trim(formData('postal_code' )) . "', " .
  "country_code = '" . trim(formData('country_code')) . "', " .
  "federal_ein = '"  . trim(formData('federal_ein' )) . "', " .
  "facility_npi = '" . trim(formData('facility_npi')) . "'");
}
?>
<html>
<head>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

</head>
<body class="body_top">

<span class="title"><?php xl('Facility Administration','e'); ?></span>

<br><br>

<table width=100%>
<tr>

<td valign=top>

<form name='facility' method='post' action="facilities.php"
 onsubmit='return top.restoreSession()'>
<input type=hidden name=mode value="facility">
<span class="bold"><?php xl('New Facility Information','e'); ?>: </span>
</td><td>

<table border=0 cellpadding=0 cellspacing=0>
<tr>
<td><span class="text"><?php xl('Name','e'); ?>: </span></td><td><input type=entry name=facility size=20 value=""></td>
<td><span class="text"><?php xl('Phone','e'); ?>: </span></td><td><input type=entry name=phone size=20 value=""></td>
</tr>
<tr>
<td>&nbsp;</td><td>&nbsp;</td>
<td><span class="text"><?php xl('Fax','e'); ?>: </span></td><td><input type=entry name=fax size=20 value=""></td>
</tr>
<tr>
<td><span class="text"><?php xl('Address','e'); ?>: </span></td><td><input type=entry size=20 name=street value=""></td>
<td><span class="text"><?php xl('City','e'); ?>: </span></td><td><input type=entry size=20 name=city value=""></td>
</tr>
<tr>
<td><span class="text"><?php xl('State','e'); ?>: </span></td><td><input type=entry size=20 name=state value=""></td>
<td><span class="text"><?php xl('Zip Code','e'); ?>: </span></td><td><input type=entry size=20 name=postal_code value=""></td>
</tr>
<tr>
<td height="22"><span class="text"><?php xl('Country','e'); ?>: </span></td>
<td><input type=entry size=20 name=country_code value=""></td>
<td><span class="text"><?php xl('Federal EIN','e'); ?>: </span></td><td><input type=entry size=20 name=federal_ein value=""></td>
</tr>
<tr>
<td>&nbsp;</td><td>&nbsp;</td>

<td><span class="text"><?php ($GLOBALS['simplified_demographics'] ? xl('Facility Code','e') : xl('Facility NPI','e')); ?>:
</span></td><td><input type=entry size=20 name=facility_npi value=""></td>

</tr>
<tr>
<td>&nbsp;</td><td>&nbsp;</td>
<td>&nbsp;</td><td><input type="submit" value=<?php xl('Add Facility','e'); ?>></td>
</tr>
</table>
</form>
<br>
</tr>
<tr>
<td valign=top>

<span class="bold"><?php xl('Edit Facilities','e'); ?>: </span>
</td><td valign=top>
<?php
$fres = 0;
$fres = sqlStatement("select * from facility order by name");
if ($fres) {
  $result2 = array();
  for ($iter3 = 0;$frow = sqlFetchArray($fres);$iter3++)
    $result2[$iter3] = $frow;
  foreach($result2 as $iter3) {
?>
<span class="text"><?php echo $iter3{name};?></span>
<a href="facility_admin.php?fid=<?php echo $iter3{id};?>" class="link_submit"
 onclick="top.restoreSession()">(<?php xl('Edit','e'); ?>)</a><br>
<?php
  }
}
?>

</td>
</tr>
</table>

<script language="JavaScript">
<?php
  if ($alertmsg = trim($alertmsg)) {
    echo "alert('$alertmsg');\n";
  }
?>
</script>

</body>
</html>
