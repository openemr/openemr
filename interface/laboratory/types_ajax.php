<?php
// Copyright (C) 2010-2012 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
/*
 * Adapted for use with the dedicated laboratory interfaces developed
 * for Williams Medical Technologies, Inc.
 *
 * @since		2014-06-15
 * @author		Ron Criswell <ron.criswell@MDTechSvcs.com>
 *
 */
require_once("../globals.php");
require_once("$srcdir/formdata.inc.php");

$id = formData('id','G') + 0;
$order = formData('order','G') + 0;
$labid = formData('labid','G') + 0;

echo "$('#con$id').html('<table width=\"100%\" cellspacing=\"0\">";

// Determine indentation level for this container.
for ($level = 0, $parentid = $id; $parentid; ++$level) {
  $row = sqlQuery("SELECT parent FROM procedure_type WHERE procedure_type_id = '$parentid'");
  $parentid = $row['parent'] + 0;
}

// CRISWELL -- WMT Lab Interface
//$res = sqlStatement("SELECT * FROM procedure_type WHERE parent = '$id' " .
//  "ORDER BY seq, name, procedure_type_id");
	$query = "SELECT pt.* FROM procedure_type pt ";
	$query .= "LEFT JOIN procedure_providers pp ON pp.ppid = pt.lab_id ";
	$query .= "WHERE parent = '$id' AND (pp.type != 'laboratory' AND pp.type != 'quest' AND pp.type != 'labcorp') ";
	$query .= "ORDER BY seq,name,procedure_type_id";
	$res = sqlStatement($query);
// END CRISWELL
$res = sqlStatement("SELECT pt.*,lo.title AS type_name FROM procedure_type pt ".
  "LEFT JOIN list_options lo ON pt.procedure_type = lo.option_id AND lo.list_id = 'proc_type' ".
	"LEFT JOIN procedure_providers pp ON pp.ppid = pt.lab_id ".
	"WHERE parent = '$id' AND (pp.type = 'laboratory' OR pp.type = 'quest' OR pp.type = 'labcorp' OR pp.type = 'internal' OR pp.type LIKE 'quick%') ".
  "ORDER BY procedure_type, name, seq, procedure_type_id");

$encount = 0;

// Generate a table row for each immediate child.
while ($row = sqlFetchArray($res)) {
  $chid = $row['procedure_type_id'] + 0;

  // Find out if this child has any children.
  $trow = sqlQuery("SELECT procedure_type_id FROM procedure_type WHERE parent = '$chid' LIMIT 1");
  $iscontainer = !empty($trow['procedure_type_id']);

  $classes = 'col1';
  if ($iscontainer) {
    $classes .= ' haskids';
  }

  echo "<tr>";
  echo "<td id=\"td$chid\"";
  echo " onclick=\"toggle($chid)\"";
  echo " class=\"$classes\">";
  echo "<span style=\"margin:0 4 0 " . ($level * 9) . "pt\" class=\"plusminus\">";
  echo $iscontainer ? "+" : '|';
  echo "</span>";
  echo htmlspecialchars($row['name'], ENT_QUOTES) . "</td>";
  //
  echo "<td class=\"col2\">";
  if ( (substr($row['procedure_type'], 0, 3) == 'ord') || (substr($row['procedure_type'], 0, 3) == 'pro') ) {
    if ($order && ($labid == 0 || $row['lab_id'] == $labid)) {
      echo "<input type=\"radio\" name=\"form_order\" value=\"$chid\"";
      if ($chid == $order) echo " checked";
      echo " />";
    }
    else {
      echo xl('Yes');
    }
  }
  else {
    echo '&nbsp;';
  }
  echo "</td>";
  //
  echo "<td class=\"col3\">" . htmlspecialchars($row['procedure_code'], ENT_QUOTES) . "</td>";
  echo "<td class=\"col4\">" . htmlspecialchars(preg_replace( "/\r|\n/", " ", $row['type_name']), ENT_QUOTES) . "</td>";
//  echo "<td class=\"col5\">" . htmlspecialchars($row['description'], ENT_QUOTES) . "</td>";
  echo "<td class=\"col6\">";
  echo "<span onclick=\"enode($chid)\" class=\"haskids\">[" . xl('Edit') . "]</span>";
  echo "<span onclick=\"anode($chid)\" class=\"haskids\"> [" . xl('Add') . "]</span>";
  echo "</td>";
  echo "</tr>";
}

echo "</table>');\n";
echo "nextOpen();\n";
?>
