<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

include_once("../../globals.php");
?>
<html>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css">

<script language="JavaScript">

function openNewForm(sel) {
 top.restoreSession();
<?php if ($GLOBALS['concurrent_layout']) { ?>
  parent.location.href = sel.options[sel.selectedIndex].value;
<?php } else { ?>
  top.frames['Main'].location.href = sel.options[sel.selectedIndex].value;
<?php } ?>
}

</script>

</head>
<body class="body_top">
<dl>
<?php //DYNAMIC FORM RETREIVAL
include_once("$srcdir/registry.inc");

function myGetRegistered($state="1", $limit="unlimited", $offset="0") {
  $sql = "SELECT category, nickname, name, state, directory, id, sql_run, " .
    "unpackaged, date FROM registry WHERE " .
    "state LIKE \"$state\" ORDER BY category, priority";
  if ($limit != "unlimited") $sql .= " limit $limit, $offset";
  $res = sqlStatement($sql);
  if ($res) {
    for($iter=0; $row=sqlFetchArray($res); $iter++) {
      $all[$iter] = $row;
    }
  }
  else {
    return false;
  }
  return $all;
}

$reg = myGetRegistered();
$old_category = '';
echo "<FORM METHOD=POST NAME='choose'>\n";
if (!empty($reg)) {
  foreach ($reg as $entry) {
	  $new_category = trim($entry['category']);
	  $new_nickname = trim($entry['nickname']);
	  if ($new_category == '') {$new_category = 'miscellaneous';}
	  if ($new_nickname != '') {$nickname = $new_nickname;}
	  else {$nickname = $entry['name'];}
	  if ($old_category != $new_category) {
		  $new_category_ = $new_category;
		  $new_category_ = str_replace(' ','_',$new_category_);
		  if ($old_category != '') {echo "</select>\n";}
		  echo "<select name=" . $new_category_ . " onchange='openNewForm(this)'>\n";
		  echo " <option value=" . $new_category_ . ">" . $new_category . "</option>\n";
		  $old_category = $new_category;
	  }
	  echo " <option value='" . $rootdir .
		  '/patient_file/encounter/load_form.php?formname=' .
		  urlencode($entry['directory']) . "'>" . xl_form_title($nickname) . "</option>\n";
  }
  echo "</select>\n";
}

// This shows Layout Based Form names just like the above.
//
$lres = sqlStatement("SELECT * FROM list_options " .
  "WHERE list_id = 'lbfnames' ORDER BY seq, title");
if (sqlNumRows($lres)) {
  echo "<select name='lbfnames' onchange='openNewForm(this)'>\n";
  echo "<option value='lbfnames'>Layout Based</option>\n";
  while ($lrow = sqlFetchArray($lres)) {
    $option_id = $lrow['option_id']; // should start with LBF
    $title = $lrow['title'];
	  echo "<option value='$rootdir/patient_file/encounter/load_form.php?" .
      "formname=$option_id'>$title</option>\n";
  }
  echo "</select>\n";
}

echo "</FORM>\n";
?>
</dl>

</body>
</html>
