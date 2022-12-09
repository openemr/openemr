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

require_once("../../../interface/globals.php");
require_once("$srcdir/auth.inc");

// Get request type
$type = isset($_REQUEST['type']) ? strip_tags($_REQUEST['type']) : '';
$ret = 'No Data';

if($type != '') {
	$fres = sqlQuery("SELECT * FROM list_options WHERE list_id=? AND ".
                        "option_id=?", array('LARC_Type',$type));
	$ret = $fres{'codes'};
}

echo $ret;
exit;

?>
