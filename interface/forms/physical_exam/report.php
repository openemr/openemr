<?php
// Copyright (C) 2005 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

include_once(dirname(__FILE__).'/../../globals.php');
include_once($GLOBALS["srcdir"] . "/api.inc");
include_once("lines.php");

function physical_exam_report($pid, $encounter, $cols, $id) {
 global $pelines;

 $rows = array();
 $res = sqlStatement("SELECT * FROM form_physical_exam WHERE forms_id = '$id'");
 while ($row = sqlFetchArray($res)) {
  $rows[$row['line_id']] = $row;
 }

 echo "<table cellpadding='0' cellspacing='0'>\n";

 foreach ($pelines as $sysname => $sysarray) {
  $sysnamedisp = xl($sysname);
  foreach ($sysarray as $line_id => $description) {
   $linedbrow = $rows[$line_id];
   if (!($linedbrow['wnl'] || $linedbrow['abn'] || $linedbrow['diagnosis'] ||
    $linedbrow['comments'])) continue;
   if ($sysname != '*') { // observation line
    echo " <tr>\n";
    echo "  <td class='text' align='center'>" . ($linedbrow['wnl'] ? "WNL" : "") . "&nbsp;&nbsp;</td>\n";
    echo "  <td class='text' align='center'>" . ($linedbrow['abn'] ? "ABN1" : "") . "&nbsp;&nbsp;</td>\n";
    echo "  <td class='text' nowrap>$sysnamedisp&nbsp;&nbsp;</td>\n";
    echo "  <td class='text' nowrap>$description&nbsp;&nbsp;</td>\n";
    echo "  <td class='text'>" . $linedbrow['diagnosis'] . "&nbsp;&nbsp;</td>\n";
    echo "  <td class='text'>" . htmlentities($linedbrow['comments']) . "</td>\n";
    echo " </tr>\n";
   } else { // treatment line
    echo " <tr>\n";
    echo "  <td class='text' align='center'>" . ($linedbrow['wnl'] ? "Y" : "") . "&nbsp;&nbsp;</td>\n";
    echo "  <td class='text' align='center'>&nbsp;&nbsp;</td>\n";
    echo "  <td class='text' colspan='2' nowrap>$description&nbsp;&nbsp;</td>\n";
    echo "  <td class='text' colspan='2'>" . htmlentities($linedbrow['comments']) . "</td>\n";
    echo " </tr>\n";
   }
   $sysnamedisp = '';
  } // end of line
 } // end of system name

 echo "</table>\n";
}
?> 
