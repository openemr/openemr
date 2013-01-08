<?php
// Copyright (C) 2010-2013 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

include_once("../../globals.php");
include_once($GLOBALS["srcdir"] . "/api.inc");
include_once($GLOBALS["srcdir"] . "/options.inc.php");

function procedure_order_report($pid, $encounter, $cols, $id) {
  $data = sqlQuery("SELECT " .
    "po.procedure_order_id, po.date_ordered, po.diagnoses, " .
    "po.order_status, po.specimen_type, " .
    "pp.name AS labname, pr.procedure_report_id, " .
    "u.lname AS ulname, u.fname AS ufname, u.mname AS umname " .
    "FROM procedure_order AS po " .
    "LEFT JOIN procedure_providers AS pp ON pp.ppid = po.lab_id " .
    "LEFT JOIN users AS u ON u.id = po.provider_id " .
    "LEFT JOIN procedure_report AS pr ON pr.procedure_order_id = po.procedure_order_id " .
    "WHERE po.procedure_order_id = ? " .
    "ORDER BY pr.procedure_report_id LIMIT 1",
    array($id));

  if ($data) {
    echo "<table cellpadding='2' cellspacing='0'>\n";
    echo " <tr>\n";
    echo "  <td class='bold'>" . xlt('Order ID') . ": </td>\n";
    echo "  <td class='text'>" . text($data['procedure_order_id']) . "</td>\n";
    echo " </tr>\n";
    echo " <tr>\n";
    echo "  <td class='bold'>" . xlt('Order Date') . ": </td>\n";
    echo "  <td class='text'>" . text(oeFormatShortDate($data['date_ordered'])) . "</td>\n";
    echo " </tr>\n";
    echo " <tr>\n";
    echo "  <td class='bold'>" . xlt('Ordered By') . ": </td>\n";
    echo "  <td class='text'>" . text($data['ulname'] . ', ' . $data['ufname'] . ' ' . $data['umname']) . "</td>\n";
    echo " </tr>\n";
    echo " <tr>\n";
    echo "  <td class='bold'>" . xlt('Lab') . ": </td>\n";
    echo "  <td class='text'>" . text($data['labname']) . "</td>\n";
    echo " </tr>\n";
    if (!empty($data['procedure_report_id'])) {
      echo " <tr>\n";
      echo "  <td>&nbsp;</td>\n";
      echo "  <td class='bold'><a href='#' onclick=\"" .
        "top.restoreSession();" .
        "window.open('" . $GLOBALS['web_root'] . "/interface/orders/single_order_results.php?orderid=" . text($id) . "');" .
        "return false;" .
        "\">[" . xlt('View Results') . "]</a></td>\n";
      echo " </tr>\n";
    }
    echo "</table>\n";
  }
}
?>
