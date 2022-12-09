<?php
// +-----------------------------------------------------------------------+
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// A copy of the GNU General Public License is included along with this 
// program:  openemr/interface/login/GnuGPL.html
// For more information write to the Free Software Foundation, Inc.
// 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
//
// +-----------------------------------------------------------------------+

// SANITIZE ALL ESCAPES
$sanitize_all_escapes = true;

// STOP FAKE REGISTER GLOBALS
$fake_register_globals = false;

require_once("../../interface/globals.php");
require_once("$srcdir/auth.inc");

// Get request type
$type = isset($_REQUEST['type']) ? strip_tags($_REQUEST['type']) : '';
$id = isset($_REQUEST['id']) ? strip_tags($_REQUEST['id']) : '';

if ($type == 'lot_no') {
	$fres = sqlStatement("SELECT * FROM drug_inventory WHERE drug_id=? AND ".
                        "on_hand > 0 ORDER BY lot_number ASC", array($id));

	echo '<option value="">-- SELECT FROM INVENTORY --</option>';
	echo "\n";
	while($frow = sqlFetchArray($fres)) {
		echo '<option value="'.$frow{'inventory_id'}.'">'.
			htmlspecialchars($frow{'lot_number'}, ENT_QUOTES, '', FALSE) . 
			' - (' . htmlspecialchars($frow{'on_hand'}, ENT_QUOTES, '', FALSE) . 
			' On Hand)</option>';
		echo "\n";
	}
}

if ($type == 'inv_detail') {
	$sql = 'SELECT * FROM drug_inventory WHERE inventory_id=?';
	$frow = sqlQuery($sql, array($id));
	echo $frow{'drug_id'}.'~%'.$frow{'expiration'}.'~%'.$frow{'manufacturer'}.
		'~%'.$frow{'on_hand'};	

}

exit;

?>
