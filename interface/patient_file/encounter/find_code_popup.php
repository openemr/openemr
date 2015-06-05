<?php
/* Copyright (C) 2008-2014 Rod Roark <rod@sunsetsystems.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 */

$fake_register_globals=false;
$sanitize_all_escapes=true;

require_once('../../globals.php');
require_once($GLOBALS['srcdir'].'/patient.inc');
require_once($GLOBALS['srcdir'].'/csv_like_join.php');
require_once($GLOBALS['fileroot'].'/custom/code_types.inc.php');

$info_msg = "";
$codetype = $_REQUEST['codetype'];
if (!empty($codetype)) {
	$allowed_codes = split_csv_line($codetype);
}

$form_code_type = $_POST['form_code_type'];

// Determine which code type will be selected by default.
$default = '';
if (!empty($form_code_type)) {
  $default = $form_code_type;
}
else if (!empty($allowed_codes) && count($allowed_codes) == 1) {
  $default = $allowed_codes[0];
}
else if (!empty($_REQUEST['default'])) {
  $default = $_REQUEST['default'];
}

// This variable is used to store the html element
// of the target script where the selected code
// will be stored in.
$target_element = $_GET['target_element'];
?>
<html>
<head>
<?php html_header_show(); ?>
<title><?php echo xlt('Code Finder'); ?></title>
<link rel="stylesheet" href='<?php echo attr($css_header) ?>' type='text/css'>

<style>
td { font-size:10pt; }
</style>

<script language="JavaScript">

 // Standard function
 function selcode(codetype, code, selector, codedesc) {
  if (opener.closed || ! opener.set_related) {
   alert('<?php echo xls('The destination form was closed; I cannot act on your selection.'); ?>');
  }
  else {
   var msg = opener.set_related(codetype, code, selector, codedesc);
   if (msg) alert(msg);
   window.close();
   return false;
  }
 }

 // TBD: The following function is not necessary. See
 // interface/forms/LBF/new.php for an alternative method that does not require it.
 // Rod 2014-04-15

 // Standard function with additional parameter to select which
 // element on the target page to place the selected code into.
 function selcode_target(codetype, code, selector, codedesc, target_element) {
  if (opener.closed || ! opener.set_related_target)
   alert('<?php echo xls('The destination form was closed; I cannot act on your selection.'); ?>');
  else
   opener.set_related_target(codetype, code, selector, codedesc, target_element);
  window.close();
  return false;
 }

</script>

</head>

<body class="body_top" OnLoad="document.theform.search_term.focus();">

<?php
$string_target_element = "";
if (!empty($target_element)) {
$string_target_element = "?target_element=" . urlencode($target_element) . "&";
}
else {
$string_target_element = "?";
}
?>
<?php if (!empty($allowed_codes)) { ?>
  <form method='post' name='theform' action='find_code_popup.php<?php echo $string_target_element ?>codetype=<?php echo urlencode($codetype) ?>'>
<?php } else { ?>
  <form method='post' name='theform' action='find_code_popup.php<?php echo $string_target_element ?>'>
<?php } ?>

<center>

<table border='0' cellpadding='5' cellspacing='0'>

 <tr>
  <td height="1">
  </td>
 </tr>

 <tr bgcolor='#ddddff'>
  <td>
   <b>

<?php
if (!empty($allowed_codes)) {
	if (count($allowed_codes) === 1) {
  echo "<input type='text' name='form_code_type' value='" . attr($codetype) . "' size='5' readonly>\n";
	} else {
?>
   <select name='form_code_type'>
<?php
		foreach ($allowed_codes as $code) {
			$selected_attr = ($default == $code) ? " selected='selected'" : '';
?>
   	<option value='<?php echo attr($code) ?>'<?php echo $selected_attr ?>><?php echo xlt($code_types[$code]['label']) ?></option>
<?php
		}
?>
   </select>
<?php
	}
}
else {
  // No allowed types were specified, so show all.
  echo "   <select name='form_code_type'";
  echo ">\n";
  foreach ($code_types as $key => $value) {
    echo "    <option value='" . attr($key) . "'";
    if ($default == $key) echo " selected";
    echo ">" . xlt($value['label']) . "</option>\n";
  }
  echo "    <option value='PROD'";
  if ($default == 'PROD') echo " selected";
  echo ">" . xlt("Product") . "</option>\n";
  echo "   </select>&nbsp;&nbsp;\n";
}
?>

 <?php echo xlt('Search for:'); ?>
   <input type='text' name='search_term' size='12' value='<?php echo attr($_REQUEST['search_term']); ?>'
    title='<?php echo xla('Any part of the desired code or its description'); ?>' />
   &nbsp;
   <input type='submit' name='bn_search' value='<?php echo xla('Search'); ?>' />
   &nbsp;&nbsp;&nbsp;
   <?php if (!empty($target_element)) { ?>
     <input type='button' value='<?php echo xla('Erase'); ?>' onclick="selcode_target('', '', '', '', '<?php echo attr(addslashes($target_element)); ?>')" />
   <?php } else { ?>
     <input type='button' value='<?php echo xla('Erase'); ?>' onclick="selcode('', '', '', '')" />
   <?php } ?>
   </b>
  </td>
 </tr>

 <tr>
  <td height="1">
  </td>
 </tr>

</table>

<?php if ($_REQUEST['bn_search']) { ?>

<table border='0'>
 <tr>
  <td><b><?php echo xlt('Code'); ?></b></td>
  <td><b><?php echo xlt('Description'); ?></b></td>
 </tr>
<?php
  $search_term = $_REQUEST['search_term'];
  $res = main_code_set_search($form_code_type,$search_term);
  if ($form_code_type == 'PROD') { // Special case that displays search for products/drugs
    while ($row = sqlFetchArray($res)) {
      $drug_id = $row['drug_id'];
      $selector = $row['selector'];
      $desc = $row['name'];
      $anchor = "<a href='' " .
        "onclick='return selcode(\"PROD\", \"" . attr(addslashes($drug_id)) . "\", \"" . attr(addslashes($selector)) . "\", \"" . attr(addslashes($desc)) . "\")'>";
      echo " <tr>";
      echo "  <td>$anchor" . text($drug_id.":".$selector) . "</a></td>\n";
      echo "  <td>$anchor" . text($desc) . "</a></td>\n";
      echo " </tr>";
    }
  }
  else {
    while ($row = sqlFetchArray($res)) { // Display normal search
      $itercode = $row['code'];
      $itertext = trim($row['code_text']);
      if (!empty($target_element)) {
        // add a 5th parameter to function to select the target element on the form for placing the code.
        $anchor = "<a href='' " .
          "onclick='return selcode_target(\"" . attr(addslashes($form_code_type)) . "\", \"" . attr(addslashes($itercode)) . "\", \"\", \"" . attr(addslashes($itertext)) . "\", \"" . attr(addslashes($target_element)) . "\")'>";
      }
      else {
        $anchor = "<a href='' " .
          "onclick='return selcode(\"" . attr(addslashes($form_code_type)) . "\", \"" . attr(addslashes($itercode)) . "\", \"\", \"" . attr(addslashes($itertext)) . "\")'>";
      }
      echo " <tr>";
      echo "  <td>$anchor" . text($itercode) . "</a></td>\n";
      echo "  <td>$anchor" . text($itertext) . "</a></td>\n";
      echo " </tr>";
    }
  }
?>
</table>

<?php } ?>

</center>
</form>
</body>
</html>
