<?php
/* interface/forms/<folder_name>/save.php
 * Provides mechanism for actually saving the work.
 * To adapt for other uses edit $form_name and $folder_name.
 * !!! Sketchpad requires appropriately referenced custom canvas.js file !!!
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
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  David Hantke
 * @link    http://www.open-emr.org
 */

include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");
if ($encounter == "")
	$encounter = date("Ymd");

$form_name = xlt('Sketchpad');
$folder_name = 'sketchpad';
$table_name = 'form_'. $folder_name;

if ($_GET["mode"] == "new") {
	$newid = formSubmit($table_name, $_POST, $_GET["id"], $userauthorized); // formSubmit() in library/api.inc
	addForm($encounter, $form_name, $newid, $folder_name, $pid, $userauthorized); // addForm() in library/forms.inc
}
elseif ($_GET["mode"] == "update") {
	sqlInsert("UPDATE " . $table_name . " SET pid = ?, groupname=?, user=?, authorized=?, activity=1, date=NOW(), background=?, output=?, comments=? WHERE id=?",
		array($_SESSION["pid"], $_SESSION["authProvider"], $_SESSION["authUser"], $userauthorized, $_POST["background"], $_POST["output"], $_POST["comments"], $_GET["id"]));
}

$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
?>
