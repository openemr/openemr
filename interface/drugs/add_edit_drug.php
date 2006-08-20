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
 $tmpl_line_no = 0;

 if (!acl_check('admin', 'drugs')) die("Not authorized!");

 // Write a line of data for one template to the form.
 //
 function writeTemplateLine($selector, $dosage, $period, $quantity, $refills) {
  global $tmpl_line_no, $interval_array;
  ++$tmpl_line_no;

  echo " <tr>\n";
  echo "  <td class='tmplcell'>";
  echo "<input type='text' name='tmpl[$tmpl_line_no][selector]' value='$selector' size='10' maxlength='100'>";
  echo "</td>\n";
  echo "  <td class='tmplcell'>";
  echo "<input type='text' name='tmpl[$tmpl_line_no][dosage]' value='$dosage' size='10' maxlength='10'>";
  echo "</td>\n";
  echo "  <td class='tmplcell'>";
  echo "<select name='tmpl[$tmpl_line_no][period]'>";
  foreach ($interval_array as $key => $value) {
   echo "<option value='$key'";
   if ($key == $period) echo " selected";
   echo ">$value</option>";
  }
  echo "</td>\n";
  echo "  <td class='tmplcell'>";
  echo "<input type='text' name='tmpl[$tmpl_line_no][quantity]' value='$quantity' size='5' maxlength='7'>";
  echo "</td>\n";
  echo "  <td class='tmplcell'>";
  echo "<input type='text' name='tmpl[$tmpl_line_no][refills]' value='$refills' size='3' maxlength='5'>";
  echo "</td>\n";
  echo " </tr>\n";
 }
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
  $new_drug = false;
  if ($drug_id) {
   if ($_POST['form_save']) {
    sqlStatement("UPDATE drugs SET " .
     "name = '"          . $_POST['form_name']          . "', " .
     "ndc_number = '"    . $_POST['form_ndc_number']    . "', " .
     "on_order = '"      . $_POST['form_on_order']      . "', " .
     "reorder_point = '" . $_POST['form_reorder_point'] . "', " .
     "form = '"          . $_POST['form_form']          . "', " .
     "size = '"          . $_POST['form_size']          . "', " .
     "unit = '"          . $_POST['form_unit']          . "', " .
     "route = '"         . $_POST['form_route']         . "' "  .
     "WHERE drug_id = '$drug_id'");
    sqlStatement("DELETE FROM drug_templates WHERE drug_id = '$drug_id'");
   }
   else {
    if (acl_check('admin', 'super')) {
     sqlStatement("DELETE FROM drug_inventory WHERE drug_id = '$drug_id'");
     sqlStatement("DELETE FROM drug_templates WHERE drug_id = '$drug_id'");
     sqlStatement("DELETE FROM drugs WHERE drug_id = '$drug_id'");
    }
   }
  } else if ($_POST['form_save']) {
   $new_drug = true;
   $drug_id = sqlInsert("INSERT INTO drugs ( " .
    "name, ndc_number, on_order, reorder_point, form, " .
    "size, unit, route " .
    ") VALUES ( " .
    "'" . $_POST['form_name']          . "', " .
    "'" . $_POST['form_ndc_number']    . "', " .
    "'" . $_POST['form_on_order']      . "', " .
    "'" . $_POST['form_reorder_point'] . "', " .
    "'" . $_POST['form_form']          . "', " .
    "'" . $_POST['form_size']          . "', " .
    "'" . $_POST['form_unit']          . "', " .
    "'" . $_POST['form_route']         . "' "  .
    ")");
  }

  if ($_POST['form_save'] && $drug_id) {
   $tmpl = $_POST['tmpl'];
   for ($lino = 1; isset($tmpl["$lino"]['selector']); ++$lino) {
    $iter = $tmpl["$lino"];
    if (trim($iter['selector'])) {
     sqlInsert("INSERT INTO drug_templates ( " .
      "drug_id, selector, dosage, period, quantity, refills " .
      ") VALUES ( " .
      "$drug_id, "                          .
      "'" . trim($iter['selector']) . "', " .
      "'" . trim($iter['dosage'])   . "', " .
      "'" . trim($iter['period'])   . "', " .
      "'" . trim($iter['quantity']) . "', " .
      "'" . trim($iter['refills'])  . "' "  .
      ")");
    }
   }
  }

  // Close this window and redisplay the updated list of drugs.
  //
  echo "<script language='JavaScript'>\n";
  if ($info_msg) echo " alert('$info_msg');\n";
  echo " if (opener.refreshme) opener.refreshme();\n";
  if ($new_drug) {
   echo " window.location.href='add_edit_lot.php?drug=$drug_id&lot=0'\n";
  } else {
   echo " window.close();\n";
  }
  echo "</script></body></html>\n";
  exit();
 }

 if ($drug_id) {
  $row = sqlQuery("SELECT * FROM drugs WHERE drug_id = '$drug_id'");
  $tres = sqlStatement("SELECT * FROM drug_templates WHERE " .
   "drug_id = '$drug_id' ORDER BY selector");
 }
?>

<form method='post' name='theform' action='add_edit_drug.php?drug=<? echo $drug_id ?>'>
<center>

<table border='0' width='100%'>

 <tr>
  <td valign='top' nowrap><b><? xl('Name','e'); ?>:</b></td>
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
  <td valign='top' nowrap><b><? xl('Pill Size','e'); ?>:</b></td>
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
  <td valign='top' nowrap><b><? xl('Templates','e'); ?>:</b></td>
  <td>
   <table border='0' width='100%'>
    <tr>
     <td><b><? xl('Name'    ,'e'); ?></b></td>
     <td><b><? xl('Schedule','e'); ?></b></td>
     <td><b><? xl('Interval','e'); ?></b></td>
     <td><b><? xl('Qty'     ,'e'); ?></b></td>
     <td><b><? xl('Refills' ,'e'); ?></b></td>
    </tr>
    <?php
     $blank_lines = 3;
     if ($tres) {
      $blank_lines = 1;
      while ($trow = sqlFetchArray($tres)) {
       writeTemplateLine($trow['selector'], $trow['dosage'], $trow['period'],
        $trow['quantity'], $trow['refills']);
      }
     }
     for ($i = 0; $i < $blank_lines; ++$i) {
      writeTemplateLine('', '', '', '', '');
     }
    ?>
   </table>
  </td>
 </tr>

</table>

<p>
<input type='submit' name='form_save' value='Save' />

<?php if (acl_check('admin', 'super')) { ?>
&nbsp;
<input type='submit' name='form_delete' value='Delete' style='color:red' />
<?php } ?>

&nbsp;
<input type='button' value='Cancel' onclick='window.close()' />

</p>

</center>
</form>
</body>
</html>
