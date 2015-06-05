<?php
/**
 * Copyright (C) 2014 Rod Roark <rod@sunsetsystems.com>
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

// Given a list ID, name of a target form field and a default value, this creates
// JavaScript that will write Option values into the target selection list.

$fake_register_globals = false;
$sanitize_all_escapes  = true;

require_once("../globals.php");
require_once("$srcdir/formdata.inc.php");

$listid  = $_GET['listid'];
$target  = $_GET['target'];
$current = $_GET['current'];

$res = sqlStatement("SELECT option_id FROM list_options WHERE list_id = ? " .
  "ORDER BY seq, option_id", array($listid));

echo "var itemsel = document.forms[0]['$target'];\n";
echo "var j = 0;\n";
echo "itemsel.options[j++] = new Option('-- " . xls('Please Select') . " --','',false,false);\n";
while ($row = sqlFetchArray($res)) {
  $tmp = addslashes($row['option_id']);
  $def = $row['option_id'] == $current ? 'true' : 'false';
  echo "itemsel.options[j++] = new Option('$tmp','$tmp',$def,$def);\n";
}
?>
