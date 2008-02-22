<?php
 // Copyright (C) 2006, 2008 Rod Roark <rod@sunsetsystems.com>
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

// Format dollars for display.
//
function bucks($amount) {
  if ($amount) {
    $amount = sprintf("%.2f", $amount);
    if ($amount != 0.00) return $amount;
  }
  return '';
}

// Write a line of data for one template to the form.
//
function writeTemplateLine($selector, $dosage, $period, $quantity, $refills, $prices, $taxrates) {
  global $tmpl_line_no, $interval_array;
  ++$tmpl_line_no;

  echo " <tr>\n";
  echo "  <td class='tmplcell'>";
  echo "<input type='text' name='tmpl[$tmpl_line_no][selector]' value='$selector' size='8' maxlength='100'>";
  echo "</td>\n";
  echo "  <td class='tmplcell'>";
  echo "<input type='text' name='tmpl[$tmpl_line_no][dosage]' value='$dosage' size='6' maxlength='10'>";
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
  echo "<input type='text' name='tmpl[$tmpl_line_no][quantity]' value='$quantity' size='3' maxlength='7'>";
  echo "</td>\n";
  echo "  <td class='tmplcell'>";
  echo "<input type='text' name='tmpl[$tmpl_line_no][refills]' value='$refills' size='3' maxlength='5'>";
  echo "</td>\n";
  foreach ($prices as $pricelevel => $price) {
    echo "  <td class='tmplcell'>";
    echo "<input type='text' name='tmpl[$tmpl_line_no][price][$pricelevel]' value='$price' size='6' maxlength='12'>";
    echo "</td>\n";
  }
  $pres = sqlStatement("SELECT option_id FROM list_options " .
    "WHERE list_id = 'taxrate' ORDER BY seq");
  while ($prow = sqlFetchArray($pres)) {
    echo "  <td class='tmplcell'>";
    echo "<input type='checkbox' name='tmpl[$tmpl_line_no][taxrate][" . $prow['option_id'] . "]' value='1'";
    if (strpos(":$taxrates", $prow['option_id']) !== false) echo " checked";
    echo " /></td>\n";
  }
  echo " </tr>\n";
}
?>
<html>
<head>
<? html_header_show();?>
<title><?php echo $drug_id ? xl("Edit") : xl("Add New"); xl (' Drug','e'); ?></title>
<link rel=stylesheet href='<?php echo $css_header ?>' type='text/css'>

<style>
td { font-size:10pt; }
</style>

<script language="JavaScript">
</script>

</head>

<body <?php echo $top_bg_line;?>>
<?php
 // If we are saving, then save and close the window.
 //
 if ($_POST['form_save'] || $_POST['form_delete']) {
  $new_drug = false;
  if ($drug_id) {
   if ($_POST['form_save']) { // updating an existing drug
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
   else { // deleting
    if (acl_check('admin', 'super')) {
     sqlStatement("DELETE FROM drug_inventory WHERE drug_id = '$drug_id'");
     sqlStatement("DELETE FROM drug_templates WHERE drug_id = '$drug_id'");
     sqlStatement("DELETE FROM drugs WHERE drug_id = '$drug_id'");
     sqlStatement("DELETE FROM prices WHERE pr_id = '$drug_id' AND pr_selector != ''");
    }
   }
  }
  else if ($_POST['form_save']) { // saving a new drug
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
   sqlStatement("DELETE FROM prices WHERE pr_id = '$drug_id' AND pr_selector != ''");
   for ($lino = 1; isset($tmpl["$lino"]['selector']); ++$lino) {
    $iter = $tmpl["$lino"];
    $selector = trim($iter['selector']);
    if ($selector) {
     $taxrates = "";
     if (!empty($iter['taxrate'])) {
      foreach ($iter['taxrate'] as $key => $value) {
       $taxrates .= "$key:";
      }
     }
     sqlInsert("INSERT INTO drug_templates ( " .
      "drug_id, selector, dosage, period, quantity, refills, taxrates " .
      ") VALUES ( " .
      "$drug_id, "                          .
      "'" . $selector               . "', " .
      "'" . trim($iter['dosage'])   . "', " .
      "'" . trim($iter['period'])   . "', " .
      "'" . trim($iter['quantity']) . "', " .
      "'" . trim($iter['refills'])  . "', " .
      "'" . $taxrates               . "' "  .
      ")");

     // Add prices for this drug ID and selector.
     foreach ($iter['price'] as $key => $value) {
      $value = $value + 0;
      if ($value) {
        sqlStatement("INSERT INTO prices ( " .
          "pr_id, pr_selector, pr_level, pr_price ) VALUES ( " .
          "'$drug_id', '$selector', '$key', '$value' )");
      }
     } // end foreach price
    } // end if selector is present
   } // end for each selector
  } // end if saving a drug

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

<form method='post' name='theform' action='add_edit_drug.php?drug=<?php echo $drug_id; ?>'>
<center>

<table border='0' width='100%'>

 <tr>
  <td valign='top' nowrap><b><?php xl('Name','e'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_name' maxlength='80' value='<?php echo $row['name'] ?>' style='width:100%' />
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><?php xl('NDC Number','e'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_ndc_number' maxlength='20' value='<?php echo $row['ndc_number'] ?>' style='width:100%' />
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><?php xl('On Order','e'); ?>:</b></td>
  <td>
   <input type='text' size='5' name='form_on_order' maxlength='7' value='<?php echo $row['on_order'] ?>' />
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><?php xl('Reorder At','e'); ?>:</b></td>
  <td>
   <input type='text' size='5' name='form_reorder_point' maxlength='7' value='<?php echo $row['reorder_point'] ?>' />
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><?php xl('Form','e'); ?>:</b></td>
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
  <td valign='top' nowrap><b><?php xl('Pill Size','e'); ?>:</b></td>
  <td>
   <input type='text' size='5' name='form_size' maxlength='7' value='<?php echo $row['size'] ?>' />
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><?php xl('Units','e'); ?>:</b></td>
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
  <td valign='top' nowrap><b><?php xl('Route','e'); ?>:</b></td>
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
  <td valign='top' nowrap><b><?php xl('Templates','e'); ?>:</b></td>
  <td>
   <table border='0' width='100%'>
    <tr>
     <td><b><?php xl('Name'    ,'e'); ?></b></td>
     <td><b><?php xl('Schedule','e'); ?></b></td>
     <td><b><?php xl('Interval','e'); ?></b></td>
     <td><b><?php xl('Qty'     ,'e'); ?></b></td>
     <td><b><?php xl('Refills' ,'e'); ?></b></td>
<?php
  // Show a heading for each price level.  Also create an array of prices
  // for new template lines.
  $emptyPrices = array();
  $pres = sqlStatement("SELECT option_id, title FROM list_options " .
    "WHERE list_id = 'pricelevel' ORDER BY seq");
  while ($prow = sqlFetchArray($pres)) {
    $emptyPrices[$prow['option_id']] = '';
    echo "     <td><b>" . $prow['title'] . "</b></td>\n";
  }
  // Show a heading for each tax rate.
  $pres = sqlStatement("SELECT option_id, title FROM list_options " .
    "WHERE list_id = 'taxrate' ORDER BY seq");
  while ($prow = sqlFetchArray($pres)) {
    echo "     <td><b>" . $prow['title'] . "</b></td>\n";
  }
?>
    </tr>
<?php
  $blank_lines = 3;
  if ($tres) {
    $blank_lines = 1;
    while ($trow = sqlFetchArray($tres)) {
      $selector = $trow['selector'];
      // Get array of prices.
      $prices = array();
      $pres = sqlStatement("SELECT lo.option_id, p.pr_price " .
        "FROM list_options AS lo LEFT OUTER JOIN prices AS p ON " .
        "p.pr_id = '$drug_id' AND p.pr_selector = '$selector' AND " .
        "p.pr_level = lo.option_id " .
        "WHERE list_id = 'pricelevel' ORDER BY lo.seq");
      while ($prow = sqlFetchArray($pres)) {
        $prices[$prow['option_id']] = $prow['pr_price'];
      }
      writeTemplateLine($selector, $trow['dosage'], $trow['period'],
        $trow['quantity'], $trow['refills'], $prices, $trow['taxrates']);
    }
  }
  for ($i = 0; $i < $blank_lines; ++$i) {
    writeTemplateLine('', '', '', '', '', $emptyPrices, '');
  }
?>
   </table>
  </td>
 </tr>

</table>

<p>
<input type='submit' name='form_save' value='<?php xl('Save','e'); ?>' />

<?php if (acl_check('admin', 'super')) { ?>
&nbsp;
<input type='submit' name='form_delete' value='<?php xl('Delete','e'); ?>' style='color:red' />
<?php } ?>

&nbsp;
<input type='button' value='<?php xl('Cancel','e'); ?>' onclick='window.close()' />

</p>

</center>
</form>
</body>
</html>
