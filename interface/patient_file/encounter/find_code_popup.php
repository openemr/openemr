<?php
// Copyright (C) 2008 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/csv_like_join.php");
require_once("../../../custom/code_types.inc.php");

$info_msg = "";
$codetype = $_REQUEST['codetype'];
if (isset($codetype)) {
	$allowed_codes = split_csv_line($codetype);
}

$form_code_type = $_POST['form_code_type'];
?>
<html>
<head>
<?php html_header_show(); ?>
<title><?php xl('Code Finder','e'); ?></title>
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>

<style>
td { font-size:10pt; }
</style>

<script language="JavaScript">

 function selcode(codetype, code, selector, codedesc) {
  if (opener.closed || ! opener.set_related)
   alert('The destination form was closed; I cannot act on your selection.');
  else
   opener.set_related(codetype, code, selector, codedesc);
  window.close();
  return false;
 }

</script>

</head>

<body class="body_top">

<?php if (isset($allowed_codes)) { ?>
  <form method='post' name='theform' action='find_code_popup.php?codetype=<?php echo $codetype ?>'>
<?php } else { ?>
  <form method='post' name='theform' action='find_code_popup.php'>
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
if (isset($allowed_codes)) {
	if (count($allowed_codes) === 1) {
  echo "<input type='text' name='form_code_type' value='$codetype' size='5' readonly>\n";
	} else {
?>
   <select name='form_code_type'>
<?php
		foreach ($allowed_codes as $code) {
			$value = htmlspecialchars($code, ENT_QUOTES);
			$selected_attr = ($form_code_type == $code) ? " selected='selected'" : '';
			$text = htmlspecialchars($code, ENT_NOQUOTES);
?>
   	<option value='<?php echo $value ?>'<?php echo $select_attr?>><?php echo $text ?></option>
<?php
		}
?>
   </select>
<?php
	}
}
else {
  echo "   <select name='form_code_type'";
  echo ">\n";
  foreach ($code_types as $key => $value) {
    echo "    <option value='$key'";
    if ($codetype == $key || $form_code_type == $key) echo " selected";
    echo ">$key</option>\n";
  }
  echo "    <option value='PROD'";
  if ($codetype == 'PROD' || $form_code_type == 'PROD') echo " selected";
  echo ">Product</option>\n";
  echo "   </select>&nbsp;&nbsp;\n";
}
?>

 <?php xl('Search for:','e'); ?>
   <input type='text' name='search_term' size='12' value='<?php echo $_REQUEST['search_term']; ?>'
    title='<?php xl('Any part of the desired code or its description','e'); ?>' />
   &nbsp;
   <input type='submit' name='bn_search' value='<?php xl('Search','e'); ?>' />
   &nbsp;&nbsp;&nbsp;
   <input type='button' value='<?php xl('Erase','e'); ?>' onclick="selcode('', '', '', '')" />
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
  <td><b><?php xl ('Code','e'); ?></b></td>
  <td><b><?php xl ('Description','e'); ?></b></td>
 </tr>
<?php
  $search_term = $_REQUEST['search_term'];
  if ($form_code_type == 'PROD') {
    $query = "SELECT dt.drug_id, dt.selector, d.name " .
      "FROM drug_templates AS dt, drugs AS d WHERE " .
      "( d.name LIKE '%$search_term%' OR " .
      "dt.selector LIKE '%$search_term%' ) " .
      "AND d.drug_id = dt.drug_id " .
      "ORDER BY d.name, dt.selector, dt.drug_id";
    $res = sqlStatement($query);
    while ($row = sqlFetchArray($res)) {
      $drug_id = addslashes($row['drug_id']);
      $selector = addslashes($row['selector']);
      $desc = addslashes($row['name']);
      $anchor = "<a href='' " .
        "onclick='return selcode(\"PROD\", \"$drug_id\", \"$selector\", \"$desc\")'>";
      echo " <tr>";
      echo "  <td>$anchor$drug_id:$selector</a></td>\n";
      echo "  <td>$anchor$desc</a></td>\n";
      echo " </tr>";
    }
  }
  else {
    $query = "SELECT code_type, code, modifier, code_text FROM codes " .
      "WHERE (code_text LIKE '%$search_term%' OR " .
      "code LIKE '%$search_term%') " .
      "AND code_type = '" . $code_types[$form_code_type]['id'] . "' " .
      "ORDER BY code";
    // echo "\n<!-- $query -->\n"; // debugging
    $res = sqlStatement($query);
    while ($row = sqlFetchArray($res)) {
      $itercode = addslashes($row['code']);
      $itertext = addslashes(ucfirst(strtolower(trim($row['code_text']))));
      $anchor = "<a href='' " .
        "onclick='return selcode(\"$form_code_type\", \"$itercode\", \"\", \"$itertext\")'>";
      echo " <tr>";
      echo "  <td>$anchor$itercode</a></td>\n";
      echo "  <td>$anchor$itertext</a></td>\n";
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
