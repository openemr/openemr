<?php
 // Copyright (C) 2006 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 require_once("../globals.php");
 require_once("$srcdir/acl.inc");
 require_once("drugs.inc.php");

 $drug_id = $_REQUEST['drug'];
 $info_msg = "";

 if (!acl_check('admin', 'drugs')) die("Not authorized!");
?>
<html>
<head>
<title><? echo $drug_id ? "Edit" : "Add New" ?> Drug</title>
<link rel=stylesheet href='<? echo $css_header ?>' type='text/css'>

<style>
td { font-size:10pt; }
</style>

<script language="JavaScript">
</script>

</head>

<body <?echo $top_bg_line;?>>
<?php
 // If we are saving, then save and close the window.
 //
 if ($_POST['form_save'] || $_POST['form_delete']) {
  if ($drug_id) {
   if ($_POST['form_save']) {
    sqlStatement("UPDATE drugs SET " .
     "name = '"          . $_POST['form_name']          . "', " .
     "ndc_number = '"    . $_POST['form_ndc_number']    . "', " .
     "on_order = '"      . $_POST['form_on_order']      . "', " .
     "reorder_point = '" . $_POST['form_reorder_point'] . "', " .
     "reactions = '"     . $_POST['form_reactions']     . "', " .
     "form = '"          . $_POST['form_form']          . "', " .
     "dosage = '"        . $_POST['form_dosage']        . "', " .
     "size = '"          . $_POST['form_size']          . "', " .
     "unit = '"          . $_POST['form_unit']          . "', " .
     "route = '"         . $_POST['form_route']         . "', " .
     "period = '"        . $_POST['form_period']        . "', " .
     "substitute = '"    . $_POST['form_substitute']    . "', " .
     "refills = '"       . $_POST['form_refills']       . "', " .
     "per_refill = '"    . $_POST['form_per_refill']    . "' "  .
     "WHERE drug_id = '$drug_id'");
   } else {
    sqlStatement("DELETE FROM drug_inventory WHERE drug_id = '$drug_id'");
    sqlStatement("DELETE FROM drugs WHERE drug_id = '$drug_id'");
   }
  } else {
   $drug_id = sqlInsert("INSERT INTO drugs ( " .
    "name, ndc_number, on_order, reorder_point, reactions, form, dosage, " .
    "size, unit, route, period, substitute, refills, per_refill " .
    ") VALUES ( " .
    "'" . $_POST['form_name']          . "', " .
    "'" . $_POST['form_ndc_number']    . "', " .
    "'" . $_POST['form_on_order']      . "', " .
    "'" . $_POST['form_reorder_point'] . "', " .
    "'" . $_POST['form_reactions']     . "', " .
    "'" . $_POST['form_form']          . "', " .
    "'" . $_POST['form_dosage']        . "', " .
    "'" . $_POST['form_size']          . "', " .
    "'" . $_POST['form_unit']          . "', " .
    "'" . $_POST['form_route']         . "', " .
    "'" . $_POST['form_period']        . "', " .
    "'" . $_POST['form_substitute']    . "', " .
    "'" . $_POST['form_refills']       . "', " .
    "'" . $_POST['form_per_refill']    . "' "  .
   ")");
  }

  // Close this window and redisplay the updated list of drugs.
  //
  echo "<script language='JavaScript'>\n";
  if ($info_msg) echo " alert('$info_msg');\n";
  echo " window.close();\n";
  echo " if (opener.refreshme) opener.refreshme();\n";
  echo "</script></body></html>\n";
  exit();
 }

 if ($drug_id) {
  $row = sqlQuery("SELECT * FROM drugs WHERE drug_id = $drug_id");
 }
?>

<form method='post' name='theform' action='add_edit_drug.php?drug=<? echo $drug_id ?>'>
<center>

<table border='0' width='100%'>

 <tr>
  <td valign='top' width='1%' nowrap><b><? xl('Name','e'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_name' maxlength='80' value='<? echo $row['name'] ?>' style='width:100%' />
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><? xl('NDC Number','e'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_ndc_number' maxlength='20' value='<? echo $row['ndc_number'] ?>' style='width:100%' />
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><? xl('On Order','e'); ?>:</b></td>
  <td>
   <input type='text' size='5' name='form_on_order' maxlength='7' value='<? echo $row['on_order'] ?>' />
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><? xl('Reorder At','e'); ?>:</b></td>
  <td>
   <input type='text' size='5' name='form_reorder_point' maxlength='7' value='<? echo $row['reorder_point'] ?>' />
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><? xl('Reactions','e'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_reactions' maxlength='250' value='<? echo $row['reactions'] ?>' style='width:100%' />
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><? xl('Form','e'); ?>:</b></td>
  <td>
   <select name='form_form'>
<?php
 foreach ($form_array as $key => $value) {
  echo "   <option value='$key'";
  if ($key == $row['form']) echo " selected";
  echo ">$value\n";
 }
?>
   </select>
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><? xl('Dosage','e'); ?>:</b></td>
  <td>
   <input type='text' size='10' name='form_dosage' maxlength='10' value='<? echo $row['dosage'] ?>' />
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><? xl('Size','e'); ?>:</b></td>
  <td>
   <input type='text' size='5' name='form_size' maxlength='7' value='<? echo $row['size'] ?>' />
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><? xl('Units','e'); ?>:</b></td>
  <td>
   <select name='form_unit'>
<?php
 foreach ($unit_array as $key => $value) {
  echo "   <option value='$key'";
  if ($key == $row['unit']) echo " selected";
  echo ">$value\n";
 }
?>
   </select>
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><? xl('Route','e'); ?>:</b></td>
  <td>
   <select name='form_route'>
<?php
 foreach ($route_array as $key => $value) {
  echo "   <option value='$key'";
  if ($key == $row['route']) echo " selected";
  echo ">$value\n";
 }
?>
   </select>
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><? xl('Interval','e'); ?>:</b></td>
  <td>
   <select name='form_period'>
<?php
 foreach ($interval_array as $key => $value) {
  echo "   <option value='$key'";
  if ($key == $row['period']) echo " selected";
  echo ">$value\n";
 }
?>
   </select>
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><? xl('Substitution','e'); ?>:</b></td>
  <td>
   <select name='form_substitute'>
<?php
 foreach ($substitute_array as $key => $value) {
  echo "   <option value='$key'";
  if ($key == $row['substitute']) echo " selected";
  echo ">$value\n";
 }
?>
   </select>
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><? xl('Refills','e'); ?>:</b></td>
  <td>
   <input type='text' size='5' name='form_refills' maxlength='7' value='<? echo $row['refills'] ?>' />
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><? xl('Per Refill','e'); ?>:</b></td>
  <td>
   <input type='text' size='5' name='form_per_refill' maxlength='7' value='<? echo $row['per_refill'] ?>' />
  </td>
 </tr>

</table>

<p>
<input type='submit' name='form_save' value='Save' />

&nbsp;
<input type='submit' name='form_delete' value='Delete' style='color:red' />

&nbsp;
<input type='button' value='Cancel' onclick='window.close()' />
</p>

</center>
</form>
</body>
</html>
