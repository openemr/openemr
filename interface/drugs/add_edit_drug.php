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

 $alertmsg = '';
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
  echo "  <td class='tmplcell drugsonly'>";
  echo "<input type='text' name='tmpl[$tmpl_line_no][selector]' value='$selector' size='8' maxlength='100'>";
  echo "</td>\n";
  echo "  <td class='tmplcell drugsonly'>";
  echo "<input type='text' name='tmpl[$tmpl_line_no][dosage]' value='$dosage' size='6' maxlength='10'>";
  echo "</td>\n";
  echo "  <td class='tmplcell drugsonly'>";
  echo "<select name='tmpl[$tmpl_line_no][period]'>";
  foreach ($interval_array as $key => $value) {
    echo "<option value='$key'";
    if ($key == $period) echo " selected";
    echo ">$value</option>";
  }
  echo "</td>\n";
  echo "  <td class='tmplcell drugsonly'>";
  echo "<input type='text' name='tmpl[$tmpl_line_no][quantity]' value='$quantity' size='3' maxlength='7'>";
  echo "</td>\n";
  echo "  <td class='tmplcell drugsonly'>";
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

// Translation for form fields.
function escapedff($name) {
  $field = trim($_POST[$name]);
  if (!get_magic_quotes_gpc()) return addslashes($field);
  return $field;
}
?>
<html>
<head>
<?php html_header_show();?>
<title><?php echo $drug_id ? xl("Edit") : xl("Add New"); xl (' Drug','e'); ?></title>
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>

<style>
td { font-size:10pt; }

<?php if ($GLOBALS['sell_non_drug_products'] == 2) { ?>
.drugsonly { display:none; }
<?php } else { ?>
.drugsonly { }
<?php } ?>

</style>

<script type="text/javascript" src="../../library/topdialog.js"></script>
<script type="text/javascript" src="../../library/dialog.js"></script>

<script language="JavaScript">

<?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

// This is for callback by the find-code popup.
// Appends to or erases the current list of related codes.
function set_related(codetype, code, selector, codedesc) {
 var f = document.forms[0];
 var s = f.form_related_code.value;
 if (code) {
  if (s.length > 0) s += ';';
  s += codetype + ':' + code;
 } else {
  s = '';
 }
 f.form_related_code.value = s;
}

// This invokes the find-code popup.
function sel_related() {
 dlgopen('../patient_file/encounter/find_code_popup.php', '_blank', 500, 400);
}

</script>

</head>

<body class="body_top">
<?php
// If we are saving, then save and close the window.
// First check for duplicates.
//
if ($_POST['form_save']) {
  $crow = sqlQuery("SELECT COUNT(*) AS count FROM drugs WHERE " .
    "name = '"  . escapedff('form_name')  . "' AND " .
    "form = '"  . escapedff('form_form')  . "' AND " .
    "size = '"  . escapedff('form_size')  . "' AND " .
    "unit = '"  . escapedff('form_unit')  . "' AND " .
    "route = '" . escapedff('form_route') . "' AND " .
    "drug_id != '$drug_id'");
  if ($crow['count']) {
    $alertmsg = "Cannot add this entry because it already exists!";
  }
}

if (($_POST['form_save'] || $_POST['form_delete']) && !$alertmsg) {
  $new_drug = false;
  if ($drug_id) {
   if ($_POST['form_save']) { // updating an existing drug
    sqlStatement("UPDATE drugs SET " .
     "name = '"          . escapedff('form_name')          . "', " .
     "ndc_number = '"    . escapedff('form_ndc_number')    . "', " .
     "on_order = '"      . escapedff('form_on_order')      . "', " .
     "reorder_point = '" . escapedff('form_reorder_point') . "', " .
     "form = '"          . escapedff('form_form')          . "', " .
     "size = '"          . escapedff('form_size')          . "', " .
     "unit = '"          . escapedff('form_unit')          . "', " .
     "route = '"         . escapedff('form_route')         . "', " .
     "related_code = '"  . escapedff('form_related_code')  . "' "  .
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
    "size, unit, route, related_code " .
    ") VALUES ( " .
    "'" . escapedff('form_name')          . "', " .
    "'" . escapedff('form_ndc_number')    . "', " .
    "'" . escapedff('form_on_order')      . "', " .
    "'" . escapedff('form_reorder_point') . "', " .
    "'" . escapedff('form_form')          . "', " .
    "'" . escapedff('form_size')          . "', " .
    "'" . escapedff('form_unit')          . "', " .
    "'" . escapedff('form_route')         . "', " .
    "'" . escapedff('form_related_code')  . "' "  .
    ")");
  }

  if ($_POST['form_save'] && $drug_id) {
   $tmpl = $_POST['tmpl'];
   // If using the simplified drug form, then force the one and only
   // selector name to be the same as the product name.
   if ($GLOBALS['sell_non_drug_products'] == 2) {
    $tmpl["1"]['selector'] = escapedff('form_name');
   }
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

 <tr class='drugsonly'>
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

 <tr class='drugsonly'>
  <td valign='top' nowrap><b><?php xl('Pill Size','e'); ?>:</b></td>
  <td>
   <input type='text' size='5' name='form_size' maxlength='7' value='<?php echo $row['size'] ?>' />
  </td>
 </tr>

 <tr class='drugsonly'>
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

 <tr class='drugsonly'>
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
  <td valign='top' nowrap><b><?php xl('Relate To','e'); ?>:</b></td>
  <td>
   <input type='text' size='50' name='form_related_code'
    value='<?php echo $row['related_code'] ?>' onclick='sel_related()'
    title='<?php xl('Click to select related code','e'); ?>'
    style='width:100%' readonly />
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap>
   <b><?php xl($GLOBALS['sell_non_drug_products'] == 2 ? 'Fees' : 'Templates','e'); ?>:</b>
  </td>
  <td>
   <table border='0' width='100%'>
    <tr>
     <td class='drugsonly'><b><?php xl('Name'    ,'e'); ?></b></td>
     <td class='drugsonly'><b><?php xl('Schedule','e'); ?></b></td>
     <td class='drugsonly'><b><?php xl('Interval','e'); ?></b></td>
     <td class='drugsonly'><b><?php xl('Qty'     ,'e'); ?></b></td>
     <td class='drugsonly'><b><?php xl('Refills' ,'e'); ?></b></td>
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
  $blank_lines = $GLOBALS['sell_non_drug_products'] == 2 ? 1 : 3;
  if ($tres) {
    while ($trow = sqlFetchArray($tres)) {
      $blank_lines = $GLOBALS['sell_non_drug_products'] == 2 ? 0 : 1;
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
    $selector = $GLOBALS['sell_non_drug_products'] == 2 ? $row['name'] : '';
    writeTemplateLine($selector, '', '', '', '', $emptyPrices, '');
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

<script language="JavaScript">
<?php
 if ($alertmsg) {
  echo "alert('" . htmlentities($alertmsg) . "');\n";
 }
?>
</script>

</body>
</html>
