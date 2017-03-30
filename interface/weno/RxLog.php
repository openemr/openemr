<?php
/** Copyright (C) 2016 Sherwin Gaddis <sherwingaddis@gmail.com>
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
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @link    http://www.open-emr.org
 */

 $sanitize_all_escapes = true;		// SANITIZE ALL ESCAPES

$fake_register_globals = false;		// STOP FAKE REGISTER GLOBALS

require_once("../globals.php");

?>
<h1>Rx Log</h1>
<html>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
</head>
<body class="body_top">
<?php

$log = sqlStatement("SELECT * FROM erx_rx_log ORDER BY id DESC");

print "<table width='100%'>";
print "<tr align='left'>

<th>". xlt("Rx ID") ."</th>
<th>". xlt("Date") ."</th>
<th>". xlt("Time") ."</th>
<th>". xlt("Code") ."</th>
<th>". xlt("Status") ."</th>
<th>". xlt("Message") ."</th>
</tr>";
while($row = sqlFetchArray($log)){

	print "<tr><td>" .text($row['prescription_id'])."</td><td>".text($row['date']).
          "</td><td>".text($row['time'])."</td><td>".text($row['code'])."</td><td>".text($row['status']).
          "</td><td>".text($row['message_id'])."</td></tr>";		  
	
	}
print "</table>";
?>
</body>
</html>