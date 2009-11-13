<?php
// Copyright (C) 2008-2009 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

include_once("../globals.php");
include_once("../../custom/code_types.inc.php");
include_once("$srcdir/sql.inc");

// Format dollars for display.
//
function bucks($amount) {
  if ($amount) {
    $amount = sprintf("%.2f", $amount);
    if ($amount != 0.00) return $amount;
  }
  return '';
}

$filter = $_REQUEST['filter'] + 0;
$where = "c.active = 1";
if ($filter) $where .= " AND c.code_type = '$filter'";
if (empty($_REQUEST['include_uncat']))
  $where .= " AND c.superbill != '' AND c.superbill != '0'";
?>
<html>
<head>
<?php html_header_show(); ?>
<title><?php xl('Services by Category','e'); ?></title>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
</head>
<body>
<center>

<form method='post' action='services_by_category.php' name='theform'>
<table border='0' cellpadding='5' cellspacing='0' width='98%'>
 <tr>
  <td class='title'>
   <?php xl('Services by Category','e'); ?>
  </td>
  <td class='text' align='right'>
   <select name='filter'>
    <option value='0'><?php xl('All','e'); ?></option>
<?php
foreach ($code_types as $key => $value) {
  echo "<option value='" . $value['id'] . "'";
  if ($value['id'] == $filter) echo " selected";
  echo ">$key</option>\n";
}
?>
   </select>
   &nbsp;
   <input type='checkbox' name='include_uncat' value='1'<?php if (!empty($_REQUEST['include_uncat'])) echo " checked"; ?> />
   <?php xl('Include Uncategorized','e'); ?>
   &nbsp;
   <input type="submit" name="form_submit" value=<?php xl('Refresh','e','\'','\''); ?>>
   &nbsp;
   <input type="button" value=<?php xl('Print','e','\'','\''); ?> onclick="window.print()">
  </td>
 </tr>
</table>
</form>

<?php if ($_POST['form_submit']) { ?>

<table border='0' cellpadding='1' cellspacing='2' width='98%'>
 <thead style='display:table-header-group'>
  <tr bgcolor="#dddddd">
   <th class='bold'><?php xl('Category'   ,'e'); ?></th>
   <th class='bold'><?php xl('Type'       ,'e'); ?></th>
   <th class='bold'><?php xl('Code'       ,'e'); ?></th>
   <th class='bold'><?php xl('Mod'        ,'e'); ?></th>
   <th class='bold'><?php xl('Units'      ,'e'); ?></th>
   <th class='bold'><?php xl('Description','e'); ?></th>
<?php if (related_codes_are_used()) { ?>
   <th class='bold'><?php xl('Related'    ,'e'); ?></th>
<?php } ?>
<?php   
$pres = sqlStatement("SELECT title FROM list_options " . 
		     "WHERE list_id = 'pricelevel' ORDER BY seq");
while ($prow = sqlFetchArray($pres)) {
  // Added 5-09 by BM - Translate label if applicable
  echo "   <th class='bold' align='right' nowrap>" . xl_list_label($prow['title']) . "</th>\n";
}
?>
  </tr>
 </thead>
 <tbody>
<?php
$res = sqlStatement("SELECT c.*, lo.title FROM codes AS c " .
  "LEFT OUTER JOIN list_options AS lo ON lo.list_id = 'superbill' " .
  "AND lo.option_id = c.superbill " .
  "WHERE $where ORDER BY lo.title, c.code_type, c.code, c.modifier");

$last_category = '';
$irow = 0;
while ($row = sqlFetchArray($res)) {
  $category = $row['title'] ? $row['title'] : 'Uncategorized';
  $disp_category = '&nbsp';
  if ($category !== $last_category) {
    $last_category = $category;
    $disp_category = $category;
    ++$irow;
  }
  foreach ($code_types as $key => $value) {
    if ($value['id'] == $row['code_type']) {
      break;
    }
  }
  $bgcolor = (($irow & 1) ? "#ffdddd" : "#ddddff");
  echo "  <tr bgcolor='$bgcolor'>\n";
  // Added 5-09 by BM - Translate label if applicable
  echo "   <td class='text'>" . xl_list_label($disp_category) . "</td>\n";
  echo "   <td class='text'>$key</td>\n";
  echo "   <td class='text'>" . $row['code'] . "</td>\n";
  echo "   <td class='text'>" . $row['modifier'] . "</td>\n";
  echo "   <td class='text'>" . $row['units'] . "</td>\n";
  echo "   <td class='text'>" . $row['code_text'] . "</td>\n";

  if (related_codes_are_used()) {
    // Show related codes.
    echo "   <td class='text'>";
    $arel = explode(';', $row['related_code']);
    foreach ($arel as $tmp) {
      list($reltype, $relcode) = explode(':', $tmp);
      $reltype = $code_types[$reltype]['id'];
      $relrow = sqlQuery("SELECT code_text FROM codes WHERE " .
        "code_type = '$reltype' AND code = '$relcode' LIMIT 1");
      echo $relcode . ' ' . trim($relrow['code_text']) . '<br />';
    }
    echo "</td>\n";
  }

  $pres = sqlStatement("SELECT p.pr_price " .
    "FROM list_options AS lo LEFT OUTER JOIN prices AS p ON " .
    "p.pr_id = '" . $row['id'] . "' AND p.pr_selector = '' " .
    "AND p.pr_level = lo.option_id " .
    "WHERE list_id = 'pricelevel' ORDER BY lo.seq");
  while ($prow = sqlFetchArray($pres)) {
    echo "   <td class='text' align='right'>" . bucks($prow['pr_price']) . "</td>\n";
  }
  echo "  </tr>\n";
}
?>
 </tbody>
</table>

<?php } // end of submit logic ?>

</center>

</body>
</html>
