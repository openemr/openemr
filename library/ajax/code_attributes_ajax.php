<?php
/**
 * Copyright (C) 2015-2017 Rod Roark <rod@sunsetsystems.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>.
 *
 * @package OpenEMR
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @link    http://www.open-emr.org
 */

// Given a code type, code, selector and price level for a service or product, this creates
// JavaScript that will call the user's handler passing the following arguments:
// code type, code, description, price, warehouse options.

$fake_register_globals = false;
$sanitize_all_escapes  = true;

require_once("../../interface/globals.php");
require_once("$srcdir/formdata.inc.php");
require_once("$fileroot/custom/code_types.inc.php");
require_once("$fileroot/interface/drugs/drugs.inc.php");

function write_code_info($codetype, $code, $selector, $pricelevel) {
  global $code_types;

  $wh = ''; // options for warehouse selection

  if ($codetype == 'PROD') {
    $wrow = sqlQuery("SELECT default_warehouse FROM users WHERE username = ?",
      array($_SESSION['authUser']));
    $defaultwh = empty($wrow['default_warehouse']) ? '' : $wrow['default_warehouse'];
    //
    $crow = sqlQuery("SELECT d.name, p.pr_price " .
      "FROM drugs AS d " .
      "LEFT JOIN prices AS p ON p.pr_id = d.drug_id AND p.pr_selector = ? AND p.pr_level = ? " .
      "WHERE d.drug_id = ?",
      array($selector, $pricelevel, $code));
    $desc = $crow['name'];
    $price = empty($crow['pr_price']) ? 0 : (0 + $crow['pr_price']);
    //
    $lres = sqlStatement("SELECT * FROM list_options " .
      "WHERE list_id = 'warehouse' AND activity = 1 ORDER BY seq, title");
    $wh .= "<option value=''></option>";
    while ($lrow = sqlFetchArray($lres)) {
      $wh .= "<option value='" . $lrow['option_id'] . "'";
      $has_inventory = sellDrug($code, 1, 0, 0, 0, 0, '', '', $lrow['option_id'], true);
      if ($has_inventory && (
          (strlen($defaultwh) == 0 && $lrow['is_default']           ) ||
          (strlen($defaultwh)  > 0 && $lrow['option_id'] == $default)))
      {
        $wh .= " selected";
      }
      else {
        // Disable this warehouse option if not selected and has no inventory.
        if (!$has_inventory) $wh .= " disabled";
      }
      $wh .= ">" . xl_list_label($lrow['title']) . "</option>";
    }
  }
  else {
    $crow = sqlQuery("SELECT c.code_text, p.pr_price " .
      "FROM codes AS c " .
      "LEFT JOIN prices AS p ON p.pr_id = c.id AND p.pr_selector = '' AND p.pr_level = ? " .
      "WHERE c.code_type = ? AND c.code = ? LIMIT 1",
      array($pricelevel, $code_types[$codetype]['id'], $code));
    $desc = $crow['code_text'];
    $price = empty($crow['pr_price']) ? 0 : (0 + $crow['pr_price']);
  }

  // error_log("Warehouse string is: " . $wh); // debugging

  echo "code_attributes_handler(" .
    "'" . addslashes($codetype) . "'," .
    "'" . addslashes($code    ) . "'," .
    "'" . addslashes($desc    ) . "'," .
    "'" . addslashes($price   ) . "'," .
    "'" . addslashes($wh      ) . "');";
}

$pricelevel = isset($_GET['pricelevel']) ? $_GET['pricelevel'] : '';

if (!empty($_GET['list'])) {
  // This case supports packages of codes.
  $arrcodes = explode('~', $_GET['list']);
  foreach ($arrcodes as $codestring) {
    if ($codestring === '') continue;
    $arrcode = explode('|', $codestring);
    $codetype = $arrcode[0];
    list($code, $modifier) = explode(":", $arrcode[1]);
    $selector = isset($arrcode[2]) ? $arrcode[2] : '';
    write_code_info($codetype, $code, $selector, $pricelevel);
  }
}
else {
  // This is the normal case of adding a single code.
  $codetype   = isset($_GET['codetype'  ]) ? $_GET['codetype'  ] : '';
  $code       = isset($_GET['code'      ]) ? $_GET['code'      ] : '';
  $selector   = isset($_GET['selector'  ]) ? $_GET['selector'  ] : '';
  write_code_info($codetype, $code, $selector, $pricelevel);
}
